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
            // Add quiz button at top
            html += '<div style="text-align: center; margin-bottom: 1.5rem;">';
            html += '<button class="btn btn-primary" id="start-quiz-btn" data-exam="' + data.exam_type.toLowerCase() + '" data-subject="' + data.subject + '" data-year="' + data.year + '">';
            html += '<i class="fas fa-play"></i> Start Practice Quiz';
            html += '</button>';
            html += '</div>';
            
            html += '<div class="questions-list">';
            $.each(data.questions, function(index, question) {
                var correctAnswer = question.correct_answer ? question.correct_answer.toUpperCase() : '';
                
                html += '<div class="question-item glass-effect" style="padding: 1rem; margin-bottom: 1rem; border-radius: 10px;">';
                html += '<p class="text-white" style="font-weight: 600;"><strong>Q' + (index + 1) + '.</strong> ' + question.question_text + '</p>';
                html += '<div class="options" style="margin-top: 0.5rem;">';
                
                // Display options with correct answer highlighted
                var options = [
                    { letter: 'A', text: question.option_a },
                    { letter: 'B', text: question.option_b },
                    { letter: 'C', text: question.option_c },
                    { letter: 'D', text: question.option_d }
                ];
                
                $.each(options, function(i, opt) {
                    var isCorrect = opt.letter === correctAnswer;
                    var style = isCorrect ? 'color: #22c55e; font-weight: 600;' : '';
                    var icon = isCorrect ? ' <i class="fas fa-check" style="color: #22c55e;"></i>' : '';
                    html += '<p class="text-muted" style="' + style + '"><strong>' + opt.letter + '.</strong> ' + opt.text + icon + '</p>';
                });
                
                html += '</div>';
                
                // Show explanation if available
                if (question.explanation) {
                    html += '<div style="margin-top: 0.75rem; padding: 0.75rem; background: rgba(139, 92, 246, 0.1); border-radius: 8px; border-left: 3px solid #8b5cf6;">';
                    html += '<p class="text-muted" style="font-size: 0.9rem; margin: 0;"><i class="fas fa-lightbulb" style="color: #f59e0b;"></i> <strong>Explanation:</strong> ' + question.explanation + '</p>';
                    html += '</div>';
                }
                
                // Show correct answer badge
                html += '<div style="margin-top: 0.5rem;">';
                html += '<span style="display: inline-block; padding: 0.25rem 0.75rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">';
                html += '<i class="fas fa-check-circle"></i> Correct Answer: ' + correctAnswer;
                html += '</span>';
                html += '</div>';
                
                html += '</div>';
            });
            html += '</div>';
            
            // Add quiz button at bottom too
            html += '<div style="text-align: center; margin-top: 1.5rem;">';
            html += '<button class="btn btn-primary" id="start-quiz-btn-bottom" data-exam="' + data.exam_type.toLowerCase() + '" data-subject="' + data.subject + '" data-year="' + data.year + '">';
            html += '<i class="fas fa-play"></i> Start Practice Quiz';
            html += '</button>';
            html += '</div>';
        } else {
            html += '<p class="text-muted text-center">No questions available for this selection.</p>';
        }
        
        html += '</div>';
        container.html(html);
        
        // Bind quiz button click handlers
        $('#start-quiz-btn, #start-quiz-btn-bottom').on('click', function() {
            var examType = $(this).data('exam');
            var subject = $(this).data('subject');
            var year = $(this).data('year');
            
            if (typeof window.ZonaTechQuiz !== 'undefined') {
                window.ZonaTechQuiz.startQuiz(examType, subject, year);
            } else {
                // Fallback: start quiz directly
                startQuizDirect(examType, subject, year);
            }
        });
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

// Start quiz function - fallback if ZonaTechQuiz is not loaded
function startQuizDirect(examType, subject, year) {
    var container = jQuery('#questions-container');
    container.html('<div class="loading" style="text-align: center; padding: 3rem;"><div class="spinner" style="border: 3px solid rgba(139, 92, 246, 0.2); border-top-color: #8b5cf6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto;"></div><p style="margin-top: 1rem; color: #a1a1aa;">Loading quiz...</p></div>');
    
    jQuery.ajax({
        url: zonatech_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'zonatech_start_quiz',
            nonce: zonatech_ajax.nonce,
            exam_type: examType,
            subject: subject,
            year: year
        },
        success: function(response) {
            if (response.success) {
                renderQuizMode(response.data);
            } else {
                container.html('<div class="glass-card text-center" style="padding: 2rem;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444; margin-bottom: 1rem;"></i><h3 class="text-white">Error</h3><p class="text-muted">' + (response.data.message || 'Failed to start quiz.') + '</p></div>');
            }
        },
        error: function() {
            container.html('<div class="glass-card text-center" style="padding: 2rem;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444; margin-bottom: 1rem;"></i><h3 class="text-white">Error</h3><p class="text-muted">Failed to start quiz. Please try again.</p></div>');
        }
    });
}

