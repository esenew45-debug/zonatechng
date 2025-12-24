<?php
/**
 * User Authentication Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_User_Auth {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_nopriv_zonatech_register', array($this, 'handle_register'));
        add_action('wp_ajax_nopriv_zonatech_login', array($this, 'handle_login'));
        add_action('wp_ajax_zonatech_logout', array($this, 'handle_logout'));
        add_action('wp_ajax_nopriv_zonatech_reset_password', array($this, 'handle_reset_password'));
        add_action('wp_ajax_zonatech_update_profile', array($this, 'handle_update_profile'));
        add_action('wp_ajax_zonatech_change_password', array($this, 'handle_change_password'));
    }
    
    public function handle_register() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'All fields are required.'));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        }
        
        if (email_exists($email)) {
            wp_send_json_error(array('message' => 'Email address already exists.'));
        }
        
        if (strlen($password) < 6) {
            wp_send_json_error(array('message' => 'Password must be at least 6 characters.'));
        }
        
        if ($password !== $confirm_password) {
            wp_send_json_error(array('message' => 'Passwords do not match.'));
        }
        
        // Create user
        $username = sanitize_user(strtolower($first_name . $last_name) . wp_rand(100, 999));
        
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }
        
        // Update user meta
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ));
        
        update_user_meta($user_id, 'phone', $phone);
        update_user_meta($user_id, 'zonatech_registered', current_time('mysql'));
        
        // Log activity
        ZonaTech_Activity_Log::log($user_id, 'registration', 'User registered successfully');
        
        // Auto login
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        wp_send_json_success(array(
            'message' => 'Registration successful!',
            'redirect' => home_url('/zonatech-dashboard/')
        ));
    }
    
    public function handle_login() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'true';
        
        if (empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'Email and password are required.'));
        }
        
        $user = get_user_by('email', $email);
        
        if (!$user) {
            wp_send_json_error(array('message' => 'Invalid email or password.'));
        }
        
        $credentials = array(
            'user_login' => $user->user_login,
            'user_password' => $password,
            'remember' => $remember
        );
        
        $login = wp_signon($credentials, is_ssl());
        
        if (is_wp_error($login)) {
            wp_send_json_error(array('message' => 'Invalid email or password.'));
        }
        
        // Log activity
        ZonaTech_Activity_Log::log($user->ID, 'login', 'User logged in');
        
        wp_send_json_success(array(
            'message' => 'Login successful!',
            'redirect' => home_url('/zonatech-dashboard/')
        ));
    }
    
    public function handle_logout() {
        $user_id = get_current_user_id();
        
        if ($user_id) {
            ZonaTech_Activity_Log::log($user_id, 'logout', 'User logged out');
        }
        
        wp_logout();
        
        wp_send_json_success(array(
            'message' => 'Logged out successfully.',
            'redirect' => home_url('/zonatech-login/')
        ));
    }
    
    public function handle_reset_password() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email'] ?? '');
        
        if (empty($email)) {
            wp_send_json_error(array('message' => 'Email address is required.'));
        }
        
        $user = get_user_by('email', $email);
        
        if (!$user) {
            // Don't reveal if email exists or not for security
            wp_send_json_success(array(
                'message' => 'If this email exists, a password reset link will be sent.'
            ));
        }
        
        // Generate reset key
        $reset_key = get_password_reset_key($user);
        
        if (is_wp_error($reset_key)) {
            wp_send_json_error(array('message' => 'Error generating reset link. Please try again.'));
        }
        
        // Send reset email
        $reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');
        
        $message = "Hi " . $user->display_name . ",\n\n";
        $message .= "You requested a password reset for your ZonaTech NG account.\n\n";
        $message .= "Click the link below to reset your password:\n";
        $message .= $reset_url . "\n\n";
        $message .= "If you didn't request this, please ignore this email.\n\n";
        $message .= "Thanks,\nZonaTech NG Team";
        
        $sent = wp_mail($email, 'Password Reset - ZonaTech NG', $message);
        
        ZonaTech_Activity_Log::log($user->ID, 'password_reset_request', 'Password reset requested');
        
        wp_send_json_success(array(
            'message' => 'If this email exists, a password reset link will be sent.'
        ));
    }
    
    public function handle_update_profile() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to update your profile.'));
        }
        
        $user_id = get_current_user_id();
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        
        if (empty($first_name) || empty($last_name)) {
            wp_send_json_error(array('message' => 'First name and last name are required.'));
        }
        
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ));
        
        update_user_meta($user_id, 'phone', $phone);
        
        ZonaTech_Activity_Log::log($user_id, 'profile_update', 'Profile updated');
        
        wp_send_json_success(array('message' => 'Profile updated successfully!'));
    }
    
    public function handle_change_password() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to change your password.'));
        }
        
        $user_id = get_current_user_id();
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            wp_send_json_error(array('message' => 'All fields are required.'));
        }
        
        $user = get_user_by('id', $user_id);
        
        if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
            wp_send_json_error(array('message' => 'Current password is incorrect.'));
        }
        
        if (strlen($new_password) < 6) {
            wp_send_json_error(array('message' => 'New password must be at least 6 characters.'));
        }
        
        if ($new_password !== $confirm_password) {
            wp_send_json_error(array('message' => 'New passwords do not match.'));
        }
        
        wp_set_password($new_password, $user_id);
        
        // Re-login user
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        ZonaTech_Activity_Log::log($user_id, 'password_change', 'Password changed');
        
        wp_send_json_success(array('message' => 'Password changed successfully!'));
    }
    
    public static function get_user_dashboard_data() {
        if (!is_user_logged_in()) {
            return null;
        }
        
        global $wpdb;
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        
        // Get purchase count
        $table_purchases = $wpdb->prefix . 'zonatech_purchases';
        $purchase_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_purchases WHERE user_id = %d AND status = 'completed'",
            $user_id
        ));
        
        // Get quiz count
        $table_quiz = $wpdb->prefix . 'zonatech_quiz_results';
        $quiz_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_quiz WHERE user_id = %d",
            $user_id
        ));
        
        // Get total spent
        $total_spent = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM $table_purchases WHERE user_id = %d AND status = 'completed'",
            $user_id
        )) ?? 0;
        
        // Get accessible subjects count
        $table_access = $wpdb->prefix . 'zonatech_user_access';
        $subjects_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_access WHERE user_id = %d",
            $user_id
        ));
        
        return array(
            'user' => array(
                'id' => $user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'display_name' => $user->display_name,
                'email' => $user->user_email,
                'phone' => get_user_meta($user_id, 'phone', true),
                'avatar' => get_avatar_url($user_id, array('size' => 150)),
                'registered' => get_user_meta($user_id, 'zonatech_registered', true)
            ),
            'stats' => array(
                'purchases' => (int) $purchase_count,
                'quizzes' => (int) $quiz_count,
                'total_spent' => (float) $total_spent,
                'subjects' => (int) $subjects_count
            )
        );
    }
}
