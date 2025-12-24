<?php
/**
 * Database Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class ZonaTech_Database {
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Past Questions Table
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        $sql_questions = "CREATE TABLE $table_questions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            exam_type varchar(20) NOT NULL,
            subject varchar(100) NOT NULL,
            year int(4) NOT NULL,
            question_text longtext NOT NULL,
            option_a text NOT NULL,
            option_b text NOT NULL,
            option_c text NOT NULL,
            option_d text NOT NULL,
            correct_answer char(1) NOT NULL,
            explanation longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY exam_type (exam_type),
            KEY subject (subject),
            KEY year (year)
        ) $charset_collate;";
        dbDelta($sql_questions);
        
        // User Purchases Table
        $table_purchases = $wpdb->prefix . 'zonatech_purchases';
        $sql_purchases = "CREATE TABLE $table_purchases (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            purchase_type varchar(50) NOT NULL,
            item_name varchar(255) NOT NULL,
            amount decimal(10,2) NOT NULL,
            reference varchar(100) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            meta_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY reference (reference),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql_purchases);
        
        // Quiz Results Table
        $table_quiz = $wpdb->prefix . 'zonatech_quiz_results';
        $sql_quiz = "CREATE TABLE $table_quiz (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            exam_type varchar(20) NOT NULL,
            subject varchar(100) NOT NULL,
            year int(4) NOT NULL,
            total_questions int(11) NOT NULL,
            correct_answers int(11) NOT NULL,
            wrong_answers int(11) NOT NULL,
            score decimal(5,2) NOT NULL,
            answers_data longtext,
            time_taken int(11),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql_quiz);
        
        // Activity Log Table
        $table_activity = $wpdb->prefix . 'zonatech_activity_log';
        $sql_activity = "CREATE TABLE $table_activity (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            activity_type varchar(50) NOT NULL,
            description text NOT NULL,
            meta_data longtext,
            ip_address varchar(45),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY activity_type (activity_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_activity);
        
        // Scratch Cards Table
        $table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
        $sql_cards = "CREATE TABLE $table_cards (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            card_type varchar(20) NOT NULL,
            pin varchar(50) NOT NULL,
            serial_number varchar(50) NOT NULL,
            status varchar(20) DEFAULT 'available',
            user_id bigint(20),
            purchase_id bigint(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            sold_at datetime,
            PRIMARY KEY (id),
            KEY card_type (card_type),
            KEY status (status),
            UNIQUE KEY pin (pin)
        ) $charset_collate;";
        dbDelta($sql_cards);
        
        // NIN Requests Table
        $table_nin = $wpdb->prefix . 'zonatech_nin_requests';
        $sql_nin = "CREATE TABLE $table_nin (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            nin_number varchar(20) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            slip_url varchar(255),
            purchase_id bigint(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY nin_number (nin_number)
        ) $charset_collate;";
        dbDelta($sql_nin);
        
        // User Subject Access Table
        $table_access = $wpdb->prefix . 'zonatech_user_access';
        $sql_access = "CREATE TABLE $table_access (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            exam_type varchar(20) NOT NULL,
            subject varchar(100) NOT NULL,
            purchase_id bigint(20),
            expires_at datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY exam_subject (exam_type, subject)
        ) $charset_collate;";
        dbDelta($sql_access);
        
        // Downloaded Documents Table
        $table_downloads = $wpdb->prefix . 'zonatech_downloads';
        $sql_downloads = "CREATE TABLE $table_downloads (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            document_type varchar(50) NOT NULL,
            document_name varchar(255) NOT NULL,
            file_url varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql_downloads);
    }
    
    public static function seed_sample_data() {
        global $wpdb;
        $table_questions = $wpdb->prefix . 'zonatech_questions';
        
        // Check if data already exists
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_questions");
        if ($count > 0) {
            return;
        }
        
        $exam_types = array('jamb', 'waec', 'neco');
        $subjects = array(
            'English Language',
            'Mathematics',
            'Physics',
            'Chemistry',
            'Biology',
            'Economics',
            'Government',
            'Literature in English',
            'Commerce',
            'Accounting',
            'Geography',
            'Agricultural Science',
            'Further Mathematics',
            'Computer Science',
            'Civic Education'
        );
        
        $sample_questions = array(
            array(
                'question_text' => 'Which of the following is the correct definition of photosynthesis?',
                'option_a' => 'The process by which plants break down glucose',
                'option_b' => 'The process by which plants convert light energy to chemical energy',
                'option_c' => 'The process by which animals produce energy',
                'option_d' => 'The process of cellular respiration',
                'correct_answer' => 'B',
                'explanation' => 'Photosynthesis is the process by which green plants and some other organisms use sunlight to synthesize foods with the help of chlorophyll.'
            ),
            array(
                'question_text' => 'Solve for x: 2x + 5 = 15',
                'option_a' => 'x = 5',
                'option_b' => 'x = 10',
                'option_c' => 'x = 7.5',
                'option_d' => 'x = 20',
                'correct_answer' => 'A',
                'explanation' => '2x + 5 = 15, 2x = 15 - 5, 2x = 10, x = 5'
            ),
            array(
                'question_text' => 'The SI unit of force is?',
                'option_a' => 'Joule',
                'option_b' => 'Watt',
                'option_c' => 'Newton',
                'option_d' => 'Pascal',
                'correct_answer' => 'C',
                'explanation' => 'The SI unit of force is Newton (N), named after Sir Isaac Newton.'
            ),
            array(
                'question_text' => 'Which of the following is an element?',
                'option_a' => 'Water',
                'option_b' => 'Carbon dioxide',
                'option_c' => 'Sodium chloride',
                'option_d' => 'Oxygen',
                'correct_answer' => 'D',
                'explanation' => 'Oxygen is an element (O), while water (H2O), carbon dioxide (CO2), and sodium chloride (NaCl) are compounds.'
            ),
            array(
                'question_text' => 'What is the capital city of Nigeria?',
                'option_a' => 'Lagos',
                'option_b' => 'Abuja',
                'option_c' => 'Kano',
                'option_d' => 'Port Harcourt',
                'correct_answer' => 'B',
                'explanation' => 'Abuja became the capital city of Nigeria in 1991, replacing Lagos.'
            )
        );
        
        // Insert sample questions for each exam type, subject, and year
        foreach ($exam_types as $exam_type) {
            foreach ($subjects as $subject) {
                for ($year = 2010; $year <= 2024; $year++) {
                    foreach ($sample_questions as $question) {
                        $wpdb->insert($table_questions, array(
                            'exam_type' => $exam_type,
                            'subject' => $subject,
                            'year' => $year,
                            'question_text' => $question['question_text'],
                            'option_a' => $question['option_a'],
                            'option_b' => $question['option_b'],
                            'option_c' => $question['option_c'],
                            'option_d' => $question['option_d'],
                            'correct_answer' => $question['correct_answer'],
                            'explanation' => $question['explanation']
                        ));
                    }
                }
            }
        }
        
        // Seed some sample scratch cards
        $table_cards = $wpdb->prefix . 'zonatech_scratch_cards';
        $card_types = array('waec', 'neco', 'jamb');
        
        foreach ($card_types as $card_type) {
            for ($i = 1; $i <= 100; $i++) {
                $wpdb->insert($table_cards, array(
                    'card_type' => $card_type,
                    'pin' => strtoupper($card_type) . '-' . wp_generate_password(12, false, false),
                    'serial_number' => strtoupper($card_type) . '-SN-' . wp_generate_password(8, false, false),
                    'status' => 'available'
                ));
            }
        }
    }
}
