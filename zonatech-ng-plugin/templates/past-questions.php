<?php
/**
 * Past Questions Template
 */

if (!defined('ABSPATH')) exit;
?>

<div class="zonatech-container">
    <div class="zonatech-wrapper">
        <!-- Header -->
        <div class="zonatech-header glass-effect">
            <div class="zonatech-logo">
                <i class="fas fa-graduation-cap"></i>
                <span>ZonaTech NG</span>
            </div>
            <nav class="zonatech-nav">
                <a href="<?php echo home_url(); ?>">Home</a>
                <a href="<?php echo home_url('/zonatech-past-questions/'); ?>" class="active">Past Questions</a>
                <a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>">Scratch Cards</a>
                <a href="<?php echo home_url('/zonatech-nin-service/'); ?>">NIN Service</a>
                <?php if (is_user_logged_in()): ?>
                    <a href="<?php echo home_url('/zonatech-dashboard/'); ?>">Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo home_url('/zonatech-login/'); ?>">Login</a>
                <?php endif; ?>
            </nav>
        </div>
        
        <!-- Page Header -->
        <div class="section">
            <div class="section-header">
                <h2>Past Questions</h2>
                <p>Access JAMB, WAEC, and NECO past questions from 2010 till date. Practice and prepare for your exams!</p>
            </div>
            
            <!-- Exam Type Cards -->
            <div class="cards-grid mb-3">
                <?php foreach ($exam_types as $type => $exam): ?>
                    <div class="service-card animate-card">
                        <div class="service-card-icon" style="background: linear-gradient(135deg, <?php echo $exam['color']; ?>20 0%, <?php echo $exam['color']; ?>10 100%); color: <?php echo $exam['color']; ?>;">
                            <i class="<?php echo esc_attr($exam['icon']); ?>"></i>
                        </div>
                        <h3 class="service-card-title"><?php echo esc_html($exam['name']); ?></h3>
                        <p class="service-card-desc"><?php echo esc_html($exam['full_name']); ?></p>
                        <p class="service-card-price">₦<?php echo number_format(ZONATECH_SUBJECT_PRICE); ?>/subject</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="glass-card mb-3">
            <h3><i class="fas fa-filter"></i> Select Questions</h3>
            <div class="row">
                <div class="col col-md-12" style="flex: 1; min-width: 200px;">
                    <div class="form-group">
                        <label for="exam-type-select">Exam Type</label>
                        <select id="exam-type-select" class="form-control">
                            <option value="">Select Exam Type</option>
                            <?php foreach ($exam_types as $type => $exam): ?>
                                <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($exam['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col col-md-12" style="flex: 1; min-width: 200px;">
                    <div class="form-group">
                        <label for="subject-select">Subject</label>
                        <select id="subject-select" class="form-control">
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                </div>
                <div class="col col-md-12" style="flex: 1; min-width: 150px;">
                    <div class="form-group">
                        <label for="year-select">Year</label>
                        <select id="year-select" class="form-control">
                            <option value="">Select Year</option>
                        </select>
                    </div>
                </div>
                <div class="col col-md-12" style="flex: 0 0 auto;">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button id="load-questions-btn" class="btn btn-primary">
                            <i class="fas fa-search"></i> Load Questions
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Questions Container -->
        <div id="questions-container">
            <?php if (!is_user_logged_in()): ?>
                <div class="glass-card text-center" style="padding: 3rem;">
                    <i class="fas fa-user-lock" style="font-size: 3rem; color: var(--zona-purple); margin-bottom: 1rem;"></i>
                    <h3>Login Required</h3>
                    <p>Please login or create an account to access past questions.</p>
                    <div class="mt-2">
                        <a href="<?php echo home_url('/zonatech-login/'); ?>" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo home_url('/zonatech-register/'); ?>" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="glass-card text-center" style="padding: 3rem;">
                    <i class="fas fa-book-open" style="font-size: 3rem; color: var(--zona-purple); margin-bottom: 1rem;"></i>
                    <h3>Select Your Questions</h3>
                    <p>Choose an exam type, subject, and year to view past questions.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Features Section -->
        <div class="section">
            <div class="section-header">
                <h2>Why Choose Our Past Questions?</h2>
            </div>
            <div class="cards-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4>2010 - Present</h4>
                        <p>Access questions from over 14 years of examinations</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Practice Tests</h4>
                        <p>Take timed quizzes and see your score instantly</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Corrections</h4>
                        <p>View detailed explanations for every question</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Mobile Friendly</h4>
                        <p>Study anywhere on any device</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
