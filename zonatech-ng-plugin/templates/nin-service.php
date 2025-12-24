<?php
/**
 * NIN Service Template
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
                <a href="<?php echo home_url(); ?>"><i class="fas fa-home"></i> Home</a>
                <a href="<?php echo home_url('/zonatech-past-questions/'); ?>"><i class="fas fa-book-open"></i> Past Questions</a>
                <a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>"><i class="fas fa-credit-card"></i> Scratch Cards</a>
                <a href="<?php echo home_url('/zonatech-nin-service/'); ?>" class="active"><i class="fas fa-id-card"></i> NIN Service</a>
                <a href="<?php echo home_url('/zonatech-dashboard/'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
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
            <a href="<?php echo home_url('/zonatech-past-questions/'); ?>"><i class="fas fa-book-open"></i> Past Questions</a>
            <a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>"><i class="fas fa-credit-card"></i> Scratch Cards</a>
            <a href="<?php echo home_url('/zonatech-nin-service/'); ?>" class="active"><i class="fas fa-id-card"></i> NIN Service</a>
            <a href="<?php echo home_url('/zonatech-dashboard/'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </nav>
        
        <!-- Page Header -->
        <div class="section">
            <div class="section-header">
                <h2 class="text-white"><i class="fas fa-id-card"></i> NIN Service</h2>
                <p class="text-muted">Retrieve your NIN number and download your premium NIN slip</p>
            </div>
        </div>
        
        <!-- NIN Verification -->
        <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
            <h3 class="text-white"><i class="fas fa-search"></i> Verify Your NIN</h3>
            <p class="text-muted">Enter your 11-digit NIN number to verify and download your slip</p>
            
            <div class="form-group">
                <label for="nin-input" class="text-white"><i class="fas fa-id-badge"></i> NIN Number</label>
                <div class="input-with-icon">
                    <i class="fas fa-id-badge input-icon"></i>
                    <input type="text" id="nin-input" class="form-control form-control-icon" placeholder="Enter your 11-digit NIN" maxlength="11" pattern="\d{11}">
                </div>
            </div>
            
            <button id="verify-nin-btn" class="btn btn-primary btn-lg" style="width: 100%;">
                <i class="fas fa-search"></i> Verify NIN
            </button>
            
            <div id="nin-result"></div>
        </div>
        
        <!-- Pricing Info -->
        <div class="glass-card mt-3" style="max-width: 600px; margin: 2rem auto;">
            <h3 class="text-white"><i class="fas fa-tag"></i> Service Pricing</h3>
            <div class="payment-card">
                <div class="payment-info">
                    <div class="payment-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="payment-details">
                        <h4 class="text-white">Premium NIN Slip Download</h4>
                        <p class="text-muted">High-quality PDF with full details</p>
                    </div>
                </div>
                <div class="payment-amount">₦<?php echo number_format(ZONATECH_NIN_SLIP_PRICE); ?></div>
            </div>
        </div>
        
        <!-- Features -->
        <div class="section">
            <div class="section-header">
                <h2 class="text-white"><i class="fas fa-gift"></i> What You Get</h2>
            </div>
            <div class="cards-grid" style="max-width: 900px; margin: 0 auto;">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Premium Quality</h4>
                        <p>High-resolution PDF slip suitable for all official purposes</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Instant Download</h4>
                        <p>Get your slip immediately after payment</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">Secure & Verified</h4>
                        <p>All data retrieved from official sources</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Important Notice -->
        <div class="alert alert-info" style="max-width: 600px; margin: 0 auto;">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> This service helps you retrieve and download your NIN slip. 
            Please ensure you enter the correct NIN number to avoid issues.
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
});
</script>
