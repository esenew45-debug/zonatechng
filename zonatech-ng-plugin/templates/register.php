<?php
/**
 * Register Template
 */

if (!defined('ABSPATH')) exit;
?>

<div class="zonatech-container">
    <div class="zonatech-wrapper">
        <!-- Registration Form -->
        <div class="auth-card glass-effect" id="register-card">
            <div class="auth-header">
                <div class="zonatech-logo mb-2">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
                <h2 class="text-white"><i class="fas fa-user-plus"></i> Create Account</h2>
                <p class="text-muted">Join thousands of students preparing for success</p>
            </div>
            
            <form id="zonatech-register-form">
                <div class="row">
                    <div class="col col-sm-12" style="flex: 1; min-width: 140px;">
                        <div class="form-group">
                            <label for="first_name" class="text-white"><i class="fas fa-user"></i> First Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="first_name" id="first_name" class="form-control form-control-icon" placeholder="First name" required>
                            </div>
                        </div>
                    </div>
                    <div class="col col-sm-12" style="flex: 1; min-width: 140px;">
                        <div class="form-group">
                            <label for="last_name" class="text-white"><i class="fas fa-user"></i> Last Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="last_name" id="last_name" class="form-control form-control-icon" placeholder="Last name" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="text-white"><i class="fas fa-envelope"></i> Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="email" class="form-control form-control-icon" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="text-white"><i class="fas fa-phone"></i> Phone Number <span class="text-muted">(Optional)</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" name="phone" id="phone" class="form-control form-control-icon" placeholder="e.g., 08012345678">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="text-white"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="form-control form-control-icon" placeholder="Create a password (min. 6 characters)" required minlength="6">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="text-white"><i class="fas fa-lock"></i> Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-icon" placeholder="Confirm your password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="terms" required style="width: auto; margin-top: 0.25rem;">
                        <span style="font-size: 0.8rem; line-height: 1.5;" class="text-white">
                            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                        </span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> <span>Create Account</span>
                </button>
            </form>
            
            <div class="auth-divider">
                <span>or</span>
            </div>
            
            <p class="text-center text-muted" style="font-size: 0.85rem;">
                Already have an account? 
                <a href="<?php echo home_url('/zonatech-login/'); ?>"><i class="fas fa-sign-in-alt"></i> Sign in</a>
            </p>
        </div>
        
        <!-- Email Verification Card (Hidden by default) -->
        <div class="auth-card glass-effect" id="verification-card" style="display: none;">
            <div class="auth-header">
                <div class="zonatech-logo mb-2">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
                <div style="font-size: 3rem; color: var(--zona-purple); margin-bottom: 1rem;">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h2 class="text-white"><i class="fas fa-shield-alt"></i> Verify Your Email</h2>
                <p class="text-muted">We've sent a 6-digit verification code to your email address. Please enter it below.</p>
            </div>
            
            <form id="zonatech-verify-form">
                <input type="hidden" name="pending_user_id" id="pending_user_id" value="">
                
                <div class="form-group">
                    <label for="verification_code" class="text-white"><i class="fas fa-key"></i> Verification Code</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key input-icon"></i>
                        <input type="text" name="verification_code" id="verification_code" class="form-control form-control-icon" placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}" required style="letter-spacing: 8px; text-align: center; font-size: 1.5rem; font-weight: bold;">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-check-circle"></i> <span>Verify Email</span>
                </button>
                
                <div class="mt-2 text-center">
                    <p class="text-muted" style="font-size: 0.85rem;">
                        Didn't receive the code? 
                        <a href="#" id="resend-code-link"><i class="fas fa-redo"></i> Resend Code</a>
                    </p>
                </div>
            </form>
            
            <div class="auth-divider">
                <span>or</span>
            </div>
            
            <p class="text-center">
                <a href="#" id="back-to-register"><i class="fas fa-arrow-left"></i> Back to Registration</a>
            </p>
        </div>
        
        <!-- Success Card (Hidden by default) -->
        <div class="auth-card glass-effect" id="success-card" style="display: none;">
            <div class="auth-header">
                <div class="zonatech-logo mb-2">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
                <div style="font-size: 4rem; color: var(--zona-success); margin-bottom: 1rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="text-white"><i class="fas fa-party-horn"></i> Account Verified!</h2>
                <p class="text-muted">Your email has been verified successfully. You can now login to your account.</p>
            </div>
            
            <a href="<?php echo home_url('/zonatech-login/'); ?>" class="btn btn-primary btn-lg" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> <span>Login Now</span>
            </a>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle registration form submission
    $('#zonatech-register-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Account...');
        
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_register',
                nonce: zonatech_ajax.nonce,
                first_name: $form.find('[name="first_name"]').val(),
                last_name: $form.find('[name="last_name"]').val(),
                email: $form.find('[name="email"]').val(),
                phone: $form.find('[name="phone"]').val(),
                password: $form.find('[name="password"]').val(),
                confirm_password: $form.find('[name="confirm_password"]').val()
            },
            success: function(response) {
                if (response.success) {
                    // Show verification card
                    $('#register-card').fadeOut(300, function() {
                        $('#pending_user_id').val(response.data.pending_user_id);
                        $('#verification-card').fadeIn(300);
                    });
                    
                    if (typeof ZonaTechNotify !== 'undefined') {
                        ZonaTechNotify.success(response.data.message);
                    }
                } else {
                    if (typeof ZonaTechNotify !== 'undefined') {
                        ZonaTechNotify.error(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                }
            },
            error: function() {
                if (typeof ZonaTechNotify !== 'undefined') {
                    ZonaTechNotify.error('An error occurred. Please try again.');
                } else {
                    alert('An error occurred. Please try again.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Handle verification form submission
    $('#zonatech-verify-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Verifying...');
        
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_verify_email',
                nonce: zonatech_ajax.nonce,
                pending_user_id: $('#pending_user_id').val(),
                verification_code: $form.find('[name="verification_code"]').val()
            },
            success: function(response) {
                if (response.success) {
                    // Show success card
                    $('#verification-card').fadeOut(300, function() {
                        $('#success-card').fadeIn(300);
                    });
                    
                    if (typeof ZonaTechNotify !== 'undefined') {
                        ZonaTechNotify.success(response.data.message);
                    }
                } else {
                    if (typeof ZonaTechNotify !== 'undefined') {
                        ZonaTechNotify.error(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                }
            },
            error: function() {
                if (typeof ZonaTechNotify !== 'undefined') {
                    ZonaTechNotify.error('An error occurred. Please try again.');
                } else {
                    alert('An error occurred. Please try again.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Resend verification code
    $('#resend-code-link').on('click', function(e) {
        e.preventDefault();
        
        var $link = $(this);
        $link.html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_resend_verification',
                nonce: zonatech_ajax.nonce,
                pending_user_id: $('#pending_user_id').val()
            },
            success: function(response) {
                if (response.success) {
                    if (typeof ZonaTechNotify !== 'undefined') {
                        ZonaTechNotify.success(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                } else {
                    if (typeof ZonaTechNotify !== 'undefined') {
                        ZonaTechNotify.error(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                }
            },
            complete: function() {
                $link.html('<i class="fas fa-redo"></i> Resend Code');
            }
        });
    });
    
    // Back to registration
    $('#back-to-register').on('click', function(e) {
        e.preventDefault();
        $('#verification-card').fadeOut(300, function() {
            $('#register-card').fadeIn(300);
        });
    });
});
</script>
