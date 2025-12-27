<?php
/**
 * Frontend Admin Dashboard Template
 * Allows admins to view analytics without accessing WordPress admin
 */

if (!defined('ABSPATH')) exit;

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_redirect(site_url('/zonatech-dashboard/'));
    exit;
}

global $wpdb;

// Handle form submissions
$message = '';
$message_type = '';

// Handle single question addition
if (isset($_POST['add_single_question']) && wp_verify_nonce($_POST['question_nonce'], 'zonatech_add_question')) {
    $exam_type = sanitize_text_field($_POST['exam_type']);
    $subject = sanitize_text_field($_POST['subject']);
    $year = intval($_POST['year']);
    $question_text = sanitize_textarea_field($_POST['question_text']);
    $option_a = sanitize_text_field($_POST['option_a']);
    $option_b = sanitize_text_field($_POST['option_b']);
    $option_c = sanitize_text_field($_POST['option_c']);
    $option_d = sanitize_text_field($_POST['option_d']);
    $correct_answer = sanitize_text_field($_POST['correct_answer']);
    $explanation = sanitize_textarea_field($_POST['explanation']);
    
    $table_questions = $wpdb->prefix . 'zonatech_questions';
    
    $result = $wpdb->insert($table_questions, array(
        'exam_type' => $exam_type,
        'subject' => $subject,
        'year' => $year,
        'question_text' => $question_text,
        'option_a' => $option_a,
        'option_b' => $option_b,
        'option_c' => $option_c,
        'option_d' => $option_d,
        'correct_answer' => $correct_answer,
        'explanation' => $explanation,
        'created_at' => current_time('mysql')
    ));
    
    if ($result) {
        $message = 'Question added successfully!';
        $message_type = 'success';
    } else {
        $message = 'Failed to add question. Please try again.';
        $message_type = 'error';
    }
}

// Handle bulk CSV upload
if (isset($_POST['bulk_upload_questions']) && wp_verify_nonce($_POST['bulk_nonce'], 'zonatech_bulk_upload')) {
    if (!empty($_FILES['csv_file']['tmp_name'])) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Skip header row
        
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        $success_count = 0;
        $error_count = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 9) {
                $result = $wpdb->insert($table_questions, array(
                    'exam_type' => sanitize_text_field($row[0]),
                    'subject' => sanitize_text_field($row[1]),
                    'year' => intval($row[2]),
                    'question_text' => sanitize_textarea_field($row[3]),
                    'option_a' => sanitize_text_field($row[4]),
                    'option_b' => sanitize_text_field($row[5]),
                    'option_c' => sanitize_text_field($row[6]),
                    'option_d' => sanitize_text_field($row[7]),
                    'correct_answer' => sanitize_text_field($row[8]),
                    'explanation' => isset($row[9]) ? sanitize_textarea_field($row[9]) : '',
                    'created_at' => current_time('mysql')
                ));
                
                if ($result) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        fclose($handle);
        
        $message = "Bulk upload completed: $success_count questions added successfully, $error_count failed.";
        $message_type = $error_count > 0 ? 'warning' : 'success';
    } else {
        $message = 'Please select a CSV file to upload.';
        $message_type = 'error';
    }
}

