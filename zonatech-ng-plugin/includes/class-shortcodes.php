<?php
/**
 * Shortcodes Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_Shortcodes {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('zonatech_login', array($this, 'render_login'));
        add_shortcode('zonatech_register', array($this, 'render_register'));
        add_shortcode('zonatech_dashboard', array($this, 'render_dashboard'));
        add_shortcode('zonatech_past_questions', array($this, 'render_past_questions'));
        add_shortcode('zonatech_nin_service', array($this, 'render_nin_service'));
        add_shortcode('zonatech_scratch_cards', array($this, 'render_scratch_cards'));
        add_shortcode('zonatech_payment', array($this, 'render_payment'));
        add_shortcode('zonatech_homepage', array($this, 'render_homepage'));
    }
    
    public function render_login() {
        if (is_user_logged_in()) {
            wp_redirect(site_url('/zonatech-dashboard/'));
            exit;
        }
        
        ob_start();
        include ZONATECH_PLUGIN_DIR . 'templates/login.php';
        return ob_get_clean();
    }
    
    public function render_register() {
        if (is_user_logged_in()) {
            wp_redirect(site_url('/zonatech-dashboard/'));
            exit;
        }
        
        ob_start();
        include ZONATECH_PLUGIN_DIR . 'templates/register.php';
        return ob_get_clean();
    }
    
    public function render_dashboard() {
        if (!is_user_logged_in()) {
            wp_redirect(site_url('/zonatech-login/'));
            exit;
        }
        
        $user_data = ZonaTech_User_Auth::get_user_dashboard_data();
        
        ob_start();
        include ZONATECH_PLUGIN_DIR . 'templates/dashboard.php';
        return ob_get_clean();
    }
    
    public function render_past_questions() {
        // Allow guest users to view past questions
        // They will be prompted to register when trying to access content
        $exam_types = ZonaTech_Past_Questions::get_exam_types();
        $is_guest = !is_user_logged_in();
        
        ob_start();
        include ZONATECH_PLUGIN_DIR . 'templates/past-questions.php';
        return ob_get_clean();
    }
    
    public function render_nin_service() {
        // Allow guest users to view NIN service page
        // They will be prompted to register when trying to use the service
        $is_guest = !is_user_logged_in();
        
        ob_start();
        include ZONATECH_PLUGIN_DIR . 'templates/nin-service.php';
        return ob_get_clean();
    }
    
    public function render_scratch_cards() {
        // Allow guest users to view scratch cards page
        // They will be prompted to register when trying to purchase
        $card_types = ZonaTech_Scratch_Cards::get_card_types();
        $is_guest = !is_user_logged_in();
        
        ob_start();
        include ZONATECH_PLUGIN_DIR . 'templates/scratch-cards.php';
        return ob_get_clean();
    }
    
    public function render_payment() {
        if (!is_user_logged_in()) {
            wp_redirect(site_url('/zonatech-login/?redirect=payment'));
            exit;
        }
        
        ob_start();
        include ZONATECH_PLUGIN_DIR . 'templates/payment.php';
        return ob_get_clean();
    }
    
    public function render_homepage() {
        $exam_types = ZonaTech_Past_Questions::get_exam_types();
        $card_types = ZonaTech_Scratch_Cards::get_card_types();
        
        ob_start();
        include ZONATECH_PLUGIN_DIR . 'templates/homepage.php';
        return ob_get_clean();
    }
}
