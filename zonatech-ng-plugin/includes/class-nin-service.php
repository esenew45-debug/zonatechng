<?php
/**
 * NIN Service Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_NIN_Service {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_zonatech_verify_nin', array($this, 'verify_nin'));
        add_action('wp_ajax_zonatech_request_nin_slip', array($this, 'request_nin_slip'));
        add_action('wp_ajax_zonatech_get_nin_requests', array($this, 'get_user_nin_requests'));
    }
    
    public function verify_nin() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to verify NIN.'));
        }
        
        $nin = sanitize_text_field($_POST['nin'] ?? '');
        
        if (empty($nin)) {
            wp_send_json_error(array('message' => 'NIN number is required.'));
        }
        
        // Validate NIN format (11 digits)
        if (!preg_match('/^\d{11}$/', $nin)) {
            wp_send_json_error(array('message' => 'Invalid NIN format. NIN must be 11 digits.'));
        }
        
        // In production, this would connect to NIMC API or a verification service
        // For demo purposes, we'll simulate verification
        $verified = $this->simulate_nin_verification($nin);
        
        if (!$verified['status']) {
            wp_send_json_error(array('message' => $verified['message']));
        }
        
        $user_id = get_current_user_id();
        ZonaTech_Activity_Log::log($user_id, 'nin_verification', 'NIN verification attempted');
        
        wp_send_json_success(array(
            'message' => 'NIN verified successfully!',
            'data' => $verified['data']
        ));
    }
    
    public function request_nin_slip() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to request NIN slip.'));
        }
        
        $nin = sanitize_text_field($_POST['nin'] ?? '');
        
        if (empty($nin)) {
            wp_send_json_error(array('message' => 'NIN number is required.'));
        }
        
        if (!preg_match('/^\d{11}$/', $nin)) {
            wp_send_json_error(array('message' => 'Invalid NIN format.'));
        }
        
        $user_id = get_current_user_id();
        
        global $wpdb;
        $table_nin = $wpdb->prefix . 'zonatech_nin_requests';
        
        // Check for existing pending request
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_nin WHERE user_id = %d AND nin_number = %s AND status IN ('pending', 'paid')",
            $user_id,
            $nin
        ));
        
        if ($existing) {
            if ($existing->status === 'paid') {
                wp_send_json_success(array(
                    'message' => 'NIN slip is being processed.',
                    'status' => 'processing'
                ));
            }
            wp_send_json_error(array('message' => 'You already have a pending request for this NIN.'));
        }
        
        // Create new request
        $wpdb->insert($table_nin, array(
            'user_id' => $user_id,
            'nin_number' => $nin,
            'status' => 'pending'
        ));
        
        $request_id = $wpdb->insert_id;
        
        ZonaTech_Activity_Log::log($user_id, 'nin_slip_request', 'Premium NIN slip requested');
        
        wp_send_json_success(array(
            'message' => 'NIN slip request created. Please complete payment.',
            'request_id' => $request_id,
            'require_payment' => true,
            'payment_type' => 'nin_slip',
            'amount' => ZONATECH_NIN_SLIP_PRICE,
            'meta_data' => array(
                'nin_number' => $nin,
                'request_id' => $request_id
            )
        ));
    }
    
    public function get_user_nin_requests() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login.'));
        }
        
        $user_id = get_current_user_id();
        
        global $wpdb;
        $table_nin = $wpdb->prefix . 'zonatech_nin_requests';
        
        $requests = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_nin WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
        
        wp_send_json_success(array('requests' => $requests));
    }
    
    private function simulate_nin_verification($nin) {
        /**
         * IMPORTANT: This is a SIMULATION for demonstration purposes only.
         * 
         * For PRODUCTION use, integrate with one of these official services:
         * - NIMC Official API (nimc.gov.ng)
         * - Dojah (dojah.io)
         * - Prembly/Identitypass (prembly.com)
         * - Youverify (youverify.co)
         * 
         * The simulation below should NOT be used in production as it does not
         * perform actual NIN verification and could result in invalid data.
         */
        
        // Basic format validation
        if (strlen($nin) !== 11) {
            return array(
                'status' => false,
                'message' => 'Invalid NIN format. NIN must be 11 digits.'
            );
        }
        
        // Validate all characters are digits
        if (!ctype_digit($nin)) {
            return array(
                'status' => false,
                'message' => 'Invalid NIN. Only digits are allowed.'
            );
        }
        
        // Additional basic validation - NIN shouldn't start with 0
        if ($nin[0] === '0') {
            return array(
                'status' => false,
                'message' => 'Invalid NIN format.'
            );
        }
        
        // Simulate verification response (DEMO ONLY)
        // In production, this would call the actual verification API
        return array(
            'status' => true,
            'message' => 'NIN format validated. (Demo Mode - Production requires API integration)',
            'data' => array(
                'nin' => $nin,
                'verified' => true,
                'name' => '[DEMO] Verification pending API integration',
                'note' => 'Pay to download premium NIN slip. Full verification requires production API setup.',
                'demo_mode' => true
            )
        );
    }
    
    public static function get_user_nin_history($user_id) {
        global $wpdb;
        $table_nin = $wpdb->prefix . 'zonatech_nin_requests';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_nin WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
    }
}
