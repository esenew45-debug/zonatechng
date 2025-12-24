<?php
/**
 * Dashboard Template
 */

if (!defined('ABSPATH')) exit;

$user = $user_data['user'];
$stats = $user_data['stats'];
?>

<div class="zonatech-container">
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar glass-effect">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
            </div>
            
            <div class="sidebar-user">
                <img src="<?php echo esc_url($user['avatar']); ?>" alt="Avatar" class="sidebar-user-avatar">
                <div class="sidebar-user-info">
                    <h4><?php echo esc_html($user['display_name']); ?></h4>
                    <p class="text-muted"><?php echo esc_html($user['email']); ?></p>
                </div>
            </div>
            
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="#overview" class="active" data-section="overview"><i class="fas fa-home"></i> Overview</a></li>
                    <li><a href="<?php echo home_url('/zonatech-past-questions/'); ?>"><i class="fas fa-book"></i> Past Questions</a></li>
                    <li><a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>"><i class="fas fa-credit-card"></i> Scratch Cards</a></li>
                    <li><a href="<?php echo home_url('/zonatech-nin-service/'); ?>"><i class="fas fa-id-card"></i> NIN Service</a></li>
                    
                    <li class="nav-divider"></li>
                    
                    <li><a href="#profile" data-section="profile"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="#payments" data-section="payments"><i class="fas fa-receipt"></i> Payment History</a></li>
                    <li><a href="#activity" data-section="activity"><i class="fas fa-history"></i> Activity</a></li>
                    
                    <li class="nav-divider"></li>
                    
                    <li><a href="#" class="zonatech-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="dashboard-main">
            <div class="dashboard-header">
                <div class="dashboard-greeting">
                    <h1>Welcome, <?php echo esc_html($user['first_name']); ?>! 👋</h1>
                    <p class="text-muted">Here's an overview of your account</p>
                </div>
                
                <div class="digital-clock glass-effect" id="digital-clock">
                    <div>
                        <span class="clock-time">--:--:--</span>
                        <span class="clock-ampm">--</span>
                    </div>
                    <span class="clock-date">---</span>
                </div>
            </div>
            
            <!-- Overview Section -->
            <div class="dashboard-section-container" id="overview-section">
                <div class="dashboard-stats">
                    <div class="stat-box">
                        <div class="stat-box-icon purple">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="stat-box-content">
                            <h3><?php echo number_format($stats['subjects']); ?></h3>
                            <p class="text-muted">Subjects Purchased</p>
                        </div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-box-icon green">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="stat-box-content">
                            <h3><?php echo number_format($stats['quizzes']); ?></h3>
                            <p class="text-muted">Quizzes Taken</p>
                        </div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-box-icon orange">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-box-content">
                            <h3><?php echo number_format($stats['purchases']); ?></h3>
                            <p class="text-muted">Purchases</p>
                        </div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-box-icon blue">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-box-content">
                            <h3>₦<?php echo number_format($stats['total_spent']); ?></h3>
                            <p class="text-muted">Total Spent</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="dashboard-section">
                    <div class="section-title">
                        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="quick-actions">
                        <a href="<?php echo home_url('/zonatech-past-questions/'); ?>" class="quick-action-btn">
                            <i class="fas fa-book-open"></i>
                            <span>Past Questions</span>
                        </a>
                        <a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>" class="quick-action-btn">
                            <i class="fas fa-credit-card"></i>
                            <span>Buy Scratch Card</span>
                        </a>
                        <a href="<?php echo home_url('/zonatech-nin-service/'); ?>" class="quick-action-btn">
                            <i class="fas fa-id-card"></i>
                            <span>NIN Service</span>
                        </a>
                        <a href="#profile" class="quick-action-btn" data-section="profile">
                            <i class="fas fa-user-edit"></i>
                            <span>Edit Profile</span>
                        </a>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="dashboard-section">
                    <div class="section-title">
                        <h3><i class="fas fa-history"></i> Recent Activity</h3>
                        <a href="#activity" class="btn btn-ghost btn-sm" data-section="activity">View All</a>
                    </div>
                    <div class="activity-list" id="recent-activity-list">
                        <div class="loading"><div class="spinner"></div></div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div class="dashboard-section-container" id="profile-section" style="display: none;">
                <div class="dashboard-section">
                    <div class="section-title">
                        <h3><i class="fas fa-user"></i> Profile Settings</h3>
                    </div>
                    
                    <form id="zonatech-profile-form">
                        <div class="profile-avatar-section">
                            <img src="<?php echo esc_url($user['avatar']); ?>" alt="Avatar" class="profile-avatar">
                            <div>
                                <h4><?php echo esc_html($user['display_name']); ?></h4>
                                <p class="text-muted">Member since <?php echo date('F Y', strtotime($user['registered'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="profile-form">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo esc_attr($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo esc_attr($user['last_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control" value="<?php echo esc_attr($user['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo esc_attr($user['phone']); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary mt-2">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
                
                <div class="dashboard-section mt-3">
                    <div class="section-title">
                        <h3><i class="fas fa-lock"></i> Change Password</h3>
                    </div>
                    
                    <form id="zonatech-change-password-form">
                        <div class="profile-form">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            
                            <div class="form-group" style="grid-column: span 2;">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning mt-2">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Payments Section -->
            <div class="dashboard-section-container" id="payments-section" style="display: none;">
                <div class="dashboard-section">
                    <div class="section-title">
                        <h3><i class="fas fa-receipt"></i> Payment History</h3>
                    </div>
                    <div id="payment-history-list">
                        <div class="loading"><div class="spinner"></div></div>
                    </div>
                </div>
            </div>
            
            <!-- Activity Section -->
            <div class="dashboard-section-container" id="activity-section" style="display: none;">
                <div class="dashboard-section">
                    <div class="section-title">
                        <h3><i class="fas fa-history"></i> Activity History</h3>
                    </div>
                    <div id="activity-history-list">
                        <div class="loading"><div class="spinner"></div></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <button class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay"></div>
</div>

<script>
jQuery(document).ready(function($) {
    // Section navigation
    $('[data-section]').on('click', function(e) {
        e.preventDefault();
        const section = $(this).data('section');
        
        $('.sidebar-nav a').removeClass('active');
        $(this).addClass('active');
        
        $('.dashboard-section-container').hide();
        $('#' + section + '-section').fadeIn();
        
        // Load data for section if needed
        if (section === 'payments') {
            loadPaymentHistory();
        } else if (section === 'activity') {
            loadActivityHistory();
        }
    });
    
    // Load recent activity on page load
    loadRecentActivity();
    
    function loadRecentActivity() {
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_get_activity_log',
                nonce: zonatech_ajax.nonce,
                page: 1
            },
            success: function(response) {
                if (response.success) {
                    renderActivityList('#recent-activity-list', response.data.activities.slice(0, 5));
                }
            }
        });
    }
    
    function loadActivityHistory() {
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_get_activity_log',
                nonce: zonatech_ajax.nonce,
                page: 1
            },
            success: function(response) {
                if (response.success) {
                    renderActivityList('#activity-history-list', response.data.activities);
                }
            }
        });
    }
    
    function renderActivityList(container, activities) {
        if (activities.length === 0) {
            $(container).html('<div class="empty-state"><i class="fas fa-history"></i><p>No activity yet.</p></div>');
            return;
        }
        
        let html = '';
        activities.forEach(function(activity) {
            html += `
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="${activity.icon}"></i>
                    </div>
                    <div class="activity-content">
                        <p>${activity.description}</p>
                        <span class="activity-time">${activity.time_ago}</span>
                    </div>
                </div>
            `;
        });
        $(container).html(html);
    }
    
    function loadPaymentHistory() {
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_get_payment_history',
                nonce: zonatech_ajax.nonce,
                page: 1
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.payments.length === 0) {
                        $('#payment-history-list').html('<div class="empty-state"><i class="fas fa-receipt"></i><p>No payment history yet.</p></div>');
                        return;
                    }
                    
                    let html = '';
                    response.data.payments.forEach(function(payment) {
                        html += `
                            <div class="payment-item">
                                <div class="payment-item-info">
                                    <div class="payment-item-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="payment-item-details">
                                        <h4>${payment.item_name}</h4>
                                        <p>${payment.formatted_date}</p>
                                    </div>
                                </div>
                                <div class="payment-item-amount">
                                    <div class="amount">${payment.formatted_amount}</div>
                                    <span class="status ${payment.status_class}">${payment.status}</span>
                                </div>
                            </div>
                        `;
                    });
                    $('#payment-history-list').html(html);
                }
            }
        });
    }
});
</script>
