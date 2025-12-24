<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap zonatech-admin">
    <h1><span class="dashicons dashicons-welcome-learn-more"></span> ZonaTech NG Dashboard</h1>
    
    <div class="zonatech-stats-grid">
        <div class="stat-card">
            <div class="stat-icon users"><span class="dashicons dashicons-groups"></span></div>
            <div class="stat-content">
                <h3><?php echo number_format($users_count); ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon revenue"><span class="dashicons dashicons-money-alt"></span></div>
            <div class="stat-content">
                <h3>₦<?php echo number_format($total_revenue); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon purchases"><span class="dashicons dashicons-cart"></span></div>
            <div class="stat-content">
                <h3><?php echo number_format($purchases_count); ?></h3>
                <p>Completed Purchases</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon quizzes"><span class="dashicons dashicons-clipboard"></span></div>
            <div class="stat-content">
                <h3><?php echo number_format($quizzes_count); ?></h3>
                <p>Quizzes Taken</p>
            </div>
        </div>
    </div>
    
    <div class="zonatech-admin-section">
        <h2>Recent Activity</h2>
        <table class="wp-list-table widefat fixed striped">
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
                            <td><?php echo esc_html($activity->user_name ?? 'Unknown'); ?></td>
                            <td><?php echo esc_html($activity->description); ?></td>
                            <td><?php echo esc_html($activity->time_ago); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No recent activity.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="zonatech-admin-section">
        <h2>Quick Links</h2>
        <div class="quick-links">
            <a href="<?php echo admin_url('admin.php?page=zonatech-questions'); ?>" class="button button-primary">
                <span class="dashicons dashicons-book"></span> Manage Questions
            </a>
            <a href="<?php echo admin_url('admin.php?page=zonatech-cards'); ?>" class="button button-primary">
                <span class="dashicons dashicons-tickets-alt"></span> Manage Scratch Cards
            </a>
            <a href="<?php echo admin_url('admin.php?page=zonatech-settings'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-admin-settings"></span> Settings
            </a>
        </div>
    </div>
</div>