// Handle DOC/PDF upload with intelligent parsing
if (isset($_POST['doc_upload_questions']) && wp_verify_nonce($_POST['doc_nonce'], 'zonatech_doc_upload')) {
    $exam_type = sanitize_text_field($_POST['doc_exam_type']);
    $subject = sanitize_text_field($_POST['doc_subject']);
    $year = intval($_POST['doc_year']);
    
    if (!empty($_FILES['doc_file']['tmp_name']) && $exam_type && $subject && $year) {
        $file = $_FILES['doc_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Read file content
        $content = '';
        
        if ($file_ext === 'txt') {
            $content = file_get_contents($file['tmp_name']);
        } elseif ($file_ext === 'docx') {
            // Parse DOCX file (ZIP with XML)
            $zip = new ZipArchive();
            if ($zip->open($file['tmp_name']) === TRUE) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();
                
                // Extract text from XML
                $xml = str_replace('</w:p>', "\n", $xml);
                $xml = str_replace('</w:t>', ' ', $xml);
                $content = strip_tags($xml);
            }
        } elseif ($file_ext === 'doc') {
            // Basic DOC parsing (works for simple documents)
            $content = '';
            $fh = fopen($file['tmp_name'], 'r');
            if ($fh) {
                while (!feof($fh)) {
                    $content .= fread($fh, 8192);
                }
                fclose($fh);
                // Filter out binary/special characters
                $content = preg_replace('/[^\x20-\x7E\n\r]/', '', $content);
            }
        } elseif ($file_ext === 'pdf') {
            // Basic PDF text extraction
            $content = file_get_contents($file['tmp_name']);
            // Extract text between stream tags
            preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $matches);
            $text_parts = array();
            foreach ($matches[1] as $part) {
                // Try to decode if it's compressed
                $decoded = @gzuncompress($part);
                if ($decoded) {
                    $part = $decoded;
                }
                // Extract text from BT/ET blocks
                preg_match_all('/\((.*?)\)/', $part, $text_matches);
                $text_parts = array_merge($text_parts, $text_matches[1]);
            }
            $content = implode(' ', $text_parts);
        }
        
        // Clean up content
        $content = trim($content);
        $content = preg_replace('/\r\n/', "\n", $content);
        $content = preg_replace('/\r/', "\n", $content);
        
        // Intelligent question parsing
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        $success_count = 0;
        $error_count = 0;
        
        // Split by question numbers (1., 2., 3., etc. or Q1, Q2, etc. or Question 1, etc.)
        $patterns = array(
            '/(?:^|\n)\s*(\d+)\s*[.\)]\s*/m',  // 1. or 1)
            '/(?:^|\n)\s*Q\.?\s*(\d+)[.\):\s]/im',  // Q1 or Q.1 or Q1:
            '/(?:^|\n)\s*Question\s*(\d+)[.\):\s]/im',  // Question 1
        );
        
        $questions_raw = array();
        foreach ($patterns as $pattern) {
            $parts = preg_split($pattern, $content, -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) > 1) {
                $questions_raw = $parts;
                break;
            }
        }
        
        // If no pattern matched, try splitting by double newlines
        if (empty($questions_raw)) {
            $questions_raw = preg_split('/\n\s*\n/', $content);
        }
        
        foreach ($questions_raw as $q_block) {
            $q_block = trim($q_block);
            if (strlen($q_block) < 20) continue; // Skip too short blocks
            
            // Try to parse question and options
            $question_text = '';
            $options = array('A' => '', 'B' => '', 'C' => '', 'D' => '');
            $correct_answer = '';
            
            // Pattern to find options
            $option_pattern = '/(?:^|\n)\s*([A-D])\s*[.\):\s]\s*(.+?)(?=(?:\n\s*[A-D]\s*[.\):\s])|$)/is';
            preg_match_all($option_pattern, $q_block, $opt_matches, PREG_SET_ORDER);
            
            if (!empty($opt_matches)) {
                // Extract question text (everything before first option)
                $first_opt_pos = strpos($q_block, $opt_matches[0][0]);
                if ($first_opt_pos !== false && $first_opt_pos > 0) {
                    $question_text = trim(substr($q_block, 0, $first_opt_pos));
                }
                
                // Extract options
                foreach ($opt_matches as $match) {
                    $letter = strtoupper($match[1]);
                    $text = trim($match[2]);
                    // Remove answer indicator if present
                    if (preg_match('/\*+\s*$/', $text) || preg_match('/\(correct\)/i', $text) || preg_match('/✓|√/', $text)) {
                        $correct_answer = $letter;
                        $text = preg_replace('/\*+\s*$/', '', $text);
                        $text = preg_replace('/\(correct\)/i', '', $text);
                        $text = preg_replace('/[✓√]/', '', $text);
                    }
                    $options[$letter] = trim($text);
                }
            }
            
            // Check for answer at the end of block (Answer: A or Ans: B)
            if (empty($correct_answer)) {
                if (preg_match('/(?:answer|ans)[:\s]*([A-D])/i', $q_block, $ans_match)) {
                    $correct_answer = strtoupper($ans_match[1]);
                }
            }
            
            // Only insert if we have question text and at least 2 options
            if (!empty($question_text) && strlen($options['A']) > 0 && strlen($options['B']) > 0) {
                $result = $wpdb->insert($table_questions, array(
                    'exam_type' => $exam_type,
                    'subject' => $subject,
                    'year' => $year,
                    'question_text' => sanitize_textarea_field($question_text),
                    'option_a' => sanitize_text_field($options['A']),
                    'option_b' => sanitize_text_field($options['B']),
                    'option_c' => sanitize_text_field($options['C']),
                    'option_d' => sanitize_text_field($options['D']),
                    'correct_answer' => $correct_answer ?: 'A',
                    'explanation' => '',
                    'created_at' => current_time('mysql')
                ));
                
                if ($result) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        
        if ($success_count > 0) {
            $message = "Document parsed: $success_count questions extracted and added successfully!";
            $message_type = 'success';
        } else {
            $message = "Could not parse questions from the document. Please ensure your document follows a clear format with numbered questions and lettered options (A, B, C, D).";
            $message_type = 'error';
        }
    } else {
        $message = 'Please select a file and fill in all required fields (Exam Type, Subject, Year).';
        $message_type = 'error';
    }
}

// Handle scratch card generation
if (isset($_POST['generate_cards']) && wp_verify_nonce($_POST['cards_nonce'], 'zonatech_generate_cards')) {
    $card_type = sanitize_text_field($_POST['card_type']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity > 0 && $quantity <= 100) {
        $table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
        $generated = 0;
        
        for ($i = 0; $i < $quantity; $i++) {
            $pin = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
            $serial = 'ZT' . date('Ymd') . strtoupper(substr(md5(mt_rand()), 0, 6));
            
            $result = $wpdb->insert($table_cards, array(
                'card_type' => $card_type,
                'serial_number' => $serial,
                'pin' => $pin,
                'status' => 'available',
                'created_at' => current_time('mysql')
            ));
            
            if ($result) $generated++;
        }
        
        $message = "$generated $card_type scratch cards generated successfully!";
        $message_type = 'success';
    } else {
        $message = 'Please enter a valid quantity (1-100).';
        $message_type = 'error';
    }
}

// Get statistics
$table_purchases = $wpdb->prefix . 'zonatech_purchases';
$table_questions = $wpdb->prefix . 'zonatech_questions';
$table_quiz = $wpdb->prefix . 'zonatech_quiz_results';
$table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
$table_nin = $wpdb->prefix . 'zonatech_nin_requests';
$table_feedback = $wpdb->prefix . 'zonatech_feedback';
$table_activity = $wpdb->prefix . 'zonatech_activity_log';

// Revenue statistics
$today_revenue = $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount), 0) FROM $table_purchases WHERE status = 'completed' AND DATE(created_at) = %s",
    date('Y-m-d')
)) ?? 0;

