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
        add_action('admin_notices', array($this, 'show_setup_notice'));
        
        // AJAX handler for saving Paystack keys from frontend admin dashboard
        add_action('wp_ajax_zonatech_save_paystack_keys', array($this, 'save_paystack_keys'));
    }
    
    public function show_setup_notice() {
        // Only show on ZonaTech pages or when keys are not configured
        $screen = get_current_screen();
        
        if (empty(ZONATECH_PAYSTACK_PUBLIC_KEY) || empty(ZONATECH_PAYSTACK_SECRET_KEY)) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>ZonaTech NG:</strong> Paystack API keys are not configured. 
                    Payment functionality will not work until you configure your keys.
                    <a href="<?php echo admin_url('admin.php?page=zonatech-settings'); ?>">Configure now</a>
                </p>
            </div>
            <?php
        }
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
            'Users',
            'Users',
            'manage_options',
            'zonatech-users',
            array($this, 'render_users')
        );
        
        add_submenu_page(
            'zonatech-ng',
            'Feedback',
            'Feedback',
            'manage_options',
            'zonatech-feedback',
            array($this, 'render_feedback')
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
    
    public function render_users() {
        include ZONATECH_PLUGIN_DIR . 'admin/views/users.php';
    }
    
    public function render_feedback() {
        include ZONATECH_PLUGIN_DIR . 'admin/views/feedback.php';
    }
    
    public function render_settings() {
        include ZONATECH_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * AJAX handler to save Paystack keys from frontend admin dashboard
     */
    public function save_paystack_keys() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'zonatech_save_paystack')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check if user can manage options
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to change settings.'));
        }
        
        $public_key = sanitize_text_field($_POST['public_key'] ?? '');
        $secret_key = sanitize_text_field($_POST['secret_key'] ?? '');
        
        // Validate public key format if not empty
        if (!empty($public_key) && !preg_match('/^pk_(test|live)_/', $public_key)) {
            wp_send_json_error(array('message' => 'Invalid public key format. It should start with pk_test_ or pk_live_'));
        }
        
        // Validate secret key format if not empty
        if (!empty($secret_key) && !preg_match('/^sk_(test|live)_/', $secret_key)) {
            wp_send_json_error(array('message' => 'Invalid secret key format. It should start with sk_test_ or sk_live_'));
        }
        
        // Save the keys
        update_option('zonatech_paystack_public_key', $public_key);
        update_option('zonatech_paystack_secret_key', $secret_key);
        
        // Log the action
        if (class_exists('ZonaTech_Activity_Log')) {
            ZonaTech_Activity_Log::log(
                get_current_user_id(),
                'settings_update',
                'Paystack API keys updated',
                array('test_mode' => strpos($public_key, 'pk_test_') === 0)
            );
        }
        
        wp_send_json_success(array('message' => 'Paystack keys saved successfully!'));
    }
}
