<?php
/**
 * Scratch Cards Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_Scratch_Cards {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_zonatech_purchase_scratch_card', array($this, 'purchase_scratch_card'));
        add_action('wp_ajax_zonatech_get_user_cards', array($this, 'get_user_cards'));
    }
    
    public function purchase_scratch_card() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to purchase scratch card.'));
        }
        
        $card_type = sanitize_text_field($_POST['card_type'] ?? '');
        
        $valid_types = array('waec', 'neco', 'jamb');
        
        if (!in_array($card_type, $valid_types)) {
            wp_send_json_error(array('message' => 'Invalid card type.'));
        }
        
        // Check availability
        global $wpdb;
        $table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
        
        $available = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_cards WHERE card_type = %s AND status = 'available'",
            $card_type
        ));
        
        if ($available < 1) {
            wp_send_json_error(array('message' => 'Sorry, this card type is currently out of stock.'));
        }
        
        $user_id = get_current_user_id();
        
        ZonaTech_Activity_Log::log(
            $user_id,
            'scratch_card_request',
            sprintf('Requested %s scratch card', strtoupper($card_type))
        );
        
        wp_send_json_success(array(
            'message' => 'Please complete payment to receive your scratch card.',
            'require_payment' => true,
            'payment_type' => 'scratch_card',
            'amount' => ZONATECH_SCRATCH_CARD_PRICE,
            'meta_data' => array(
                'card_type' => $card_type
            )
        ));
    }
    
    public function get_user_cards() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login.'));
        }
        
        $user_id = get_current_user_id();
        
        global $wpdb;
        $table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
        
        $cards = $wpdb->get_results($wpdb->prepare(
            "SELECT id, card_type, pin, serial_number, sold_at 
             FROM $table_cards 
             WHERE user_id = %d AND status = 'sold' 
             ORDER BY sold_at DESC",
            $user_id
        ));
        
        wp_send_json_success(array('cards' => $cards));
    }
    
    public static function get_card_types() {
        return array(
            'waec' => array(
                'name' => 'WAEC',
                'full_name' => 'WAEC Result Checker PIN',
                'description' => 'Check your WAEC result with this PIN',
                'price' => ZONATECH_SCRATCH_CARD_PRICE,
                'icon' => 'fas fa-credit-card',
                'color' => '#22c55e'
            ),
            'neco' => array(
                'name' => 'NECO',
                'full_name' => 'NECO Result Checker PIN',
                'description' => 'Check your NECO result with this PIN',
                'price' => ZONATECH_SCRATCH_CARD_PRICE,
                'icon' => 'fas fa-id-card',
                'color' => '#f59e0b'
            ),
            'jamb' => array(
                'name' => 'JAMB',
                'full_name' => 'JAMB e-PIN',
                'description' => 'Use for JAMB registration and services',
                'price' => ZONATECH_SCRATCH_CARD_PRICE,
                'icon' => 'fas fa-key',
                'color' => '#8b5cf6'
            )
        );
    }
    
    public static function get_user_purchased_cards($user_id) {
        global $wpdb;
        $table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT id, card_type, pin, serial_number, sold_at 
             FROM $table_cards 
             WHERE user_id = %d AND status = 'sold' 
             ORDER BY sold_at DESC",
            $user_id
        ));
    }
}
