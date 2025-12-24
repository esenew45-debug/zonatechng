<?php
/**
 * Admin Panel Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'ZonaTech NG',
            'ZonaTech NG',
            'manage_options',
            'zonatech-ng',
            array($this, 'render_dashboard'),
            'dashicons-welcome-learn-more',
            30
        );
        
        add_submenu_page(
            'zonatech-ng',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'zonatech-ng',
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            'zonatech-ng',
            'Questions',
            'Questions',
            'manage_options',
            'zonatech-questions',
            array($this, 'render_questions')
        );
        
        add_submenu_page(
            'zonatech-ng',
            'Scratch Cards',
            'Scratch Cards',
            'manage_options',
            'zonatech-cards',
            array($this, 'render_cards')
        );
        
        add_submenu_page(
            'zonatech-ng',
            'Activity Log',
            'Activity Log',
            'manage_options',
            'zonatech-activity',
            array($this, 'render_activity')
        );
        
        add_submenu_page(
            'zonatech-ng',
            'Settings',
            'Settings',
            'manage_options',
            'zonatech-settings',
            array($this, 'render_settings')
        );
    }
    
    public function register_settings() {
        register_setting('zonatech_settings', 'zonatech_paystack_public_key');
        register_setting('zonatech_settings', 'zonatech_paystack_secret_key');
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'zonatech') === false) {
            return;
        }
        
        wp_enqueue_style('zonatech-admin', ZONATECH_PLUGIN_URL . 'assets/css/admin.css', array(), ZONATECH_VERSION);
        wp_enqueue_script('zonatech-admin', ZONATECH_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), ZONATECH_VERSION, true);
        
        wp_localize_script('zonatech-admin', 'zonatech_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('zonatech_nonce')
        ));
    }
    
    public function render_dashboard() {
        global $wpdb;
        
        // Get statistics
        $users_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
        
        $table_purchases = $wpdb->prefix . 'zonatech_purchases';
        $total_revenue = $wpdb->get_var("SELECT SUM(amount) FROM $table_purchases WHERE status = 'completed'") ?? 0;
        $purchases_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_purchases WHERE status = 'completed'");
        
        $table_quiz = $wpdb->prefix . 'zonatech_quiz_results';
        $quizzes_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_quiz");
        
        $recent_activities = ZonaTech_Activity_Log::get_recent_activities(20);
        
        include ZONATECH_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    public function render_questions() {
        include ZONATECH_PLUGIN_DIR . 'admin/views/questions.php';
    }
    
    public function render_cards() {
        include ZONATECH_PLUGIN_DIR . 'admin/views/cards.php';
    }
    
    public function render_activity() {
        include ZONATECH_PLUGIN_DIR . 'admin/views/activity.php';
    }
    
    public function render_settings() {
        include ZONATECH_PLUGIN_DIR . 'admin/views/settings.php';
    }
}
