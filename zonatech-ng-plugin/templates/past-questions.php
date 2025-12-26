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
                <a href="<?php echo site_url(); ?>"><i class="fas fa-home"></i> Home</a>
                <a href="<?php echo site_url('/zonatech-past-questions/'); ?>" class="active"><i class="fas fa-book-open"></i> Past Questions</a>
                <a href="<?php echo site_url('/zonatech-scratch-cards/'); ?>"><i class="fas fa-credit-card"></i> Scratch Cards</a>
                <a href="<?php echo site_url('/zonatech-nin-service/'); ?>"><i class="fas fa-id-card"></i> NIN Service</a>
                <?php if (is_user_logged_in()): ?>
                    <a href="<?php echo site_url('/zonatech-dashboard/'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo site_url('/zonatech-login/'); ?>"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="<?php echo site_url('/zonatech-register/'); ?>" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </nav>
            
            <!-- Hamburger Menu -->
            <div class="hamburger-menu" id="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        
        <!-- Mobile Navigation Overlay -->
        <div class="mobile-nav-overlay" id="mobile-nav-overlay"></div>
        
        <!-- Mobile Navigation -->
        <nav class="mobile-nav" id="mobile-nav">
            <div class="mobile-nav-header">
                <div class="zonatech-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
                <button class="mobile-nav-close" id="mobile-nav-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <a href="<?php echo site_url(); ?>"><i class="fas fa-home"></i> Home</a>
            <a href="<?php echo site_url('/zonatech-past-questions/'); ?>" class="active"><i class="fas fa-book-open"></i> Past Questions</a>
            <a href="<?php echo site_url('/zonatech-scratch-cards/'); ?>"><i class="fas fa-credit-card"></i> Scratch Cards</a>
            <a href="<?php echo site_url('/zonatech-nin-service/'); ?>"><i class="fas fa-id-card"></i> NIN Service</a>
            <?php if (is_user_logged_in()): ?>
                <a href="<?php echo site_url('/zonatech-dashboard/'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <?php else: ?>
                <a href="<?php echo site_url('/zonatech-login/'); ?>"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="<?php echo site_url('/zonatech-register/'); ?>"><i class="fas fa-user-plus"></i> Create Account</a>
            <?php endif; ?>
        </nav>
        
        <!-- Exam Type Cards -->
        <div class="section" style="margin-top: 1rem;">
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
            <h3 class="text-white"><i class="fas fa-filter"></i> Select Questions</h3>
            <div class="row">
                <div class="col col-md-12" style="flex: 1; min-width: 200px;">
                    <div class="form-group">
                        <label for="exam-type-select" class="text-white"><i class="fas fa-graduation-cap"></i> Exam Type</label>
                        <div class="input-with-icon">
                            <i class="fas fa-graduation-cap input-icon"></i>
                            <select id="exam-type-select" class="form-control form-control-icon">
                                <option value="">Select Exam Type</option>
                                <?php foreach ($exam_types as $type => $exam): ?>
                                    <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($exam['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col col-md-12" style="flex: 1; min-width: 200px;">
                    <div class="form-group">
                        <label for="subject-select" class="text-white"><i class="fas fa-book"></i> Subject</label>
                        <div class="input-with-icon">
                            <i class="fas fa-book input-icon"></i>
                            <select id="subject-select" class="form-control form-control-icon">
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col col-md-12" style="flex: 1; min-width: 150px;">
                    <div class="form-group">
                        <label for="year-select" class="text-white"><i class="fas fa-calendar-alt"></i> Year</label>
                        <div class="input-with-icon">
                            <i class="fas fa-calendar-alt input-icon"></i>
                            <select id="year-select" class="form-control form-control-icon">
                                <option value="">Select Year</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col col-md-12" style="flex: 0 0 auto;">
                    <div class="form-group">
                        <label class="text-white">&nbsp;</label>
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
                    <h3 class="text-white">Login Required</h3>
                    <p class="text-muted">Please login or create an account to access past questions.</p>
                    <div class="mt-2">
                        <a href="<?php echo site_url('/zonatech-login/'); ?>" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo site_url('/zonatech-register/'); ?>" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="glass-card text-center" style="padding: 3rem;">
                    <i class="fas fa-book-open" style="font-size: 3rem; color: var(--zona-purple); margin-bottom: 1rem;"></i>
                    <h3 class="text-white">Select Your Questions</h3>
                    <p class="text-muted">Choose an exam type, subject, and year to view past questions.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Features Section -->
        <div class="section">
            <div class="section-header">
                <h2 class="text-white"><i class="fas fa-star"></i> Why Choose Our Past Questions?</h2>
            </div>
            <div class="cards-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">2010 - Present</h4>
                        <p>Access questions from over 14 years of examinations</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Practice Tests</h4>
                        <p>Take timed quizzes and see your score instantly</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Corrections</h4>
                        <p>View detailed explanations for every question</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Mobile Friendly</h4>
                        <p>Study anywhere on any device</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="zonatech-footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
                <div class="footer-social">
                    <a href="https://wa.me/234<?php echo substr(ZONATECH_WHATSAPP_NUMBER, 1); ?>" target="_blank" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="mailto:<?php echo ZONATECH_SUPPORT_EMAIL; ?>" title="Email">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
                <p class="footer-copyright">
                    © <?php echo date('Y'); ?> ZonaTech NG. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Mobile Navigation
    var hamburger = $('#hamburger-menu');
    var mobileNav = $('#mobile-nav');
    var mobileNavOverlay = $('#mobile-nav-overlay');
    var mobileNavClose = $('#mobile-nav-close');
    
    function openMobileNav() {
        hamburger.addClass('active');
        mobileNav.addClass('active');
        mobileNavOverlay.addClass('active');
        $('body').css('overflow', 'hidden');
    }
    
    function closeMobileNav() {
        hamburger.removeClass('active');
        mobileNav.removeClass('active');
        mobileNavOverlay.removeClass('active');
        $('body').css('overflow', '');
    }
    
    hamburger.on('click', function() {
        if (mobileNav.hasClass('active')) {
            closeMobileNav();
        } else {
            openMobileNav();
        }
    });
    
    mobileNavClose.on('click', closeMobileNav);
    mobileNavOverlay.on('click', closeMobileNav);
    
    mobileNav.find('a').on('click', function() {
        closeMobileNav();
    });
    
    // =============================================
    // Past Questions - Subject and Year Filtering
    // =============================================
    
    // When exam type changes, load subjects and years
    $('#exam-type-select').on('change', function() {
        var examType = $(this).val();
        var $subjectSelect = $('#subject-select');
        var $yearSelect = $('#year-select');
        
        // Reset subject and year dropdowns
        $subjectSelect.html('<option value="">Loading...</option>');
        $yearSelect.html('<option value="">Select Subject First</option>');
        
        if (!examType) {
            $subjectSelect.html('<option value="">Select Subject</option>');
            return;
        }
        
        // Fetch subjects for the selected exam type
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_get_subjects',
                nonce: zonatech_ajax.nonce,
                exam_type: examType
            },
            success: function(response) {
                if (response.success && response.data.subjects) {
                    var options = '<option value="">Select Subject</option>';
                    // Check if we have subjects_with_status for availability info
                    if (response.data.subjects_with_status) {
                        $.each(response.data.subjects_with_status, function(index, subjectInfo) {
                            var availableText = subjectInfo.available ? ' ✓' : '';
                            var dataAttr = subjectInfo.available ? 'data-available="true"' : 'data-available="false"';
                            options += '<option value="' + subjectInfo.name + '" ' + dataAttr + '>' + subjectInfo.name + availableText + '</option>';
                        });
                    } else {
                        $.each(response.data.subjects, function(index, subject) {
                            options += '<option value="' + subject + '">' + subject + '</option>';
                        });
                    }
                    $subjectSelect.html(options);
                    
                    // Also fetch years for the exam type
                    fetchYears(examType, '');
                } else {
                    $subjectSelect.html('<option value="">No subjects available</option>');
                    showNotification('No subjects found for this exam type.', 'warning');
                }
            },
            error: function() {
                $subjectSelect.html('<option value="">Error loading subjects</option>');
                showNotification('Failed to load subjects. Please try again.', 'error');
            }
        });
    });
    
    // When subject changes, load years for that subject
    $('#subject-select').on('change', function() {
        var examType = $('#exam-type-select').val();
        var subject = $(this).val();
        
        if (examType) {
            fetchYears(examType, subject);
        }
    });
    
    // Function to fetch years
    function fetchYears(examType, subject) {
        var $yearSelect = $('#year-select');
        $yearSelect.html('<option value="">Loading years...</option>');
        
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_get_years',
                nonce: zonatech_ajax.nonce,
                exam_type: examType,
                subject: subject
            },
            success: function(response) {
                if (response.success && response.data.years && response.data.years.length > 0) {
                    var options = '<option value="">Select Year</option>';
                    $.each(response.data.years, function(index, year) {
                        options += '<option value="' + year + '">' + year + '</option>';
                    });
                    $yearSelect.html(options);
                } else {
                    // Show default years from 2010 to present
                    var currentYear = new Date().getFullYear();
                    var options = '<option value="">Select Year</option>';
                    for (var y = currentYear; y >= 2010; y--) {
                        options += '<option value="' + y + '">' + y + '</option>';
                    }
                    $yearSelect.html(options);
                }
            },
            error: function() {
                // Fallback to default years
                var currentYear = new Date().getFullYear();
                var options = '<option value="">Select Year</option>';
                for (var y = currentYear; y >= 2010; y--) {
                    options += '<option value="' + y + '">' + y + '</option>';
                }
                $yearSelect.html(options);
            }
        });
    }
    
    // Load Questions button click
    $('#load-questions-btn').on('click', function() {
        var examType = $('#exam-type-select').val();
        var subject = $('#subject-select').val();
        var year = $('#year-select').val();
        
        if (!examType) {
            showNotification('Please select an exam type.', 'warning');
            return;
        }
        
        if (!subject) {
            showNotification('Please select a subject.', 'warning');
            return;
        }
        
        if (!year) {
            showNotification('Please select a year.', 'warning');
            return;
        }
        
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);
        
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_get_questions',
                nonce: zonatech_ajax.nonce,
                exam_type: examType,
                subject: subject,
                year: year
            },
            success: function(response) {
                if (response.success) {
                    displayQuestions(response.data);
                } else {
                    if (response.data && response.data.require_payment) {
                        showPaymentPrompt(response.data.exam_type, response.data.subject);
                    } else {
                        showNotification(response.data.message || 'Failed to load questions.', 'error');
                    }
                }
            },
            error: function() {
                showNotification('An error occurred. Please try again.', 'error');
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Display questions in the container
    function displayQuestions(data) {
        var container = $('#questions-container');
        var html = '<div class="glass-card">';
        html += '<h3 class="text-white"><i class="fas fa-book-open"></i> ' + data.exam_type + ' ' + data.subject + ' - ' + data.year + '</h3>';
        html += '<p class="text-muted mb-2">Total Questions: ' + data.total + '</p>';
        
        if (data.questions && data.questions.length > 0) {
            html += '<div class="questions-list">';
            $.each(data.questions, function(index, question) {
                html += '<div class="question-item glass-effect" style="padding: 1rem; margin-bottom: 1rem; border-radius: 10px;">';
                html += '<p class="text-white" style="font-weight: 600;"><strong>Q' + (index + 1) + '.</strong> ' + question.question_text + '</p>';
                html += '<div class="options" style="margin-top: 0.5rem;">';
                html += '<p class="text-muted"><strong>A.</strong> ' + question.option_a + '</p>';
                html += '<p class="text-muted"><strong>B.</strong> ' + question.option_b + '</p>';
                html += '<p class="text-muted"><strong>C.</strong> ' + question.option_c + '</p>';
                html += '<p class="text-muted"><strong>D.</strong> ' + question.option_d + '</p>';
                html += '</div>';
                html += '</div>';
            });
            html += '</div>';
            
            // Add quiz button
            html += '<div style="text-align: center; margin-top: 1.5rem;">';
            html += '<button class="btn btn-primary" onclick="startQuiz(\'' + data.exam_type + '\', \'' + data.subject + '\', ' + data.year + ')">';
            html += '<i class="fas fa-play"></i> Start Practice Quiz';
            html += '</button>';
            html += '</div>';
        } else {
            html += '<p class="text-muted text-center">No questions available for this selection.</p>';
        }
        
        html += '</div>';
        container.html(html);
    }
    
    // Show payment prompt
    function showPaymentPrompt(examType, subject) {
        var html = '<div class="glass-card text-center" style="padding: 2rem;">';
        html += '<i class="fas fa-lock" style="font-size: 3rem; color: var(--zona-purple); margin-bottom: 1rem;"></i>';
        html += '<h3 class="text-white">Purchase Required</h3>';
        html += '<p class="text-muted">You need to purchase access to ' + examType + ' ' + subject + ' questions.</p>';
        html += '<p class="text-white" style="font-size: 1.5rem; margin: 1rem 0;"><strong>₦' + zonatech_ajax.subject_price.toLocaleString() + '</strong></p>';
        html += '<button class="btn btn-primary" onclick="purchaseSubject(\'' + examType.toLowerCase() + '\', \'' + subject + '\')">';
        html += '<i class="fas fa-credit-card"></i> Buy Now';
        html += '</button>';
        html += '</div>';
        
        $('#questions-container').html(html);
    }
    
    // Helper function to show notifications
    function showNotification(message, type) {
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        } else {
            alert(message);
        }
    }
});

// Start quiz function (global scope)
function startQuiz(examType, subject, year) {
    alert('Starting quiz for ' + examType + ' ' + subject + ' ' + year);
    // TODO: Implement quiz functionality
}

// Purchase subject function (global scope)
function purchaseSubject(examType, subject) {
    if (typeof zonatech_ajax !== 'undefined' && zonatech_ajax.paystack_configured) {
        // Redirect to payment page with proper URL
        var siteUrl = window.location.origin;
        var paymentUrl = siteUrl + '/zonatech-payment/?type=subject&exam_type=' + encodeURIComponent(examType) + '&subject=' + encodeURIComponent(subject) + '&redirect=' + encodeURIComponent(window.location.href);
        window.location.href = paymentUrl;
    } else {
        if (typeof ZonaTechNotify !== 'undefined') {
            ZonaTechNotify.show('Payment system is not configured. Please contact support.', 'error', 5000);
        } else {
            alert('Payment system is not configured. Please contact support at ' + (zonatech_ajax.support_email || 'support@zonatechng.com'));
        }
    }
}
</script>
