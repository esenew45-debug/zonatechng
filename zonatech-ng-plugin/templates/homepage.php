<?php
/**
 * Homepage Template
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
                <a href="#services"><i class="fas fa-concierge-bell"></i> Services</a>
                <a href="#past-questions"><i class="fas fa-book-open"></i> Past Questions</a>
                <a href="#scratch-cards"><i class="fas fa-credit-card"></i> Scratch Cards</a>
                <?php if (is_user_logged_in()): ?>
                    <a href="<?php echo home_url('/zonatech-dashboard/'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php else: ?>
                    <a href="<?php echo home_url('/zonatech-login/'); ?>"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="<?php echo home_url('/zonatech-register/'); ?>" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Get Started</a>
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
            <a href="<?php echo home_url(); ?>"><i class="fas fa-home"></i> Home</a>
            <a href="#services"><i class="fas fa-concierge-bell"></i> Services</a>
            <a href="<?php echo home_url('/zonatech-past-questions/'); ?>"><i class="fas fa-book-open"></i> Past Questions</a>
            <a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>"><i class="fas fa-credit-card"></i> Scratch Cards</a>
            <a href="<?php echo home_url('/zonatech-nin-service/'); ?>"><i class="fas fa-id-card"></i> NIN Service</a>
            <?php if (is_user_logged_in()): ?>
                <a href="<?php echo home_url('/zonatech-dashboard/'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <?php else: ?>
                <a href="<?php echo home_url('/zonatech-login/'); ?>"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="<?php echo home_url('/zonatech-register/'); ?>"><i class="fas fa-user-plus"></i> Create Account</a>
            <?php endif; ?>
        </nav>
        
        <!-- Hero Section -->
        <section class="section" style="text-align: center; padding: 4rem 0;">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;" class="text-gradient-animate text-white">
                Your Gateway to Academic Excellence
            </h1>
            <p style="font-size: 1.1rem; max-width: 600px; margin: 0 auto 2rem;" class="text-white">
                Access JAMB, WAEC, and NECO past questions, purchase scratch cards, 
                and retrieve your NIN - all in one place.
            </p>
            <div class="d-flex justify-center gap-2" style="flex-wrap: wrap;">
                <a href="<?php echo home_url('/zonatech-past-questions/'); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-book-open"></i> Explore Past Questions
                </a>
                <a href="<?php echo home_url('/zonatech-register/'); ?>" class="btn btn-secondary btn-lg">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            </div>
        </section>
        
        <!-- Stats Section -->
        <section class="section">
            <div class="cards-grid" style="max-width: 900px; margin: 0 auto;">
                <div class="stats-card glass-effect">
                    <div class="stats-value">15+</div>
                    <div class="stats-label">Years of Questions</div>
                </div>
                <div class="stats-card glass-effect">
                    <div class="stats-value">15+</div>
                    <div class="stats-label">Subjects</div>
                </div>
                <div class="stats-card glass-effect">
                    <div class="stats-value">3</div>
                    <div class="stats-label">Exam Bodies</div>
                </div>
                <div class="stats-card glass-effect">
                    <div class="stats-value">24/7</div>
                    <div class="stats-label">Access</div>
                </div>
            </div>
        </section>
        
        <!-- Services Section -->
        <section id="services" class="section">
            <div class="section-header">
                <h2 class="text-white"><i class="fas fa-concierge-bell"></i> Our Services</h2>
                <p class="text-muted">Everything you need to succeed in your examinations</p>
            </div>
            
            <div class="cards-grid">
                <!-- Past Questions -->
                <div class="service-card animate-card">
                    <div class="service-card-icon purple">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 class="service-card-title">Past Questions</h3>
                    <p class="service-card-desc">Access JAMB, WAEC, and NECO past questions from 2010 to present with detailed answers.</p>
                    <p class="service-card-price">₦<?php echo number_format(ZONATECH_SUBJECT_PRICE); ?>/subject</p>
                    <a href="<?php echo home_url('/zonatech-past-questions/'); ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> View Questions
                    </a>
                </div>
                
                <!-- Scratch Cards -->
                <div class="service-card animate-card">
                    <div class="service-card-icon green">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="service-card-title">Scratch Cards</h3>
                    <p class="service-card-desc">Purchase WAEC, NECO, and JAMB scratch cards & PINs instantly.</p>
                    <p class="service-card-price">₦<?php echo number_format(ZONATECH_SCRATCH_CARD_PRICE); ?></p>
                    <a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Buy Cards
                    </a>
                </div>
                
                <!-- NIN Service -->
                <div class="service-card animate-card">
                    <div class="service-card-icon orange">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h3 class="service-card-title">NIN Service</h3>
                    <p class="service-card-desc">Retrieve your NIN number and download your premium NIN slip.</p>
                    <p class="service-card-price">₦<?php echo number_format(ZONATECH_NIN_SLIP_PRICE); ?></p>
                    <a href="<?php echo home_url('/zonatech-nin-service/'); ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Get NIN Slip
                    </a>
                </div>
            </div>
        </section>
        
        <!-- Past Questions Section -->
        <section id="past-questions" class="section">
            <div class="section-header">
                <h2 class="text-white"><i class="fas fa-book-open"></i> Past Questions</h2>
                <p class="text-muted">Comprehensive past questions for all major examinations</p>
            </div>
            
            <div class="cards-grid">
                <?php foreach ($exam_types as $type => $exam): ?>
                    <div class="glass-card text-center">
                        <div style="width: 80px; height: 80px; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; background: <?php echo $exam['color']; ?>20; border-radius: 50%; font-size: 2rem; color: <?php echo $exam['color']; ?>;">
                            <i class="<?php echo esc_attr($exam['icon']); ?>"></i>
                        </div>
                        <h3 class="text-white"><?php echo esc_html($exam['name']); ?></h3>
                        <p class="text-muted"><?php echo esc_html($exam['full_name']); ?></p>
                        <p style="font-size: 0.85rem; color: var(--zona-white-muted);">2010 - Present • 15+ Subjects</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Features Section -->
        <section class="section">
            <div class="section-header">
                <h2 class="text-white"><i class="fas fa-medal"></i> Why Choose ZonaTech NG?</h2>
            </div>
            
            <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Mobile App</h4>
                        <p>Install our web app and study offline anytime</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Practice Tests</h4>
                        <p>Take timed quizzes and track your progress</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Instant Corrections</h4>
                        <p>Get detailed explanations for every question</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Instant Delivery</h4>
                        <p>Get your scratch cards and PINs immediately</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Secure Payments</h4>
                        <p>Pay safely with Paystack integration</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">24/7 Support</h4>
                        <p>Get help anytime via WhatsApp or email</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- CTA Section -->
        <section class="section">
            <div class="glass-card glass-effect-purple text-center" style="padding: 3rem;">
                <h2 class="text-white"><i class="fas fa-rocket"></i> Ready to Start Learning?</h2>
                <p class="text-muted" style="max-width: 500px; margin: 0 auto 1.5rem;">
                    Join thousands of students who are preparing for success with ZonaTech NG.
                </p>
                <a href="<?php echo home_url('/zonatech-register/'); ?>" class="btn btn-primary btn-lg glow">
                    <i class="fas fa-rocket"></i> Get Started Now
                </a>
            </div>
        </section>
        
        <!-- Footer -->
        <footer class="zonatech-footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
                <p class="footer-tagline">Your Gateway to Academic Excellence</p>
                
                <div class="footer-links">
                    <a href="<?php echo home_url('/zonatech-past-questions/'); ?>">
                        <i class="fas fa-book-open"></i> Past Questions
                    </a>
                    <a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>">
                        <i class="fas fa-credit-card"></i> Scratch Cards
                    </a>
                    <a href="<?php echo home_url('/zonatech-nin-service/'); ?>">
                        <i class="fas fa-id-card"></i> NIN Service
                    </a>
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
    
    // Close mobile nav when clicking links
    mobileNav.find('a').on('click', function() {
        closeMobileNav();
    });
});
</script>
