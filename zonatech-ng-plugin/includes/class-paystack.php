<?php
/**
 * Paystack Integration Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_Paystack {
    
    private static $instance = null;
    private $secret_key;
    private $public_key;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->secret_key = get_option('zonatech_paystack_secret_key', '');
        $this->public_key = get_option('zonatech_paystack_public_key', '');
        
        add_action('wp_ajax_zonatech_initialize_payment', array($this, 'initialize_payment'));
        add_action('wp_ajax_zonatech_verify_payment', array($this, 'verify_payment'));
        add_action('wp_ajax_nopriv_zonatech_paystack_webhook', array($this, 'handle_webhook'));
        add_action('wp_ajax_zonatech_paystack_webhook', array($this, 'handle_webhook'));
    }
    
    public function initialize_payment() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to make payment.'));
            return;
        }
        
        // Check if Paystack is configured
        if (empty($this->public_key) || empty($this->secret_key)) {
            wp_send_json_error(array('message' => 'Payment system is not configured. Please contact support at ' . ZONATECH_SUPPORT_EMAIL));
            return;
        }
        
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        
        $payment_type = sanitize_text_field($_POST['payment_type'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $meta_data = isset($_POST['meta_data']) ? json_decode(stripslashes($_POST['meta_data']), true) : array();
        
        if (empty($payment_type) || $amount <= 0) {
            wp_send_json_error(array('message' => 'Invalid payment details.'));
        }
        
        // Validate amount based on payment type
        $valid_amounts = array(
            'subject' => ZONATECH_SUBJECT_PRICE,
            'nin_slip' => ZONATECH_NIN_SLIP_PRICE,
            'scratch_card' => ZONATECH_SCRATCH_CARD_PRICE
        );
        
        if (!isset($valid_amounts[$payment_type]) || $amount !== $valid_amounts[$payment_type]) {
            wp_send_json_error(array('message' => 'Invalid payment amount.'));
        }
        
        $reference = 'ZONA_' . time() . '_' . wp_rand(1000, 9999);
        
        // Create pending purchase record
        global $wpdb;
        $table_purchases = $wpdb->prefix . 'zonatech_purchases';
        
        $item_name = $this->get_item_name($payment_type, $meta_data);
        
        $wpdb->insert($table_purchases, array(
            'user_id' => $user_id,
            'purchase_type' => $payment_type,
            'item_name' => $item_name,
            'amount' => $amount,
            'reference' => $reference,
            'status' => 'pending',
            'meta_data' => wp_json_encode($meta_data)
        ));
        
        // Return data for Paystack inline
        wp_send_json_success(array(
            'reference' => $reference,
            'email' => $user->user_email,
            'amount' => $amount * 100, // Convert to kobo
            'public_key' => $this->public_key,
            'currency' => 'NGN',
            'metadata' => array(
                'user_id' => $user_id,
                'payment_type' => $payment_type,
                'item_name' => $item_name,
                'custom_fields' => array(
                    array(
                        'display_name' => 'Customer Name',
                        'variable_name' => 'customer_name',
                        'value' => $user->display_name
                    ),
                    array(
                        'display_name' => 'Payment Type',
                        'variable_name' => 'payment_type',
                        'value' => $payment_type
                    )
                )
            )
        ));
    }
    
    public function verify_payment() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to verify payment.'));
        }
        
        $reference = sanitize_text_field($_POST['reference'] ?? '');
        
        if (empty($reference)) {
            wp_send_json_error(array('message' => 'Invalid payment reference.'));
        }
        
        // Verify with Paystack API
        $response = wp_remote_get(
            'https://api.paystack.co/transaction/verify/' . $reference,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->secret_key
                )
            )
        );
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Could not verify payment. Please contact support.'));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!$body['status'] || $body['data']['status'] !== 'success') {
            wp_send_json_error(array('message' => 'Payment verification failed.'));
        }
        
        // Update purchase record
        global $wpdb;
        $table_purchases = $wpdb->prefix . 'zonatech_purchases';
        
        $purchase = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_purchases WHERE reference = %s",
            $reference
        ));
        
        if (!$purchase) {
            wp_send_json_error(array('message' => 'Purchase record not found.'));
        }
        
        if ($purchase->status === 'completed') {
            wp_send_json_success(array('message' => 'Payment already processed.'));
        }
        
        // Mark as completed
        $wpdb->update(
            $table_purchases,
            array('status' => 'completed'),
            array('reference' => $reference)
        );
        
        // Process the purchase based on type
        $this->process_purchase($purchase);
        
        // Log activity
        $user_id = get_current_user_id();
        ZonaTech_Activity_Log::log(
            $user_id,
            'payment_completed',
            sprintf('Payment of ₦%s completed for %s', number_format($purchase->amount), $purchase->item_name),
            array('reference' => $reference, 'amount' => $purchase->amount)
        );
        
        wp_send_json_success(array(
            'message' => 'Payment successful!',
            'purchase' => array(
                'type' => $purchase->purchase_type,
                'item' => $purchase->item_name,
                'amount' => $purchase->amount
            )
        ));
    }
    
    private function process_purchase($purchase) {
        global $wpdb;
        $meta_data = json_decode($purchase->meta_data, true);
        
        switch ($purchase->purchase_type) {
            case 'subject':
                // Grant access to subject
                $table_access = $wpdb->prefix . 'zonatech_user_access';
                $wpdb->insert($table_access, array(
                    'user_id' => $purchase->user_id,
                    'exam_type' => $meta_data['exam_type'] ?? '',
                    'subject' => $meta_data['subject'] ?? '',
                    'purchase_id' => $purchase->id,
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year'))
                ));
                break;
                
            case 'scratch_card':
                // Assign a scratch card
                $table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
                $card_type = $meta_data['card_type'] ?? '';
                
                $card = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $table_cards WHERE card_type = %s AND status = 'available' LIMIT 1",
                    $card_type
                ));
                
                if ($card) {
                    $wpdb->update(
                        $table_cards,
                        array(
                            'status' => 'sold',
                            'user_id' => $purchase->user_id,
                            'purchase_id' => $purchase->id,
                            'sold_at' => current_time('mysql')
                        ),
                        array('id' => $card->id)
                    );
                }
                break;
                
            case 'nin_slip':
                // Process NIN slip request
                $table_nin = $wpdb->prefix . 'zonatech_nin_requests';
                $wpdb->update(
                    $table_nin,
                    array(
                        'status' => 'paid',
                        'purchase_id' => $purchase->id
                    ),
                    array(
                        'user_id' => $purchase->user_id,
                        'nin_number' => $meta_data['nin_number'] ?? '',
                        'status' => 'pending'
                    )
                );
                break;
        }
    }
    
    public function handle_webhook() {
        // Get and validate input
        $input = file_get_contents('php://input');
        
        if (empty($input)) {
            $this->log_webhook_error('Empty webhook payload');
            http_response_code(400);
            exit('Empty payload');
        }
        
        // Verify webhook signature
        if (!$this->verify_webhook_signature($input)) {
            $this->log_webhook_error('Invalid signature', array(
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ));
            http_response_code(400);
            exit('Invalid signature');
        }
        
        // Decode and validate JSON
        $event = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log_webhook_error('Invalid JSON', array('error' => json_last_error_msg()));
            http_response_code(400);
            exit('Invalid JSON');
        }
        
        // Validate required fields
        if (!isset($event['event']) || !isset($event['data']['reference'])) {
            $this->log_webhook_error('Missing required fields', array('event' => $event));
            http_response_code(400);
            exit('Missing required fields');
        }
        
        if ($event['event'] === 'charge.success') {
            $reference = sanitize_text_field($event['data']['reference']);
            
            global $wpdb;
            $table_purchases = $wpdb->prefix . 'zonatech_purchases';
            
            $purchase = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_purchases WHERE reference = %s AND status = 'pending'",
                $reference
            ));
            
            if ($purchase) {
                $wpdb->update(
                    $table_purchases,
                    array('status' => 'completed'),
                    array('reference' => $reference)
                );
                
                $this->process_purchase($purchase);
                
                // Log successful webhook
                if (class_exists('ZonaTech_Activity_Log')) {
                    ZonaTech_Activity_Log::log(
                        $purchase->user_id,
                        'webhook_processed',
                        'Payment webhook processed successfully',
                        array('reference' => $reference)
                    );
                }
            }
        }
        
        http_response_code(200);
        exit('Webhook processed');
    }
    
    private function log_webhook_error($message, $data = array()) {
        // Log to WordPress error log
        error_log('ZonaTech Paystack Webhook Error: ' . $message . ' - ' . wp_json_encode($data));
    }
    
    private function verify_webhook_signature($input) {
        if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
            $this->log_webhook_error('Missing signature header');
            return false;
        }
        
        if (empty($this->secret_key)) {
            $this->log_webhook_error('Secret key not configured');
            return false;
        }
        
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'];
        $computed = hash_hmac('sha512', $input, $this->secret_key);
        
        return hash_equals($signature, $computed);
    }
    
    private function get_item_name($payment_type, $meta_data) {
        switch ($payment_type) {
            case 'subject':
                return sprintf(
                    '%s %s Past Questions',
                    strtoupper($meta_data['exam_type'] ?? ''),
                    $meta_data['subject'] ?? ''
                );
            case 'scratch_card':
                return sprintf('%s Scratch Card/PIN', strtoupper($meta_data['card_type'] ?? ''));
            case 'nin_slip':
                return 'Premium NIN Slip';
            default:
                return 'ZonaTech Purchase';
        }
    }
    
    public static function get_user_purchases($user_id, $limit = 10) {
        global $wpdb;
        $table_purchases = $wpdb->prefix . 'zonatech_purchases';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_purchases WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
            $user_id,
            $limit
        ));
    }
}

// Initialize
ZonaTech_Paystack::get_instance();
