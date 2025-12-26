<?php
/**
 * OtaPay API Integration Class
 * For purchasing WAEC and NECO scratch cards from otapay.ng
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_OtaPay {
    
    private static $instance = null;
    private $api_base_url = 'https://app.otapay.ng/api';
    private $api_key = '';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->api_key = get_option('zonatech_otapay_api_key', '');
        
        // Register AJAX handlers
        add_action('wp_ajax_zonatech_purchase_otapay_card', array($this, 'purchase_card'));
        add_action('wp_ajax_zonatech_save_otapay_key', array($this, 'save_api_key'));
    }
    
    /**
     * Check if OtaPay is configured
     */
    public function is_configured() {
        return !empty($this->api_key);
    }
    
    /**
     * Get API Key
     */
    public function get_api_key() {
        return $this->api_key;
    }
    
    /**
     * Make API request to OtaPay
     */
    private function make_request($endpoint, $data = array(), $method = 'POST') {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => 'OtaPay API key not configured'
            );
        }
        
        $url = $this->api_base_url . '/' . ltrim($endpoint, '/');
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            )
        );
        
        if ($method === 'POST' && !empty($data)) {
            $args['body'] = wp_json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            error_log('ZonaTech OtaPay Error: ' . $response->get_error_message());
            return array(
                'success' => false,
                'message' => 'Connection error: ' . $response->get_error_message()
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('ZonaTech OtaPay Error: Invalid JSON response');
            return array(
                'success' => false,
                'message' => 'Invalid response from OtaPay'
            );
        }
        
        return $data;
    }
    
    /**
     * Purchase WAEC or NECO scratch card
     */
    public function purchase_card() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to purchase scratch card.'));
        }
        
        if (!$this->is_configured()) {
            wp_send_json_error(array('message' => 'Scratch card service is not configured. Please contact support.'));
        }
        
        $card_type = sanitize_text_field($_POST['card_type'] ?? '');
        $reference = sanitize_text_field($_POST['reference'] ?? '');
        
        // Validate card type - OtaPay supports WAEC and NECO
        $valid_types = array('waec', 'neco');
        if (!in_array(strtolower($card_type), $valid_types)) {
            wp_send_json_error(array('message' => 'Invalid card type. Only WAEC and NECO are supported.'));
        }
        
        $user_id = get_current_user_id();
        $user = get_user_by('ID', $user_id);
        
        // Log the purchase attempt
        ZonaTech_Activity_Log::log(
            $user_id,
            'otapay_purchase_attempt',
            sprintf('Attempting to purchase %s scratch card via OtaPay', strtoupper($card_type))
        );
        
        // Map card type to OtaPay provider code
        $provider_code = $this->get_provider_code($card_type);
        
        // Generate unique reference
        $unique_ref = 'ZT' . time() . $user_id . rand(100, 999);
        
        // Make the purchase request to OtaPay using their exact API format
        // Endpoint: https://app.otapay.ng/api/exampin/
        // Payload: {"provider":"1", "quantity":"1","ref":"unique_ref"}
        $purchase_data = array(
            'provider' => $provider_code,
            'quantity' => '1',
            'ref' => $reference ?: $unique_ref
        );
        
        $response = $this->make_request('/exampin/', $purchase_data);
        
        if (!$response || !isset($response['status'])) {
            wp_send_json_error(array('message' => 'Unable to connect to scratch card provider. Please try again.'));
        }
        
        // Check for success - OtaPay returns {"status": "success", "Status":"successful",...}
        $is_success = (isset($response['status']) && strtolower($response['status']) === 'success') ||
                      (isset($response['Status']) && strtolower($response['Status']) === 'successful');
        
        if (!$is_success) {
            $error_message = $response['msg'] ?? $response['message'] ?? 'Purchase failed. Please try again.';
            
            ZonaTech_Activity_Log::log(
                $user_id,
                'otapay_purchase_failed',
                sprintf('Failed to purchase %s scratch card: %s', strtoupper($card_type), $error_message)
            );
            
            wp_send_json_error(array('message' => $error_message));
        }
        
        // Extract PIN from response - OtaPay returns pins in multiple fields
        // Response format: {"status": "success","msg":"123456","pin":"123456","pins":"123456","token":"123456"}
        $pin = $response['pin'] ?? $response['pins'] ?? $response['token'] ?? $response['msg'] ?? '';
        $serial = ''; // OtaPay doesn't return serial separately
        
        if (empty($pin)) {
            wp_send_json_error(array('message' => 'No PIN received from provider. Please contact support.'));
        }
        
        // Store the purchased card in database
        global $wpdb;
        $table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
        
        // Ensure table exists
        $this->ensure_table_exists();
        
        $insert_result = $wpdb->insert($table_cards, array(
            'card_type' => strtolower($card_type),
            'pin' => $pin,
            'serial_number' => $serial,
            'user_id' => $user_id,
            'status' => 'sold',
            'sold_at' => current_time('mysql'),
            'created_at' => current_time('mysql')
        ), array('%s', '%s', '%s', '%d', '%s', '%s', '%s'));
        
        if (!$insert_result) {
            error_log('ZonaTech OtaPay: Failed to save card to database: ' . $wpdb->last_error);
        }
        
        // Log successful purchase
        ZonaTech_Activity_Log::log(
            $user_id,
            'otapay_purchase_success',
            sprintf('Successfully purchased %s scratch card via OtaPay', strtoupper($card_type)),
            array('pin_suffix' => substr($pin, -4))
        );
        
        // Send email with PIN details
        $this->send_card_email($user_id, $card_type, $pin, $serial);
        
        wp_send_json_success(array(
            'message' => 'Scratch card purchased successfully!',
            'pin' => $pin,
            'serial' => $serial,
            'card_type' => strtoupper($card_type)
        ));
    }
    
    /**
     * Get OtaPay provider code for card type
     * Based on OtaPay API: provider "1" for WAEC, "2" for NECO (may need adjustment)
     */
    private function get_provider_code($card_type) {
        // OtaPay uses numeric provider codes
        // These codes should match the OtaPay documentation
        $codes = array(
            'waec' => '1',   // WAEC Result Checker
            'neco' => '2'    // NECO Result Checker
        );
        
        return $codes[strtolower($card_type)] ?? '1';
    }
    
    /**
     * Ensure scratch cards table exists
     */
    private function ensure_table_exists() {
        global $wpdb;
        $table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_cards'");
        if (!$table_exists) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_cards (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                card_type varchar(20) NOT NULL,
                pin varchar(100) NOT NULL,
                serial_number varchar(100),
                user_id bigint(20) DEFAULT NULL,
                status varchar(20) DEFAULT 'available',
                sold_at datetime DEFAULT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY card_type (card_type),
                KEY user_id (user_id),
                KEY status (status)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
    
    /**
     * Send card details email
     */
    private function send_card_email($user_id, $card_type, $pin, $serial) {
        $user = get_user_by('ID', $user_id);
        if (!$user) return;
        
        $to = $user->user_email;
        $first_name = get_user_meta($user_id, 'first_name', true) ?: $user->display_name;
        $card_name = strtoupper($card_type) . ' Result Checker PIN';
        
        $subject = "Your $card_name - ZonaTech NG";
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #0a0a0a;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 20px; padding: 40px; margin: 20px 0;">
                    <div style="text-align: center; margin-bottom: 30px;">
                        <div style="font-size: 32px; color: #8b5cf6; margin-bottom: 10px;">🎫</div>
                        <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Your Scratch Card Details</h1>
                    </div>
                    
                    <p style="color: #ffffff; font-size: 18px; margin-bottom: 20px;">Hi ' . esc_html($first_name) . ',</p>
                    
                    <p style="color: #a1a1aa; font-size: 14px; line-height: 1.6;">
                        Thank you for your purchase! Here are your <strong style="color: #ffffff;">' . esc_html($card_name) . '</strong> details:
                    </p>
                    
                    <div style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 15px; padding: 25px; margin: 25px 0;">
                        <div style="margin-bottom: 15px;">
                            <span style="color: #a1a1aa; font-size: 12px; text-transform: uppercase;">Card Type</span>
                            <div style="color: #ffffff; font-size: 18px; font-weight: bold;">' . esc_html(strtoupper($card_type)) . ' Result Checker</div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <span style="color: #a1a1aa; font-size: 12px; text-transform: uppercase;">PIN</span>
                            <div style="color: #22c55e; font-size: 24px; font-weight: bold; font-family: monospace; letter-spacing: 2px;">' . esc_html($pin) . '</div>
                        </div>
                        ' . (!empty($serial) ? '<div>
                            <span style="color: #a1a1aa; font-size: 12px; text-transform: uppercase;">Serial Number</span>
                            <div style="color: #ffffff; font-size: 16px; font-family: monospace;">' . esc_html($serial) . '</div>
                        </div>' : '') . '
                    </div>
                    
                    <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 15px; margin: 20px 0;">
                        <p style="color: #f59e0b; font-size: 13px; margin: 0;">
                            <strong>⚠️ Important:</strong> Keep this PIN safe. Each PIN can only be used once. 
                            Do not share your PIN with anyone.
                        </p>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="' . site_url('/zonatech-dashboard/') . '" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: #ffffff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 14px;">
                            View in Dashboard
                        </a>
                    </div>
                    
                    <p style="color: #71717a; font-size: 12px; text-align: center; margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                        If you did not make this purchase, please contact support immediately.<br>
                        © ' . date('Y') . ' ZonaTech NG. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>';
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ZonaTech NG <' . ZONATECH_SUPPORT_EMAIL . '>'
        );
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Save OtaPay API Key (AJAX handler)
     */
    public function save_api_key() {
        check_ajax_referer('zonatech_save_otapay', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized access.'));
        }
        
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        
        if (empty($api_key)) {
            // Allow clearing the key
            delete_option('zonatech_otapay_api_key');
            wp_send_json_success(array('message' => 'OtaPay API key removed.'));
        }
        
        // Save the API key
        update_option('zonatech_otapay_api_key', $api_key);
        $this->api_key = $api_key;
        
        // Test the API key
        $test_response = $this->make_request('/balance', array(), 'GET');
        
        if ($test_response && isset($test_response['success']) && $test_response['success']) {
            wp_send_json_success(array(
                'message' => 'OtaPay API key saved and verified successfully!',
                'balance' => $test_response['data']['balance'] ?? 'N/A'
            ));
        } else {
            // Still save the key but warn about verification
            wp_send_json_success(array(
                'message' => 'OtaPay API key saved. Note: Could not verify key - please ensure it is correct.',
                'warning' => true
            ));
        }
    }
    
    /**
     * Get OtaPay wallet balance
     */
    public function get_balance() {
        if (!$this->is_configured()) {
            return null;
        }
        
        $response = $this->make_request('/balance', array(), 'GET');
        
        if ($response && isset($response['success']) && $response['success']) {
            return $response['data']['balance'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Get supported card types
     */
    public static function get_supported_cards() {
        return array(
            'waec' => array(
                'name' => 'WAEC',
                'full_name' => 'WAEC Result Checker PIN',
                'description' => 'Check your WAEC SSCE/GCE result instantly',
                'price' => ZONATECH_WAEC_CARD_PRICE,
                'icon' => 'fas fa-credit-card',
                'color' => '#22c55e',
                'provider' => 'otapay'
            ),
            'neco' => array(
                'name' => 'NECO',
                'full_name' => 'NECO Result Checker PIN',
                'description' => 'Check your NECO SSCE/GCE result instantly',
                'price' => ZONATECH_NECO_CARD_PRICE,
                'icon' => 'fas fa-id-card',
                'color' => '#f59e0b',
                'provider' => 'otapay'
            )
        );
    }
}