var quizData = null;
var quizAnswers = {};
var quizTimer = null;
var timeRemaining = 0;

function renderQuizMode(data) {
    quizData = data;
    quizAnswers = {};
    timeRemaining = data.time_limit;
    
    var container = jQuery('#questions-container');
    var html = '<div class="quiz-mode">';
    html += '<div class="glass-card mb-2">';
    html += '<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">';
    html += '<div>';
    html += '<h3 class="text-white" style="margin: 0;"><i class="fas fa-clipboard-check"></i> ' + data.exam_type + ' ' + data.subject + ' Quiz - ' + data.year + '</h3>';
    html += '<p class="text-muted" style="margin: 0.25rem 0 0;">Questions: ' + data.total + '</p>';
    html += '</div>';
    html += '<div class="quiz-timer" style="background: rgba(139, 92, 246, 0.2); padding: 0.75rem 1.5rem; border-radius: 10px; text-align: center;">';
    html += '<i class="fas fa-clock" style="color: #8b5cf6;"></i> <span id="quiz-timer" style="font-size: 1.5rem; font-weight: 700; color: #fff;">--:--</span>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    
    // Progress bar
    html += '<div class="mb-2" style="background: rgba(255,255,255,0.1); border-radius: 10px; padding: 0.75rem;">';
    html += '<div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">';
    html += '<small class="text-muted">Progress</small>';
    html += '<small class="text-muted"><span id="answered-count">0</span>/' + data.total + ' answered</small>';
    html += '</div>';
    html += '<div style="background: rgba(255,255,255,0.1); border-radius: 5px; height: 8px; overflow: hidden;">';
    html += '<div id="quiz-progress" style="width: 0%; height: 100%; background: linear-gradient(135deg, #8b5cf6, #7c3aed); transition: width 0.3s;"></div>';
    html += '</div>';
    html += '</div>';
    
    // Questions
    html += '<div class="questions-list">';
    jQuery.each(data.questions, function(index, q) {
        html += '<div class="question-card glass-effect" data-question-id="' + q.id + '" style="padding: 1.5rem; margin-bottom: 1rem; border-radius: 15px;">';
        html += '<div style="display: flex; align-items: flex-start; gap: 1rem;">';
        html += '<span class="question-number" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #fff; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">' + (index + 1) + '</span>';
        html += '<p class="question-text text-white" style="margin: 0; font-size: 1rem; line-height: 1.6;">' + q.question_text + '</p>';
        html += '</div>';
        html += '<div class="question-options" style="margin-top: 1rem; display: grid; gap: 0.5rem;">';
        
        var options = [
            { letter: 'A', text: q.option_a },
            { letter: 'B', text: q.option_b },
            { letter: 'C', text: q.option_c },
            { letter: 'D', text: q.option_d }
        ];
        
        jQuery.each(options, function(i, opt) {
            html += '<div class="option-item" data-answer="' + opt.letter + '" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: rgba(255,255,255,0.05); border: 2px solid transparent; border-radius: 10px; cursor: pointer; transition: all 0.2s;">';
            html += '<span class="option-letter" style="background: rgba(139, 92, 246, 0.3); color: #8b5cf6; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem;">' + opt.letter + '</span>';
            html += '<span class="option-text text-muted">' + opt.text + '</span>';
            html += '</div>';
        });
        
        html += '</div>';
        html += '</div>';
    });
    html += '</div>';
    
    // Submit button
    html += '<div style="text-align: center; margin-top: 1.5rem;">';
    html += '<button class="btn btn-primary btn-lg" id="submit-quiz-btn" style="padding: 1rem 3rem;">';
    html += '<i class="fas fa-check"></i> Submit Quiz';
    html += '</button>';
    html += '</div>';
    html += '</div>';
    
    container.html(html);
    
    // Start timer
    startQuizTimer();
    
    // Bind option click
    jQuery('.quiz-mode .option-item').on('click', function() {
        var questionId = jQuery(this).closest('.question-card').data('question-id');
        var answer = jQuery(this).data('answer');
        
        jQuery(this).closest('.question-options').find('.option-item').css({
            'border-color': 'transparent',
            'background': 'rgba(255,255,255,0.05)'
        });
        jQuery(this).css({
            'border-color': '#8b5cf6',
            'background': 'rgba(139, 92, 246, 0.2)'
        });
        
        quizAnswers[questionId] = answer;
        updateQuizProgress();
    });
    
    // Bind submit
    jQuery('#submit-quiz-btn').on('click', function() {
        if (Object.keys(quizAnswers).length === 0) {
            if (typeof ZonaTechNotify !== 'undefined') {
                ZonaTechNotify.show('Please answer at least one question.', 'warning');
            } else {
                alert('Please answer at least one question.');
            }
            return;
        }
        
        if (confirm('Are you sure you want to submit the quiz?')) {
            submitQuizAnswers();
        }
    });
}

