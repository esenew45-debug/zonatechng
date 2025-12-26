<?php
/**
 * NIN Service Template
 */

if (!defined('ABSPATH')) exit;
$is_guest = !is_user_logged_in();
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
                <a href="<?php echo site_url('/zonatech-past-questions/'); ?>"><i class="fas fa-book-open"></i> Past Questions</a>
                <a href="<?php echo site_url('/zonatech-scratch-cards/'); ?>"><i class="fas fa-credit-card"></i> Scratch Cards</a>
                <a href="<?php echo site_url('/zonatech-nin-service/'); ?>" class="active"><i class="fas fa-id-card"></i> NIN Service</a>
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
            <a href="<?php echo site_url('/zonatech-past-questions/'); ?>"><i class="fas fa-book-open"></i> Past Questions</a>
            <a href="<?php echo site_url('/zonatech-scratch-cards/'); ?>"><i class="fas fa-credit-card"></i> Scratch Cards</a>
            <a href="<?php echo site_url('/zonatech-nin-service/'); ?>" class="active"><i class="fas fa-id-card"></i> NIN Service</a>
            <?php if (is_user_logged_in()): ?>
                <a href="<?php echo site_url('/zonatech-dashboard/'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <?php else: ?>
                <a href="<?php echo site_url('/zonatech-login/'); ?>"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="<?php echo site_url('/zonatech-register/'); ?>"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </nav>
        
        <?php if ($is_guest): ?>
        <!-- Guest User Prompt -->
        <div class="glass-card glass-effect-purple" style="max-width: 600px; margin: 0 auto 2rem; text-align: center;">
            <div style="width: 80px; height: 80px; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; background: rgba(139, 92, 246, 0.2); border-radius: 50%; font-size: 2rem; color: var(--zona-purple-light);">
                <i class="fas fa-user-lock"></i>
            </div>
            <h3 class="text-white"><i class="fas fa-lock"></i> Login Required</h3>
            <p class="text-muted" style="margin-bottom: 1.5rem;">Create an account or login to verify your NIN and download your premium slip.</p>
            <div class="d-flex justify-center gap-2" style="flex-wrap: wrap;">
                <a href="<?php echo site_url('/zonatech-register/'); ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
                <a href="<?php echo site_url('/zonatech-login/'); ?>" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- NIN Verification -->
        <div class="glass-card" style="max-width: 600px; margin: 0 auto; <?php echo $is_guest ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
            <h3 class="text-white"><i class="fas fa-search"></i> Verify Your NIN</h3>
            <p class="text-muted">Enter your 11-digit NIN number to verify and download your slip</p>
            
            <div class="form-group">
                <label for="nin-input" class="text-white"><i class="fas fa-id-badge"></i> NIN Number</label>
                <div class="input-with-icon">
                    <i class="fas fa-id-badge input-icon"></i>
                    <input type="text" id="nin-input" class="form-control form-control-icon" placeholder="Enter your 11-digit NIN" maxlength="11" pattern="\d{11}" <?php echo $is_guest ? 'disabled' : ''; ?>>
                </div>
            </div>
            
            <button id="verify-nin-btn" class="btn btn-primary btn-lg" style="width: 100%;" <?php echo $is_guest ? 'disabled' : ''; ?>>
                <i class="fas fa-search"></i> Verify NIN
            </button>
            
            <div id="nin-result"></div>
        </div>
        
        <!-- Pricing Info -->
        <div class="glass-card mt-3" style="max-width: 600px; margin: 2rem auto;">
            <h3 class="text-white"><i class="fas fa-tag"></i> Service Pricing</h3>
            
            <!-- Standard Slip -->
            <div class="payment-card" style="margin-bottom: 1rem; cursor: pointer;" id="standard-slip-option" onclick="selectSlipType('standard')">
                <div class="payment-info">
                    <div class="payment-icon" style="background: rgba(139, 92, 246, 0.2);">
                        <i class="fas fa-file"></i>
                    </div>
                    <div class="payment-details">
                        <h4 class="text-white">Standard NIN Slip Download</h4>
                        <p class="text-muted">Basic PDF slip with essential details</p>
                    </div>
                </div>
                <div class="payment-amount" style="color: #4ade80;">₦<?php echo number_format(ZONATECH_NIN_STANDARD_SLIP_PRICE); ?></div>
            </div>
            
            <!-- Premium Slip -->
            <div class="payment-card" style="cursor: pointer;" id="premium-slip-option" onclick="selectSlipType('premium')">
                <div class="payment-info">
                    <div class="payment-icon" style="background: rgba(234, 179, 8, 0.2);">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="payment-details">
                        <h4 class="text-white"><i class="fas fa-crown" style="color: #eab308;"></i> Premium NIN Slip Download</h4>
                        <p class="text-muted">High-quality PDF with full details & photo</p>
                    </div>
                </div>
                <div class="payment-amount" style="color: #eab308;">₦<?php echo number_format(ZONATECH_NIN_SLIP_PRICE); ?></div>
            </div>
            
            <input type="hidden" id="selected-slip-type" value="standard">
            <p class="text-muted text-center" style="margin-top: 1rem; font-size: 0.85rem;">
                <i class="fas fa-info-circle"></i> Click on a plan to select it before verifying your NIN
            </p>
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

// Slip type selection
function selectSlipType(type) {
    document.getElementById('selected-slip-type').value = type;
    
    var standardOption = document.getElementById('standard-slip-option');
    var premiumOption = document.getElementById('premium-slip-option');
    
    // Remove selection from both
    standardOption.style.border = '1px solid rgba(255,255,255,0.1)';
    premiumOption.style.border = '1px solid rgba(255,255,255,0.1)';
    
    // Add selection to chosen option
    if (type === 'standard') {
        standardOption.style.border = '2px solid #4ade80';
        standardOption.style.boxShadow = '0 0 15px rgba(74, 222, 128, 0.3)';
        premiumOption.style.boxShadow = 'none';
    } else {
        premiumOption.style.border = '2px solid #eab308';
        premiumOption.style.boxShadow = '0 0 15px rgba(234, 179, 8, 0.3)';
        standardOption.style.boxShadow = 'none';
    }
}

// Initialize default selection
document.addEventListener('DOMContentLoaded', function() {
    selectSlipType('standard');
});
</script>