$this_week_revenue = $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount), 0) FROM $table_purchases WHERE status = 'completed' AND created_at >= %s",
    date('Y-m-d', strtotime('-7 days'))
)) ?? 0;

$this_month_revenue = $wpdb->get_var($wpdb->prepare(
    "SELECT COALESCE(SUM(amount), 0) FROM $table_purchases WHERE status = 'completed' AND MONTH(created_at) = %d AND YEAR(created_at) = %d",
    date('n'), date('Y')
)) ?? 0;

$total_revenue = $wpdb->get_var(
    "SELECT COALESCE(SUM(amount), 0) FROM $table_purchases WHERE status = 'completed'"
) ?? 0;

// User statistics
$total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}") ?? 0;
$new_users_today = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->users} WHERE DATE(user_registered) = %s",
    date('Y-m-d')
)) ?? 0;
$new_users_week = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered >= %s",
    date('Y-m-d', strtotime('-7 days'))
)) ?? 0;

// Questions count
$total_questions = $wpdb->get_var("SELECT COUNT(*) FROM $table_questions") ?? 0;
$question_stats = $wpdb->get_results("SELECT exam_type, COUNT(*) as count FROM $table_questions GROUP BY exam_type");

// Quizzes taken
$total_quizzes = $wpdb->get_var("SELECT COUNT(*) FROM $table_quiz") ?? 0;
$quizzes_today = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_quiz WHERE DATE(completed_at) = %s",
    date('Y-m-d')
)) ?? 0;

// Pending items
$pending_purchases = $wpdb->get_var("SELECT COUNT(*) FROM $table_purchases WHERE status = 'pending'") ?? 0;

// Recent purchases
$recent_purchases = $wpdb->get_results($wpdb->prepare(
    "SELECT p.*, u.display_name, u.user_email 
     FROM $table_purchases p 
     LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID 
     ORDER BY p.created_at DESC 
     LIMIT %d",
    10
));

// Top subjects
$top_subjects = $wpdb->get_results(
    "SELECT item_name, COUNT(*) as count, SUM(amount) as revenue 
     FROM $table_purchases 
     WHERE status = 'completed' AND purchase_type = 'subject'
     GROUP BY item_name 
     ORDER BY count DESC 
     LIMIT 5"
);

// Available scratch cards
$available_cards = $wpdb->get_results(
    "SELECT card_type, COUNT(*) as count FROM $table_cards WHERE status = 'available' GROUP BY card_type"
);

// Recent users
$recent_users = $wpdb->get_results($wpdb->prepare(
    "SELECT ID, display_name, user_email, user_registered FROM {$wpdb->users} ORDER BY user_registered DESC LIMIT %d",
    10
));

// Recent feedback (check if table exists first)
$recent_feedback = array();
if ($wpdb->get_var("SHOW TABLES LIKE '$table_feedback'") == $table_feedback) {
    $recent_feedback = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_feedback ORDER BY created_at DESC LIMIT %d",
        10
    ));
}

// Activity log
$recent_activities = $wpdb->get_results($wpdb->prepare(
    "SELECT a.*, u.display_name FROM $table_activity a 
     LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID 
     ORDER BY a.created_at DESC LIMIT %d",
    10
));

