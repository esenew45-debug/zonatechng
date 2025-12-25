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

// Recent feedback
$recent_feedback = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_feedback ORDER BY created_at DESC LIMIT %d",
    5
));

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
            gap: 12px;
            padding: 15px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }
        
        .admin-logo img {
            width: 45px;
            height: 45px;
            border-radius: 12px;
        }
        
        .admin-logo h2 {
            font-size: 18px;
            font-weight: 700;
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .admin-logo span {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            display: block;
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
                <div>
                    <h2>ZonaTech NG</h2>
                    <span>Admin Dashboard</span>
                </div>
            </div>
            
            <ul class="admin-nav">
                <li><a href="#" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="#users"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="#purchases"><i class="fas fa-shopping-cart"></i> Purchases</a></li>
                <li><a href="#questions"><i class="fas fa-book"></i> Questions</a></li>
                <li><a href="#feedback"><i class="fas fa-comments"></i> Feedback</a></li>
                
                <div class="nav-divider"></div>
                
                <li><a href="<?php echo admin_url('admin.php?page=zonatech-questions'); ?>"><i class="fas fa-plus-circle"></i> Add Questions</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=zonatech-cards'); ?>"><i class="fas fa-ticket-alt"></i> Manage Cards</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=zonatech-settings'); ?>"><i class="fas fa-cog"></i> Settings</a></li>
                
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
            <div class="admin-header">
                <h1><i class="fas fa-chart-line"></i> Dashboard Overview</h1>
                <div class="admin-header-actions">
                    <a href="<?php echo admin_url('admin.php?page=zonatech-questions'); ?>" class="btn-admin btn-admin-primary">
                        <i class="fas fa-plus"></i> Add Question
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=zonatech-cards'); ?>" class="btn-admin btn-admin-outline">
                        <i class="fas fa-ticket-alt"></i> Add Cards
                    </a>
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
                        <a href="<?php echo admin_url('admin.php?page=zonatech-ng'); ?>" class="btn-admin btn-admin-outline">View All</a>
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
                        <a href="<?php echo admin_url('admin.php?page=zonatech-users'); ?>" class="btn-admin btn-admin-outline">View All</a>
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
                        <a href="<?php echo admin_url('admin.php?page=zonatech-cards'); ?>" class="btn-admin btn-admin-outline">Manage</a>
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
                    <p style="text-align: center; color: rgba(255,255,255,0.5);">No scratch cards available. <a href="<?php echo admin_url('admin.php?page=zonatech-cards'); ?>" style="color: #8b5cf6;">Add some</a></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Feedback -->
            <div class="admin-section" id="feedback">
                <div class="section-header">
                    <h2><i class="fas fa-comments"></i> Recent Feedback</h2>
                    <a href="<?php echo admin_url('admin.php?page=zonatech-feedback'); ?>" class="btn-admin btn-admin-outline">View All</a>
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
    </script>
</body>
</html>
