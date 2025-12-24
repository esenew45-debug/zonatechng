<?php
/**
 * Quiz System Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_Quiz_System {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_zonatech_start_quiz', array($this, 'start_quiz'));
        add_action('wp_ajax_zonatech_submit_quiz', array($this, 'submit_quiz'));
        add_action('wp_ajax_zonatech_get_corrections', array($this, 'get_corrections'));
        add_action('wp_ajax_zonatech_get_quiz_history', array($this, 'get_quiz_history'));
    }
    
    public function start_quiz() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login to take quiz.'));
        }
        
        $user_id = get_current_user_id();
        $exam_type = sanitize_text_field($_POST['exam_type'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        
        if (empty($exam_type) || empty($subject) || $year < 2010) {
            wp_send_json_error(array('message' => 'Invalid quiz parameters.'));
        }
        
        // Check access
        $past_questions = ZonaTech_Past_Questions::get_instance();
        if (!$past_questions->user_has_access($user_id, $exam_type, $subject)) {
            wp_send_json_error(array(
                'message' => 'You need to purchase access to this subject first.',
                'require_payment' => true
            ));
        }
        
        global $wpdb;
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        
        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT id, question_text, option_a, option_b, option_c, option_d 
             FROM $table_questions 
             WHERE exam_type = %s AND subject = %s AND year = %d 
             ORDER BY RAND()",
            $exam_type,
            $subject,
            $year
        ));
        
        if (empty($questions)) {
            wp_send_json_error(array('message' => 'No questions available for this selection.'));
        }
        
        ZonaTech_Activity_Log::log(
            $user_id,
            'quiz_start',
            sprintf('Started %s %s %d quiz', strtoupper($exam_type), $subject, $year)
        );
        
        wp_send_json_success(array(
            'questions' => $questions,
            'total' => count($questions),
            'exam_type' => strtoupper($exam_type),
            'subject' => $subject,
            'year' => $year,
            'time_limit' => count($questions) * 60 // 1 minute per question
        ));
    }
    
    public function submit_quiz() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login.'));
        }
        
        $user_id = get_current_user_id();
        $exam_type = sanitize_text_field($_POST['exam_type'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $answers = isset($_POST['answers']) ? json_decode(stripslashes($_POST['answers']), true) : array();
        $time_taken = intval($_POST['time_taken'] ?? 0);
        
        if (empty($exam_type) || empty($subject) || $year < 2010 || empty($answers)) {
            wp_send_json_error(array('message' => 'Invalid submission data.'));
        }
        
        global $wpdb;
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        
        // Get correct answers - validate all question IDs are integers
        $question_ids = array_keys($answers);
        $validated_ids = array();
        
        foreach ($question_ids as $id) {
            $int_id = intval($id);
            if ($int_id > 0) {
                $validated_ids[] = $int_id;
            }
        }
        
        if (empty($validated_ids)) {
            wp_send_json_error(array('message' => 'Invalid question IDs.'));
        }
        
        $placeholders = implode(',', array_fill(0, count($validated_ids), '%d'));
        
        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT id, correct_answer FROM $table_questions WHERE id IN ($placeholders)",
            $validated_ids
        ), OBJECT_K);
        
        $correct = 0;
        $wrong = 0;
        $results = array();
        
        foreach ($answers as $question_id => $user_answer) {
            $question_id = intval($question_id);
            if (isset($questions[$question_id])) {
                $is_correct = strtoupper($user_answer) === strtoupper($questions[$question_id]->correct_answer);
                if ($is_correct) {
                    $correct++;
                } else {
                    $wrong++;
                }
                $results[$question_id] = array(
                    'user_answer' => strtoupper($user_answer),
                    'correct_answer' => $questions[$question_id]->correct_answer,
                    'is_correct' => $is_correct
                );
            }
        }
        
        $total = count($answers);
        $score = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
        
        // Save quiz result
        $table_quiz = $wpdb->prefix . 'zonatech_quiz_results';
        $wpdb->insert($table_quiz, array(
            'user_id' => $user_id,
            'exam_type' => $exam_type,
            'subject' => $subject,
            'year' => $year,
            'total_questions' => $total,
            'correct_answers' => $correct,
            'wrong_answers' => $wrong,
            'score' => $score,
            'answers_data' => wp_json_encode($results),
            'time_taken' => $time_taken
        ));
        
        $result_id = $wpdb->insert_id;
        
        ZonaTech_Activity_Log::log(
            $user_id,
            'quiz_complete',
            sprintf('Completed %s %s %d quiz with score: %.2f%%', strtoupper($exam_type), $subject, $year, $score),
            array('result_id' => $result_id, 'score' => $score)
        );
        
        wp_send_json_success(array(
            'result_id' => $result_id,
            'score' => $score,
            'correct' => $correct,
            'wrong' => $wrong,
            'total' => $total,
            'time_taken' => $time_taken,
            'grade' => $this->get_grade($score),
            'message' => $this->get_score_message($score)
        ));
    }
    
    public function get_corrections() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login.'));
        }
        
        $user_id = get_current_user_id();
        $result_id = intval($_POST['result_id'] ?? 0);
        
        if ($result_id < 1) {
            wp_send_json_error(array('message' => 'Invalid result ID.'));
        }
        
        global $wpdb;
        $table_quiz = $wpdb->prefix . 'zonatech_quiz_results';
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_quiz WHERE id = %d AND user_id = %d",
            $result_id,
            $user_id
        ));
        
        if (!$result) {
            wp_send_json_error(array('message' => 'Quiz result not found.'));
        }
        
        $answers_data = json_decode($result->answers_data, true);
        
        // Get wrong answers only
        $wrong_ids = array();
        foreach ($answers_data as $q_id => $data) {
            if (!$data['is_correct']) {
                $wrong_ids[] = intval($q_id);
            }
        }
        
        if (empty($wrong_ids)) {
            wp_send_json_success(array(
                'message' => 'Congratulations! You got all answers correct!',
                'corrections' => array()
            ));
        }
        
        $placeholders = implode(',', array_fill(0, count($wrong_ids), '%d'));
        
        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation 
             FROM $table_questions 
             WHERE id IN ($placeholders)",
            $wrong_ids
        ));
        
        $corrections = array();
        foreach ($questions as $q) {
            $corrections[] = array(
                'id' => $q->id,
                'question' => $q->question_text,
                'options' => array(
                    'A' => $q->option_a,
                    'B' => $q->option_b,
                    'C' => $q->option_c,
                    'D' => $q->option_d
                ),
                'your_answer' => $answers_data[$q->id]['user_answer'],
                'correct_answer' => $q->correct_answer,
                'explanation' => $q->explanation
            );
        }
        
        ZonaTech_Activity_Log::log($user_id, 'view_corrections', 'Viewed quiz corrections', array('result_id' => $result_id));
        
        wp_send_json_success(array('corrections' => $corrections));
    }
    
    public function get_quiz_history() {
        check_ajax_referer('zonatech_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please login.'));
        }
        
        $user_id = get_current_user_id();
        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = 10;
        $offset = ($page - 1) * $per_page;
        
        global $wpdb;
        $table_quiz = $wpdb->prefix . 'zonatech_quiz_results';
        
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_quiz WHERE user_id = %d",
            $user_id
        ));
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT id, exam_type, subject, year, total_questions, correct_answers, wrong_answers, score, time_taken, created_at 
             FROM $table_quiz 
             WHERE user_id = %d 
             ORDER BY created_at DESC 
             LIMIT %d OFFSET %d",
            $user_id,
            $per_page,
            $offset
        ));
        
        foreach ($results as &$r) {
            $r->grade = $this->get_grade($r->score);
        }
        
        wp_send_json_success(array(
            'results' => $results,
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ));
    }
    
    private function get_grade($score) {
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'D';
        if ($score >= 40) return 'E';
        return 'F';
    }
    
    private function get_score_message($score) {
        if ($score >= 90) return 'Outstanding! You\'re a genius!';
        if ($score >= 70) return 'Excellent performance! Keep it up!';
        if ($score >= 60) return 'Good job! You\'re doing well!';
        if ($score >= 50) return 'Fair performance. Keep studying!';
        if ($score >= 40) return 'You need more practice. Don\'t give up!';
        return 'Keep trying! Review the corrections and try again.';
    }
    
    public static function get_user_quiz_stats($user_id) {
        global $wpdb;
        $table_quiz = $wpdb->prefix . 'zonatech_quiz_results';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_quizzes,
                AVG(score) as average_score,
                MAX(score) as best_score,
                SUM(correct_answers) as total_correct,
                SUM(wrong_answers) as total_wrong
             FROM $table_quiz 
             WHERE user_id = %d",
            $user_id
        ));
    }
}
