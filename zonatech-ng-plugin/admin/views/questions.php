<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_questions = $wpdb->prefix . 'zonatech_questions';

// Handle form submission
if (isset($_POST['zonatech_add_question']) && wp_verify_nonce($_POST['_wpnonce'], 'zonatech_add_question')) {
    $wpdb->insert($table_questions, array(
        'exam_type' => sanitize_text_field($_POST['exam_type']),
        'subject' => sanitize_text_field($_POST['subject']),
        'year' => intval($_POST['year']),
        'question_text' => sanitize_textarea_field($_POST['question_text']),
        'option_a' => sanitize_text_field($_POST['option_a']),
        'option_b' => sanitize_text_field($_POST['option_b']),
        'option_c' => sanitize_text_field($_POST['option_c']),
        'option_d' => sanitize_text_field($_POST['option_d']),
        'correct_answer' => sanitize_text_field($_POST['correct_answer']),
        'explanation' => sanitize_textarea_field($_POST['explanation'])
    ));
    echo '<div class="notice notice-success"><p>Question added successfully!</p></div>';
}

// Get questions count by exam type
$stats = $wpdb->get_results(
    "SELECT exam_type, COUNT(*) as count FROM $table_questions GROUP BY exam_type"
);
?>
<div class="wrap zonatech-admin">
    <h1><span class="dashicons dashicons-book"></span> Manage Questions</h1>
    
    <div class="zonatech-stats-grid" style="margin-bottom: 20px;">
        <?php foreach ($stats as $stat): ?>
            <div class="stat-card small">
                <h4><?php echo strtoupper(esc_html($stat->exam_type)); ?></h4>
                <p><?php echo number_format($stat->count); ?> questions</p>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="zonatech-admin-section">
        <h2>Add New Question</h2>
        <form method="post" class="zonatech-form">
            <?php wp_nonce_field('zonatech_add_question'); ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Exam Type</label>
                    <select name="exam_type" required>
                        <option value="jamb">JAMB</option>
                        <option value="waec">WAEC</option>
                        <option value="neco">NECO</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject" required>
                        <option value="English Language">English Language</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Physics">Physics</option>
                        <option value="Chemistry">Chemistry</option>
                        <option value="Biology">Biology</option>
                        <option value="Economics">Economics</option>
                        <option value="Government">Government</option>
                        <option value="Literature in English">Literature in English</option>
                        <option value="Commerce">Commerce</option>
                        <option value="Accounting">Accounting</option>
                        <option value="Geography">Geography</option>
                        <option value="Agricultural Science">Agricultural Science</option>
                        <option value="Further Mathematics">Further Mathematics</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Civic Education">Civic Education</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Year</label>
                    <select name="year" required>
                        <?php for ($y = 2024; $y >= 2010; $y--): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Question</label>
                <textarea name="question_text" rows="3" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Option A</label>
                    <input type="text" name="option_a" required>
                </div>
                <div class="form-group">
                    <label>Option B</label>
                    <input type="text" name="option_b" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Option C</label>
                    <input type="text" name="option_c" required>
                </div>
                <div class="form-group">
                    <label>Option D</label>
                    <input type="text" name="option_d" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Correct Answer</label>
                    <select name="correct_answer" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Explanation (Optional)</label>
                <textarea name="explanation" rows="2"></textarea>
            </div>
            
            <button type="submit" name="zonatech_add_question" class="button button-primary">
                Add Question
            </button>
        </form>
    </div>
</div>