$current_user = wp_get_current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ZonaTech NG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo ZONATECH_PLUGIN_URL; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo ZONATECH_PLUGIN_URL; ?>assets/css/dashboard.css">
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --primary-light: #a78bfa;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --info: #3b82f6;
            --dark: #1f2937;
            --light: #f9fafb;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: #ffffff;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(139, 92, 246, 0.2);
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        
        .admin-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .admin-logo a {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .admin-logo img {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
        }
        
        .admin-logo img:hover {
            border-color: rgba(139, 92, 246, 0.8);
            transform: scale(1.05);
        }
        
        .admin-logo-text {
            flex: 1;
            min-width: 0;
        }
        
        .admin-logo h2 {
            font-size: 16px;
            font-weight: 700;
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            line-height: 1.3;
        }
        
        .admin-logo span {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            display: block;
            margin-top: 2px;
        }
        
        .admin-nav {
            list-style: none;
        }
        
        .admin-nav li {
            margin-bottom: 5px;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: rgba(139, 92, 246, 0.2);
            color: #ffffff;
        }
        
        .admin-nav a.active {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.3), rgba(167, 139, 250, 0.2));
            border-left: 3px solid #8b5cf6;
        }
        
        .admin-nav i {
            width: 20px;
            text-align: center;
        }
        
        .nav-divider {
            height: 1px;
            background: rgba(139, 92, 246, 0.2);
            margin: 20px 0;
        }
        
        .admin-user {
            margin-top: auto;
            padding: 15px;
            background: rgba(139, 92, 246, 0.1);
            border-radius: 12px;
            margin-top: 30px;
        }
        
        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .admin-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .admin-user-details h4 {
            font-size: 14px;
            font-weight: 600;
        }
        
        .admin-user-details span {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            font-size: 28px;
            font-weight: 700;
        }
        
        .admin-header-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn-admin {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-admin-primary {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #ffffff;
        }
        
        .btn-admin-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(139, 92, 246, 0.4);
        }
        
        .btn-admin-outline {
            background: transparent;
            border: 1px solid rgba(139, 92, 246, 0.5);
            color: #ffffff;
        }
        
        .btn-admin-outline:hover {
            background: rgba(139, 92, 246, 0.1);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(139, 92, 246, 0.5);
            box-shadow: 0 10px 40px rgba(139, 92, 246, 0.2);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-card-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .stat-card-icon.revenue { background: rgba(139, 92, 246, 0.2); color: #8b5cf6; }
        .stat-card-icon.users { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .stat-card-icon.questions { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
        .stat-card-icon.pending { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
        
        .stat-card-change {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 20px;
        }
        
        .stat-card-change.positive {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .stat-card-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-card-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Section */
        .admin-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-header h2 i {
            color: #8b5cf6;
        }
        
        /* Tables */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            text-align: left;
            padding: 12px 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .admin-table td {
            padding: 15px;
            font-size: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .admin-table tr:hover td {
            background: rgba(139, 92, 246, 0.05);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-badge.completed {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .status-badge.pending {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }
        
        .status-badge.failed {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        /* Grid Layouts */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        
        .three-columns {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        /* Cards Info */
        .card-info {
            background: rgba(139, 92, 246, 0.1);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
        }
        
        .card-info h4 {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .card-info .value {
            font-size: 24px;
            font-weight: 700;
            color: #8b5cf6;
        }
        
        /* Feedback Card */
        .feedback-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .feedback-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .feedback-rating {
            color: #f59e0b;
        }
        
        .feedback-message {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.5;
        }
        
        .feedback-meta {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 10px;
        }
        
        /* User Avatar */
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }
        
        .user-details {
            font-size: 14px;
        }
        
        .user-details small {
            display: block;
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
        }
        
        /* Hamburger Menu for Mobile */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 200;
            width: 45px;
            height: 45px;
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 12px;
            cursor: pointer;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .mobile-menu-toggle span {
            width: 20px;
            height: 2px;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .two-columns {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
            }
            
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .admin-sidebar.open {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
                padding: 80px 15px 30px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .three-columns {
                grid-template-columns: 1fr;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .admin-header h1 {
                font-size: 22px;
            }
        }
        
        /* Modal Styles */
        .admin-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            overflow-y: auto;
            padding: 40px 20px;
        }
        
        .admin-modal.active {
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }
        
        .admin-modal-content {
            background: linear-gradient(135deg, rgba(30, 30, 50, 0.98), rgba(20, 20, 35, 0.98));
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 20px;
            padding: 30px;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        
        .admin-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .admin-modal-header h2 {
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
        }
        
        .admin-modal-header h2 i {
            color: #8b5cf6;
        }
        
        .admin-modal-close {
            width: 40px;
            height: 40px;
            border: none;
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        
        .admin-modal-close:hover {
            background: rgba(239, 68, 68, 0.4);
        }
        
        /* Form Styles */
        .admin-form-group {
            margin-bottom: 20px;
        }
        
        .admin-form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 8px;
        }
        
        .admin-form-group input,
        .admin-form-group select,
        .admin-form-group textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 10px;
            color: #ffffff;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .admin-form-group input:focus,
        .admin-form-group select:focus,
        .admin-form-group textarea:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }
        
        .admin-form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .admin-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .admin-form-row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        @media (max-width: 600px) {
            .admin-form-row, .admin-form-row-3 {
                grid-template-columns: 1fr;
            }
        }
        
        .admin-form-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border: none;
            border-radius: 10px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .admin-form-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
        }
        
        /* Tabs */
        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .admin-tab {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .admin-tab:hover, .admin-tab.active {
            background: rgba(139, 92, 246, 0.2);
            border-color: rgba(139, 92, 246, 0.5);
            color: #ffffff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* File Upload */
        .file-upload-area {
            border: 2px dashed rgba(139, 92, 246, 0.4);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .file-upload-area:hover {
            border-color: #8b5cf6;
            background: rgba(139, 92, 246, 0.1);
        }
        
        .file-upload-area i {
            font-size: 40px;
            color: #8b5cf6;
            margin-bottom: 15px;
        }
        
        .file-upload-area p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 10px;
        }
        
        .file-upload-area small {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
        }
        
        .file-upload-area input[type="file"] {
            display: none;
        }
        
        /* Message Alert */
        .admin-alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .admin-alert.success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #10b981;
        }
        
        .admin-alert.error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #ef4444;
        }
        
        .admin-alert.warning {
            background: rgba(245, 158, 11, 0.2);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #f59e0b;
        }
        
        /* Download Link */
        .download-template {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.4);
            border-radius: 8px;
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .download-template:hover {
            background: rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-logo">
                <a href="<?php echo site_url(); ?>">
                    <img src="<?php echo ZONATECH_PLUGIN_URL; ?>assets/images/logo.png" alt="ZonaTech NG">
                </a>
                <div class="admin-logo-text">
                    <h2>ZonaTech NG</h2>
                    <span>Admin Dashboard</span>
                </div>
            </div>
            
            <ul class="admin-nav">
                <li><a href="#dashboard" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="#users"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="#purchases"><i class="fas fa-shopping-cart"></i> Purchases</a></li>
                <li><a href="#questions"><i class="fas fa-book"></i> Questions</a></li>
                <li><a href="#feedback"><i class="fas fa-comments"></i> Feedback</a></li>
                
                <div class="nav-divider"></div>
                
                <li><a href="#" onclick="openModal('addQuestionModal'); return false;"><i class="fas fa-plus-circle"></i> Add Questions</a></li>
                <li><a href="#" onclick="openModal('manageCardsModal'); return false;"><i class="fas fa-ticket-alt"></i> Manage Cards</a></li>
                <li><a href="#" onclick="openModal('settingsModal'); return false;"><i class="fas fa-cog"></i> Settings</a></li>
                
                <div class="nav-divider"></div>
                
                <li><a href="<?php echo site_url('/zonatech-dashboard/'); ?>"><i class="fas fa-user"></i> User Dashboard</a></li>
                <li><a href="<?php echo site_url(); ?>"><i class="fas fa-home"></i> View Site</a></li>
                <li><a href="<?php echo wp_logout_url(site_url()); ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            
            <div class="admin-user">
                <div class="admin-user-info">
                    <div class="admin-user-avatar">
                        <?php echo strtoupper(substr($current_user->display_name, 0, 1)); ?>
                    </div>
                    <div class="admin-user-details">
                        <h4><?php echo esc_html($current_user->display_name); ?></h4>
                        <span>Administrator</span>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <?php if (!empty($message)): ?>
            <div class="admin-alert <?php echo esc_attr($message_type); ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
                <?php echo esc_html($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="admin-header">
                <h1><i class="fas fa-chart-line"></i> Dashboard Overview</h1>
                <div class="admin-header-actions">
                    <button onclick="openModal('addQuestionModal')" class="btn-admin btn-admin-primary">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                    <button onclick="openModal('manageCardsModal')" class="btn-admin btn-admin-outline">
                        <i class="fas fa-ticket-alt"></i> Add Cards
                    </button>
                </div>
            </div>
            
            <!-- Revenue Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon revenue"><i class="fas fa-calendar-day"></i></div>
                        <span class="stat-card-change positive">Today</span>
                    </div>
                    <div class="stat-card-value">₦<?php echo number_format($today_revenue); ?></div>
                    <div class="stat-card-label">Today's Revenue</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon revenue"><i class="fas fa-calendar-week"></i></div>
                        <span class="stat-card-change positive">7 Days</span>
                    </div>
                    <div class="stat-card-value">₦<?php echo number_format($this_week_revenue); ?></div>
                    <div class="stat-card-label">This Week</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon revenue"><i class="fas fa-calendar-alt"></i></div>
                        <span class="stat-card-change positive">Month</span>
                    </div>
                    <div class="stat-card-value">₦<?php echo number_format($this_month_revenue); ?></div>
                    <div class="stat-card-label">This Month</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon revenue"><i class="fas fa-vault"></i></div>
                    </div>
                    <div class="stat-card-value">₦<?php echo number_format($total_revenue); ?></div>
                    <div class="stat-card-label">Total Revenue</div>
                </div>
            </div>
            
            <!-- Platform Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon users"><i class="fas fa-users"></i></div>
                        <span class="stat-card-change positive">+<?php echo $new_users_today; ?> today</span>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($total_users); ?></div>
                    <div class="stat-card-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon questions"><i class="fas fa-book"></i></div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($total_questions); ?></div>
                    <div class="stat-card-label">Total Questions</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon questions"><i class="fas fa-clipboard-check"></i></div>
                        <span class="stat-card-change positive">+<?php echo $quizzes_today; ?> today</span>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($total_quizzes); ?></div>
                    <div class="stat-card-label">Quizzes Taken</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-icon pending"><i class="fas fa-clock"></i></div>
                    </div>
                    <div class="stat-card-value"><?php echo number_format($pending_purchases); ?></div>
                    <div class="stat-card-label">Pending Payments</div>
                </div>
            </div>
            
            <!-- Questions by Exam Type -->
            <div class="admin-section">
                <div class="section-header">
                    <h2><i class="fas fa-book-open"></i> Questions by Exam Type</h2>
                </div>
                <div class="three-columns">
                    <?php 
                    $exam_colors = array('jamb' => '#8b5cf6', 'waec' => '#10b981', 'neco' => '#f59e0b');
                    foreach ($question_stats as $stat): 
                        $color = $exam_colors[strtolower($stat->exam_type)] ?? '#6b7280';
                    ?>
                    <div class="card-info" style="border-left: 4px solid <?php echo $color; ?>;">
                        <h4><?php echo strtoupper(esc_html($stat->exam_type)); ?></h4>
                        <div class="value"><?php echo number_format($stat->count); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Two Column Layout -->
            <div class="two-columns">
                <!-- Recent Purchases -->
                <div class="admin-section" id="purchases">
                    <div class="section-header">
                        <h2><i class="fas fa-shopping-cart"></i> Recent Purchases</h2>
                        <span class="btn-admin btn-admin-outline" style="opacity: 0.7;">Showing Latest 10</span>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Item</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_purchases)): ?>
                                <?php foreach ($recent_purchases as $purchase): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar"><?php echo strtoupper(substr($purchase->display_name ?? 'U', 0, 1)); ?></div>
                                            <div class="user-details">
                                                <?php echo esc_html($purchase->display_name ?? 'Unknown'); ?>
                                                <small><?php echo date('M j', strtotime($purchase->created_at)); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($purchase->item_name); ?></td>
                                    <td>₦<?php echo number_format($purchase->amount); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo esc_attr($purchase->status); ?>">
                                            <?php echo ucfirst($purchase->status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align: center; color: rgba(255,255,255,0.5);">No purchases yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Recent Users -->
                <div class="admin-section" id="users">
                    <div class="section-header">
                        <h2><i class="fas fa-users"></i> Recent Users</h2>
                        <span class="btn-admin btn-admin-outline" style="opacity: 0.7;">Showing Latest 10</span>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_users)): ?>
                                <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar"><?php echo strtoupper(substr($user->display_name, 0, 1)); ?></div>
                                            <span><?php echo esc_html($user->display_name); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($user->user_email); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user->user_registered)); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align: center; color: rgba(255,255,255,0.5);">No users yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- More Stats -->
            <div class="two-columns">
                <!-- Top Selling Subjects -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2><i class="fas fa-star"></i> Top Selling Subjects</h2>
                    </div>
                    <?php if (!empty($top_subjects)): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Sales</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_subjects as $subject): ?>
                            <tr>
                                <td><?php echo esc_html($subject->item_name); ?></td>
                                <td><?php echo number_format($subject->count); ?></td>
                                <td>₦<?php echo number_format($subject->revenue); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="text-align: center; color: rgba(255,255,255,0.5);">No subject sales yet</p>
                    <?php endif; ?>
                </div>
                
                <!-- Scratch Cards -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2><i class="fas fa-ticket-alt"></i> Available Scratch Cards</h2>
                        <button onclick="openModal('manageCardsModal')" class="btn-admin btn-admin-outline">Add More</button>
                    </div>
                    <?php if (!empty($available_cards)): ?>
                    <div class="three-columns">
                        <?php foreach ($available_cards as $card): ?>
                        <div class="card-info">
                            <h4><?php echo strtoupper(esc_html($card->card_type)); ?></h4>
                            <div class="value"><?php echo number_format($card->count); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p style="text-align: center; color: rgba(255,255,255,0.5);">No scratch cards available. <a href="#" onclick="openModal('manageCardsModal'); return false;" style="color: #8b5cf6;">Add some</a></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Feedback -->
            <div class="admin-section" id="feedback">
                <div class="section-header">
                    <h2><i class="fas fa-comments"></i> Recent Feedback</h2>
                    <span class="btn-admin btn-admin-outline" style="opacity: 0.7;">Showing Latest 5</span>
                </div>
                <?php if (!empty($recent_feedback)): ?>
                    <?php foreach ($recent_feedback as $fb): ?>
                    <div class="feedback-card">
                        <div class="feedback-card-header">
                            <div class="user-info">
                                <div class="user-avatar"><?php echo strtoupper(substr($fb->name, 0, 1)); ?></div>
                                <div class="user-details">
                                    <?php echo esc_html($fb->name); ?>
                                    <small><?php echo esc_html($fb->subject); ?></small>
                                </div>
                            </div>
                            <div class="feedback-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa<?php echo $i <= $fb->rating ? 's' : 'r'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="feedback-message"><?php echo esc_html(substr($fb->message, 0, 200)); ?><?php echo strlen($fb->message) > 200 ? '...' : ''; ?></p>
                        <div class="feedback-meta">
                            <i class="fas fa-envelope"></i> <?php echo esc_html($fb->email); ?> &bull; 
                            <i class="fas fa-clock"></i> <?php echo date('M j, Y g:i A', strtotime($fb->created_at)); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: rgba(255,255,255,0.5);">No feedback yet</p>
                <?php endif; ?>
            </div>
            
            <!-- Recent Activity -->
            <div class="admin-section">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Recent Activity</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Activity</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar"><?php echo strtoupper(substr($activity->display_name ?? 'U', 0, 1)); ?></div>
                                        <span><?php echo esc_html($activity->display_name ?? 'Unknown'); ?></span>
                                    </div>
                                </td>
                                <td><?php echo esc_html($activity->action); ?></td>
                                <td><?php echo date('M j, g:i A', strtotime($activity->created_at)); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align: center; color: rgba(255,255,255,0.5);">No recent activity</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Add Question Modal -->
    <div class="admin-modal" id="addQuestionModal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h2><i class="fas fa-plus-circle"></i> Add Questions</h2>
                <button class="admin-modal-close" onclick="closeModal('addQuestionModal')">&times;</button>
            </div>
            
            <div class="admin-tabs">
                <button class="admin-tab active" onclick="switchTab('singleQuestion', this)">Single Question</button>
                <button class="admin-tab" onclick="switchTab('bulkUpload', this)">Bulk Upload (CSV)</button>
                <button class="admin-tab" onclick="switchTab('docUpload', this)">Document Upload</button>
            </div>
            
            <!-- Single Question Form -->
            <div class="tab-content active" id="singleQuestion">
                <form method="POST" action="">
                    <?php wp_nonce_field('zonatech_add_question', 'question_nonce'); ?>
                    
                    <div class="admin-form-row-3">
                        <div class="admin-form-group">
                            <label>Exam Type *</label>
                            <select name="exam_type" required>
                                <option value="">Select Exam</option>
                                <option value="jamb">JAMB</option>
                                <option value="waec">WAEC</option>
                                <option value="neco">NECO</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label>Subject *</label>
                            <select name="subject" id="modalSubject" required>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label>Year *</label>
                            <select name="year" required>
                                <option value="">Select Year</option>
                                <?php for ($y = date('Y'); $y >= 2010; $y--): ?>
                                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="admin-form-group">
                        <label>Question Text *</label>
                        <textarea name="question_text" placeholder="Enter the question..." required></textarea>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label>Option A *</label>
                            <input type="text" name="option_a" placeholder="First option" required>
                        </div>
                        <div class="admin-form-group">
                            <label>Option B *</label>
                            <input type="text" name="option_b" placeholder="Second option" required>
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label>Option C *</label>
                            <input type="text" name="option_c" placeholder="Third option" required>
                        </div>
                        <div class="admin-form-group">
                            <label>Option D *</label>
                            <input type="text" name="option_d" placeholder="Fourth option" required>
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label>Correct Answer *</label>
                            <select name="correct_answer" required>
                                <option value="">Select Answer</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label>Explanation (Optional)</label>
                            <input type="text" name="explanation" placeholder="Why this answer is correct">
                        </div>
                    </div>
                    
                    <button type="submit" name="add_single_question" class="admin-form-submit">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                </form>
            </div>
            
            <!-- Bulk Upload Form -->
            <div class="tab-content" id="bulkUpload">
                <a href="#" onclick="downloadCSVTemplate(); return false;" class="download-template">
                    <i class="fas fa-download"></i> Download CSV Template
                </a>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php wp_nonce_field('zonatech_bulk_upload', 'bulk_nonce'); ?>
                    
                    <div class="file-upload-area" onclick="document.getElementById('csvFile').click();">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload CSV file</p>
                        <small>Format: exam_type, subject, year, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation</small>
                        <input type="file" name="csv_file" id="csvFile" accept=".csv" onchange="handleFileSelect(this)">
                    </div>
                    
                    <p id="selectedFile" style="text-align: center; color: #8b5cf6; margin-bottom: 15px;"></p>
                    
                    <button type="submit" name="bulk_upload_questions" class="admin-form-submit">
                        <i class="fas fa-upload"></i> Upload Questions
                    </button>
                </form>
                
                <div style="margin-top: 20px; padding: 15px; background: rgba(59, 130, 246, 0.1); border-radius: 10px;">
                    <h4 style="margin-bottom: 10px; color: #3b82f6;"><i class="fas fa-info-circle"></i> CSV Format Guide</h4>
                    <p style="font-size: 13px; color: rgba(255,255,255,0.7); line-height: 1.6;">
                        Each row should contain: exam_type (jamb/waec/neco), subject, year, question_text, option_a, option_b, option_c, option_d, correct_answer (A/B/C/D), explanation (optional)
                    </p>
                </div>
            </div>
            
            <!-- Document Upload Tab -->
            <div class="tab-content" id="docUpload">
                <div style="margin-bottom: 20px; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px;">
                    <h4 style="margin-bottom: 10px; color: #10b981;"><i class="fas fa-magic"></i> Smart Document Parser</h4>
                    <p style="font-size: 13px; color: rgba(255,255,255,0.7); line-height: 1.6;">
                        Upload a DOC, DOCX, PDF, or TXT file with questions. The system will intelligently extract questions and options.
                    </p>
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php wp_nonce_field('zonatech_doc_upload', 'doc_nonce'); ?>
                    
                    <div class="admin-form-row-3">
                        <div class="admin-form-group">
                            <label><i class="fas fa-graduation-cap"></i> Exam Type *</label>
                            <select name="doc_exam_type" id="docExamType" required>
                                <option value="">Select Exam</option>
                                <option value="jamb">JAMB</option>
                                <option value="waec">WAEC</option>
                                <option value="neco">NECO</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label><i class="fas fa-book"></i> Subject *</label>
                            <select name="doc_subject" id="docSubject" required>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label><i class="fas fa-calendar"></i> Year *</label>
                            <select name="doc_year" required>
                                <option value="">Select Year</option>
                                <?php for ($y = date('Y'); $y >= 2010; $y--): ?>
                                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="file-upload-area" onclick="document.getElementById('docFile').click();" style="margin-top: 15px;">
                        <i class="fas fa-file-alt"></i>
                        <p>Click to upload DOC, DOCX, PDF, or TXT file</p>
                        <small>Questions will be automatically extracted from your document</small>
                        <input type="file" name="doc_file" id="docFile" accept=".doc,.docx,.pdf,.txt" onchange="handleDocSelect(this)">
                    </div>
                    
                    <p id="selectedDoc" style="text-align: center; color: #10b981; margin-bottom: 15px;"></p>
                    
                    <button type="submit" name="doc_upload_questions" class="admin-form-submit" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-magic"></i> Parse & Upload Questions
                    </button>
                </form>
                
                <div style="margin-top: 25px; padding: 20px; background: rgba(139, 92, 246, 0.1); border-radius: 12px;">
                    <h4 style="margin-bottom: 15px; color: #a78bfa;"><i class="fas fa-lightbulb"></i> Document Format Tips</h4>
                    <div style="font-size: 13px; color: rgba(255,255,255,0.8); line-height: 1.8;">
                        <p style="margin-bottom: 10px;">For best results, format your document like this:</p>
                        <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; font-family: monospace; font-size: 12px;">
                            <p style="color: #f59e0b;">1. What is the capital of Nigeria?</p>
                            <p>A. Abuja</p>
                            <p>B. Lagos</p>
                            <p>C. Kano</p>
                            <p>D. Ibadan</p>
                            <p style="color: #10b981;">Answer: A</p>
                            <br>
                            <p style="color: #f59e0b;">2. Which river is the longest in Africa?</p>
                            <p>A. Niger River</p>
                            <p>B. Nile River *</p>
                            <p>C. Congo River</p>
                            <p>D. Zambezi River</p>
                        </div>
                        <p style="margin-top: 15px; color: rgba(255,255,255,0.6);">
                            <strong>Tips:</strong> Mark correct answers with *, (correct), or "Answer: X" after options.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Manage Cards Modal -->
    <div class="admin-modal" id="manageCardsModal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h2><i class="fas fa-ticket-alt"></i> Generate Scratch Cards</h2>
                <button class="admin-modal-close" onclick="closeModal('manageCardsModal')">&times;</button>
            </div>
            
            <form method="POST" action="">
                <?php wp_nonce_field('zonatech_generate_cards', 'cards_nonce'); ?>
                
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label>Card Type *</label>
                        <select name="card_type" required>
                            <option value="">Select Type</option>
                            <option value="waec">WAEC Result Checker</option>
                            <option value="neco">NECO Result Checker</option>
                            <option value="jamb">JAMB Profile Code</option>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label>Quantity (1-100) *</label>
                        <input type="number" name="quantity" min="1" max="100" placeholder="Number of cards" required>
                    </div>
                </div>
                
                <button type="submit" name="generate_cards" class="admin-form-submit">
                    <i class="fas fa-magic"></i> Generate Cards
                </button>
            </form>
            
            <div style="margin-top: 25px;">
                <h3 style="font-size: 16px; margin-bottom: 15px; color: rgba(255,255,255,0.8);"><i class="fas fa-list"></i> Current Stock</h3>
                <?php if (!empty($available_cards)): ?>
                <div class="three-columns">
                    <?php foreach ($available_cards as $card): ?>
                    <div class="card-info">
                        <h4><?php echo strtoupper(esc_html($card->card_type)); ?></h4>
                        <div class="value"><?php echo number_format($card->count); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="text-align: center; color: rgba(255,255,255,0.5);">No scratch cards in stock</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Settings Modal -->
    <div class="admin-modal" id="settingsModal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h2><i class="fas fa-cog"></i> Settings</h2>
                <button class="admin-modal-close" onclick="closeModal('settingsModal')">&times;</button>
            </div>
            
            <div style="padding: 20px; background: rgba(139, 92, 246, 0.1); border-radius: 12px; text-align: center;">
                <i class="fas fa-info-circle" style="font-size: 40px; color: #8b5cf6; margin-bottom: 15px;"></i>
                <h3 style="margin-bottom: 10px;">Payment Settings</h3>
                <p style="color: rgba(255,255,255,0.7); margin-bottom: 15px;">
                    Configure Paystack API keys in WordPress Admin for payment processing.
                </p>
                <a href="<?php echo admin_url('admin.php?page=zonatech-settings'); ?>" class="btn-admin btn-admin-primary" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Open Settings
                </a>
            </div>
            
            <div style="margin-top: 25px;">
                <h3 style="font-size: 16px; margin-bottom: 15px;"><i class="fas fa-sliders-h"></i> Quick Info</h3>
                <div class="three-columns">
                    <div class="card-info">
                        <h4>Subject Price</h4>
                        <div class="value">₦5,000</div>
                    </div>
                    <div class="card-info">
                        <h4>Scratch Card</h4>
                        <div class="value">₦5,000</div>
                    </div>
                    <div class="card-info">
                        <h4>NIN Slip</h4>
                        <div class="value">₦1-2K</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('open');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('adminSidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
        
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Close modal on backdrop click
        document.querySelectorAll('.admin-modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
        
        // Tab switching
        function switchTab(tabId, button) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.admin-tab').forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            button.classList.add('active');
        }
        
        // File upload handling
        function handleFileSelect(input) {
            const fileName = input.files[0]?.name;
            if (fileName) {
                document.getElementById('selectedFile').textContent = 'Selected: ' + fileName;
            }
        }
        
        // Document file upload handling
        function handleDocSelect(input) {
            const fileName = input.files[0]?.name;
            if (fileName) {
                document.getElementById('selectedDoc').textContent = 'Selected: ' + fileName;
            }
        }
        
        // Download CSV template
        function downloadCSVTemplate() {
            const headers = 'exam_type,subject,year,question_text,option_a,option_b,option_c,option_d,correct_answer,explanation\n';
            const example = 'jamb,Mathematics,2023,"What is 2 + 2?",3,4,5,6,B,"2 + 2 equals 4"';
            const blob = new Blob([headers + example], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'zonatech_questions_template.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
        // Subject dropdown population based on exam type
        const subjects = {
            jamb: ['Use of English', 'Mathematics', 'Physics', 'Chemistry', 'Biology', 'Agricultural Science', 'Economics', 'Commerce', 'Accounting', 'Government', 'Geography', 'Literature in English', 'Christian Religious Studies', 'Islamic Religious Studies', 'History', 'Civic Education', 'Home Economics', 'Food & Nutrition', 'Fine Arts', 'Music', 'French', 'Arabic', 'Hausa', 'Igbo', 'Yoruba', 'Physical Education'],
            waec: ['English Language', 'Mathematics', 'Civic Education', 'Physics', 'Chemistry', 'Biology', 'Agricultural Science', 'Further Mathematics', 'Health Education', 'Economics', 'Commerce', 'Financial Accounting', 'Literature in English', 'Government', 'History', 'Christian Religious Studies', 'Islamic Religious Studies', 'Geography', 'Fine Arts', 'Music', 'French', 'Arabic', 'Hausa', 'Igbo', 'Yoruba', 'Data Processing', 'Computer Studies', 'Animal Husbandry', 'Technical Drawing'],
            neco: ['English Language', 'Mathematics', 'Civic Education', 'Physics', 'Chemistry', 'Biology', 'Agricultural Science', 'Further Mathematics', 'Health Science', 'Economics', 'Commerce', 'Financial Accounting', 'Literature in English', 'Government', 'History', 'Christian Religious Studies', 'Islamic Religious Studies', 'Geography', 'Fine Arts', 'Music', 'French', 'Arabic', 'Hausa', 'Igbo', 'Yoruba', 'Computer Studies', 'Data Processing', 'Marketing', 'Home Economics', 'Animal Husbandry', 'Technical Drawing']
        };
        
        // Update subjects for single question form
        document.querySelector('select[name="exam_type"]')?.addEventListener('change', function() {
            const subjectSelect = document.getElementById('modalSubject');
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            
            const examSubjects = subjects[this.value] || [];
            examSubjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject;
                option.textContent = subject;
                subjectSelect.appendChild(option);
            });
        });
        
        // Update subjects for document upload form
        document.getElementById('docExamType')?.addEventListener('change', function() {
            const subjectSelect = document.getElementById('docSubject');
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            
            const examSubjects = subjects[this.value] || [];
            examSubjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject;
                option.textContent = subject;
                subjectSelect.appendChild(option);
            });
        });
    </script>
</body>
</html>