function startQuizTimer() {
    quizTimer = setInterval(function() {
        timeRemaining--;
        
        var minutes = Math.floor(timeRemaining / 60);
        var seconds = timeRemaining % 60;
        
        jQuery('#quiz-timer').text(
            String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0')
        );
        
        if (timeRemaining <= 60) {
            jQuery('#quiz-timer').css('color', '#ef4444');
        }
        
        if (timeRemaining <= 0) {
            clearInterval(quizTimer);
            if (typeof ZonaTechNotify !== 'undefined') {
                ZonaTechNotify.show('Time is up! Submitting your quiz...', 'warning');
            }
            submitQuizAnswers();
        }
    }, 1000);
}

function updateQuizProgress() {
    var total = quizData.total;
    var answered = Object.keys(quizAnswers).length;
    var percent = (answered / total) * 100;
    
    jQuery('#quiz-progress').css('width', percent + '%');
    jQuery('#answered-count').text(answered);
}

function submitQuizAnswers() {
    clearInterval(quizTimer);
    
    var container = jQuery('#questions-container');
    container.html('<div class="loading" style="text-align: center; padding: 3rem;"><div class="spinner" style="border: 3px solid rgba(139, 92, 246, 0.2); border-top-color: #8b5cf6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto;"></div><p style="margin-top: 1rem; color: #a1a1aa;">Submitting quiz...</p></div>');
    
    var timeTaken = quizData.time_limit - timeRemaining;
    
    jQuery.ajax({
        url: zonatech_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'zonatech_submit_quiz',
            nonce: zonatech_ajax.nonce,
            exam_type: quizData.exam_type.toLowerCase(),
            subject: quizData.subject,
            year: quizData.year,
            answers: JSON.stringify(quizAnswers),
            time_taken: timeTaken
        },
        success: function(response) {
            if (response.success) {
                showQuizResults(response.data);
            } else {
                container.html('<div class="glass-card text-center" style="padding: 2rem;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444; margin-bottom: 1rem;"></i><h3 class="text-white">Error</h3><p class="text-muted">' + (response.data.message || 'Failed to submit quiz.') + '</p></div>');
            }
        },
        error: function() {
            container.html('<div class="glass-card text-center" style="padding: 2rem;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444; margin-bottom: 1rem;"></i><h3 class="text-white">Error</h3><p class="text-muted">Failed to submit quiz. Please try again.</p></div>');
        }
    });
}

function showQuizResults(data) {
    var container = jQuery('#questions-container');
    var gradeColor = data.score >= 50 ? '#22c55e' : '#ef4444';
    
    var html = '<div class="glass-card text-center" style="padding: 2rem;">';
    html += '<div style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, ' + gradeColor + '40, ' + gradeColor + '20); display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">';
    html += '<span style="font-size: 2.5rem; font-weight: 700; color: ' + gradeColor + ';">' + data.score + '%</span>';
    html += '<span style="color: #a1a1aa; font-size: 0.9rem;">Score</span>';
    html += '</div>';
    
    html += '<h2 class="text-white" style="margin: 0 0 0.5rem;">Grade: ' + data.grade + '</h2>';
    html += '<p class="text-muted">' + data.message + '</p>';
    
    html += '<div style="display: flex; justify-content: center; gap: 2rem; margin: 1.5rem 0;">';
    html += '<div style="text-align: center;">';
    html += '<div style="font-size: 2rem; font-weight: 700; color: #22c55e;">' + data.correct + '</div>';
    html += '<div style="color: #a1a1aa; font-size: 0.85rem;">Correct</div>';
    html += '</div>';
    html += '<div style="text-align: center;">';
    html += '<div style="font-size: 2rem; font-weight: 700; color: #ef4444;">' + data.wrong + '</div>';
    html += '<div style="color: #a1a1aa; font-size: 0.85rem;">Wrong</div>';
    html += '</div>';
    html += '<div style="text-align: center;">';
    html += '<div style="font-size: 2rem; font-weight: 700; color: #8b5cf6;">' + data.total + '</div>';
    html += '<div style="color: #a1a1aa; font-size: 0.85rem;">Total</div>';
    html += '</div>';
    html += '</div>';
    
    html += '<div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">';
    html += '<button class="btn btn-primary" id="view-corrections-btn" data-result-id="' + data.result_id + '">';
    html += '<i class="fas fa-eye"></i> View Corrections';
    html += '</button>';
    html += '<a href="' + window.location.href + '" class="btn btn-secondary">';
    html += '<i class="fas fa-redo"></i> Try Again';
    html += '</a>';
    html += '</div>';
    html += '</div>';
    
    container.html(html);
    
    // Bind view corrections button
    jQuery('#view-corrections-btn').on('click', function() {
        var resultId = jQuery(this).data('result-id');
        viewQuizCorrections(resultId);
    });
}

