<?php
/**
 * Past Questions Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_Past_Questions {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_zonatech_get_subjects', array($this, 'get_subjects'));
        add_action('wp_ajax_nopriv_zonatech_get_subjects', array($this, 'get_subjects'));
        add_action('wp_ajax_zonatech_get_years', array($this, 'get_years'));
        add_action('wp_ajax_nopriv_zonatech_get_years', array($this, 'get_years'));
        add_action('wp_ajax_zonatech_get_questions', array($this, 'get_questions'));
        add_action('wp_ajax_zonatech_search_questions', array($this, 'search_questions'));
        add_action('wp_ajax_zonatech_check_access', array($this, 'check_access'));
    }
    
    public function get_subjects() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        $exam_type = sanitize_text_field($_POST['exam_type'] ?? '');
        
        if (empty($exam_type)) {
            wp_send_json_error(array('message' => 'Exam type is required.'));
        }
        
        global $wpdb;
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        
        $subjects = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT subject FROM $table_questions WHERE exam_type = %s ORDER BY subject",
            $exam_type
        ));
        
        wp_send_json_success(array('subjects' => $subjects));
    }
    
    public function get_years() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        $exam_type = sanitize_text_field($_POST['exam_type'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        
        if (empty($exam_type)) {
            wp_send_json_error(array('message' => 'Exam type is required.'));
        }
        
        global $wpdb;
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        
        if (!empty($subject)) {
            $years = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT year FROM $table_questions WHERE exam_type = %s AND subject = %s ORDER BY year DESC",
                $exam_type,
                $subject
            ));
        } else {
            $years = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT year FROM $table_questions WHERE exam_type = %s ORDER BY year DESC",
                $exam_type
            ));
        }
        
        wp_send_json_success(array('years' => $years));
    }
    
    public function get_questions() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to access questions.'));
        }
        
        $user_id = get_current_user_id();
        $exam_type = sanitize_text_field($_POST['exam_type'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        
        if (empty($exam_type) || empty($subject) || $year < 2010) {
            wp_send_json_error(array('message' => 'Invalid request parameters.'));
        }
        
        // Check if user has access
        if (!$this->user_has_access($user_id, $exam_type, $subject)) {
            wp_send_json_error(array(
                'message' => 'You need to purchase access to this subject.',
                'require_payment' => true,
                'exam_type' => $exam_type,
                'subject' => $subject
            ));
        }
        
        global $wpdb;
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        
        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT id, question_text, option_a, option_b, option_c, option_d 
             FROM $table_questions 
             WHERE exam_type = %s AND subject = %s AND year = %d 
             ORDER BY id",
            $exam_type,
            $subject,
            $year
        ));
        
        // Log activity
        ZonaTech_Activity_Log::log(
            $user_id,
            'view_questions',
            sprintf('Accessed %s %s questions for %d', strtoupper($exam_type), $subject, $year)
        );
        
        wp_send_json_success(array(
            'questions' => $questions,
            'total' => count($questions),
            'exam_type' => strtoupper($exam_type),
            'subject' => $subject,
            'year' => $year
        ));
    }
    
    public function search_questions() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to search questions.'));
        }
        
        $search_term = sanitize_text_field($_POST['search'] ?? '');
        $exam_type = sanitize_text_field($_POST['exam_type'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = 20;
        
        if (empty($search_term) && empty($exam_type)) {
            wp_send_json_error(array('message' => 'Please provide search criteria.'));
        }
        
        global $wpdb;
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        
        $where = array('1=1');
        $params = array();
        
        if (!empty($search_term)) {
            $where[] = 'question_text LIKE %s';
            $params[] = '%' . $wpdb->esc_like($search_term) . '%';
        }
        
        if (!empty($exam_type)) {
            $where[] = 'exam_type = %s';
            $params[] = $exam_type;
        }
        
        if (!empty($subject)) {
            $where[] = 'subject = %s';
            $params[] = $subject;
        }
        
        if ($year > 0) {
            $where[] = 'year = %d';
            $params[] = $year;
        }
        
        $where_clause = implode(' AND ', $where);
        $offset = ($page - 1) * $per_page;
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM $table_questions WHERE $where_clause";
        if (!empty($params)) {
            $count_sql = $wpdb->prepare($count_sql, $params);
        }
        $total = $wpdb->get_var($count_sql);
        
        // Get results
        $params[] = $per_page;
        $params[] = $offset;
        
        $sql = "SELECT id, exam_type, subject, year, question_text 
                FROM $table_questions 
                WHERE $where_clause 
                ORDER BY year DESC, subject 
                LIMIT %d OFFSET %d";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $params));
        
        wp_send_json_success(array(
            'questions' => $results,
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ));
    }
    
    public function check_access() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('has_access' => false));
        }
        
        $user_id = get_current_user_id();
        $exam_type = sanitize_text_field($_POST['exam_type'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        
        $has_access = $this->user_has_access($user_id, $exam_type, $subject);
        
        wp_send_json_success(array('has_access' => $has_access));
    }
    
    public function user_has_access($user_id, $exam_type, $subject) {
        global $wpdb;
        $table_access = $wpdb->prefix . 'zonatech_user_access';
        
        $access = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_access 
             WHERE user_id = %d AND exam_type = %s AND subject = %s 
             AND (expires_at IS NULL OR expires_at > NOW())",
            $user_id,
            $exam_type,
            $subject
        ));
        
        return (int) $access > 0;
    }
    
    public static function get_exam_types() {
        return array(
            'jamb' => array(
                'name' => 'JAMB',
                'full_name' => 'Joint Admissions and Matriculation Board',
                'icon' => 'fas fa-graduation-cap',
                'color' => '#8b5cf6'
            ),
            'waec' => array(
                'name' => 'WAEC',
                'full_name' => 'West African Examinations Council',
                'icon' => 'fas fa-book-open',
                'color' => '#22c55e'
            ),
            'neco' => array(
                'name' => 'NECO',
                'full_name' => 'National Examinations Council',
                'icon' => 'fas fa-scroll',
                'color' => '#f59e0b'
            )
        );
    }
    
    public static function get_user_accessible_subjects($user_id) {
        global $wpdb;
        $table_access = $wpdb->prefix . 'zonatech_user_access';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT exam_type, subject, created_at, expires_at 
             FROM $table_access 
             WHERE user_id = %d 
             ORDER BY exam_type, subject",
            $user_id
        ));
    }
}