function viewQuizCorrections(resultId) {
    var container = jQuery('#questions-container');
    container.html('<div class="loading" style="text-align: center; padding: 3rem;"><div class="spinner" style="border: 3px solid rgba(139, 92, 246, 0.2); border-top-color: #8b5cf6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto;"></div><p style="margin-top: 1rem; color: #a1a1aa;">Loading corrections...</p></div>');
    
    jQuery.ajax({
        url: zonatech_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'zonatech_get_corrections',
            nonce: zonatech_ajax.nonce,
            result_id: resultId
        },
        success: function(response) {
            if (response.success) {
                if (response.data.corrections.length === 0) {
                    container.html('<div class="glass-card text-center" style="padding: 3rem;"><i class="fas fa-trophy" style="font-size: 3rem; color: #22c55e; margin-bottom: 1rem;"></i><h3 class="text-white">Perfect Score!</h3><p class="text-muted">' + response.data.message + '</p><a href="' + window.location.href + '" class="btn btn-primary mt-2"><i class="fas fa-redo"></i> Take Another Quiz</a></div>');
                    return;
                }
                
                var html = '<div class="glass-card mb-2"><h3 class="text-white"><i class="fas fa-check-double"></i> Corrections</h3><p class="text-muted">Review the questions you got wrong</p></div>';
                html += '<div class="corrections-list">';
                
                jQuery.each(response.data.corrections, function(index, c) {
                    html += '<div class="question-card glass-effect" style="padding: 1.5rem; margin-bottom: 1rem; border-radius: 15px;">';
                    html += '<div style="display: flex; align-items: flex-start; gap: 1rem;">';
                    html += '<span style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">' + (index + 1) + '</span>';
                    html += '<p class="text-white" style="margin: 0;">' + c.question + '</p>';
                    html += '</div>';
                    html += '<div style="margin-top: 1rem; display: grid; gap: 0.5rem;">';
                    
                    var letters = ['A', 'B', 'C', 'D'];
                    jQuery.each(letters, function(i, letter) {
                        var isCorrect = letter === c.correct_answer;
                        var isUserAnswer = letter === c.your_answer;
                        var bgColor = 'rgba(255,255,255,0.05)';
                        var borderColor = 'transparent';
                        var icon = '';
                        
                        if (isCorrect) {
                            bgColor = 'rgba(34, 197, 94, 0.2)';
                            borderColor = '#22c55e';
                            icon = ' <i class="fas fa-check" style="color: #22c55e; margin-left: auto;"></i>';
                        } else if (isUserAnswer) {
                            bgColor = 'rgba(239, 68, 68, 0.2)';
                            borderColor = '#ef4444';
                            icon = ' <i class="fas fa-times" style="color: #ef4444; margin-left: auto;"></i>';
                        }
                        
                        html += '<div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: ' + bgColor + '; border: 2px solid ' + borderColor + '; border-radius: 10px;">';
                        html += '<span style="background: rgba(139, 92, 246, 0.3); color: #8b5cf6; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">' + letter + '</span>';
                        html += '<span class="text-muted">' + c.options[letter] + '</span>';
                        html += icon;
                        html += '</div>';
                    });
                    
                    if (c.explanation) {
                        html += '<div style="margin-top: 1rem; padding: 1rem; background: rgba(139, 92, 246, 0.1); border-radius: 10px; border-left: 3px solid #8b5cf6;">';
                        html += '<p style="margin: 0; color: #a1a1aa;"><i class="fas fa-lightbulb" style="color: #f59e0b;"></i> <strong>Explanation:</strong> ' + c.explanation + '</p>';
                        html += '</div>';
                    }
                    
                    html += '</div>';
                    html += '</div>';
                });
                
                html += '</div>';
                html += '<div style="text-align: center; margin-top: 1.5rem;"><a href="' + window.location.href + '" class="btn btn-primary"><i class="fas fa-redo"></i> Try Again</a></div>';
                
                container.html(html);
            } else {
                container.html('<div class="glass-card text-center" style="padding: 2rem;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444; margin-bottom: 1rem;"></i><h3 class="text-white">Error</h3><p class="text-muted">' + (response.data.message || 'Failed to load corrections.') + '</p></div>');
            }
        },
        error: function() {
            container.html('<div class="glass-card text-center" style="padding: 2rem;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444; margin-bottom: 1rem;"></i><h3 class="text-white">Error</h3><p class="text-muted">Failed to load corrections. Please try again.</p></div>');
        }
    });
}

// Legacy startQuiz function (global scope)
function startQuiz(examType, subject, year) {
    startQuizDirect(examType, subject, year);
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
