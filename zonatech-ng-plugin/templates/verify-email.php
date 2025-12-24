<?php
/**
 * Email Verification Template
 */

if (!defined('ABSPATH')) exit;

// Get pending user ID from URL or session
$pending_user_id = isset($_GET['pending_id']) ? intval($_GET['pending_id']) : 0;
$pending_email = isset($_GET['email']) ? sanitize_email(urldecode($_GET['email'])) : '';
?>

<div class="zonatech-container">
    <div class="zonatech-wrapper">
        <!-- Email Verification Card -->
        <div class="auth-card glass-effect" id="verification-card">
            <div class="auth-header">
                <div class="zonatech-logo mb-2">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
                <div style="font-size: 3rem; color: var(--zona-purple); margin-bottom: 1rem;">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h2 class="text-white"><i class="fas fa-shield-alt"></i> Verify Your Email</h2>
                <p class="text-muted">We've sent a 6-digit verification code to your email address<?php echo $pending_email ? ' (' . esc_html($pending_email) . ')' : ''; ?>. Please enter it below.</p>
            </div>
            
            <form id="zonatech-verify-form">
                <input type="hidden" name="pending_user_id" id="pending_user_id" value="<?php echo esc_attr($pending_user_id); ?>">
                
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
            
            <p class="text-center text-muted" style="font-size: 0.85rem;">
                <a href="<?php echo site_url('/zonatech-register/'); ?>"><i class="fas fa-arrow-left"></i> Back to Registration</a>
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
                <h2 class="text-white">Account Verified!</h2>
                <p class="text-muted">Your email has been verified successfully. You can now login to your account.</p>
            </div>
            
            <a href="<?php echo site_url('/zonatech-login/'); ?>" class="btn btn-primary btn-lg" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> <span>Login Now</span>
            </a>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle verification form submission
    $('#zonatech-verify-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var originalText = $btn.html();
        var pendingUserId = $('#pending_user_id').val();
        
        if (!pendingUserId || pendingUserId === '0') {
            if (typeof ZonaTechNotify !== 'undefined') {
                ZonaTechNotify.error('Invalid verification session. Please register again.');
            } else {
                alert('Invalid verification session. Please register again.');
            }
            window.location.href = '<?php echo site_url('/zonatech-register/'); ?>';
            return;
        }
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Verifying...');
        
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_verify_email',
                nonce: zonatech_ajax.nonce,
                pending_user_id: pendingUserId,
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
        
        var pendingUserId = $('#pending_user_id').val();
        
        if (!pendingUserId || pendingUserId === '0') {
            if (typeof ZonaTechNotify !== 'undefined') {
                ZonaTechNotify.error('Invalid verification session. Please register again.');
            } else {
                alert('Invalid verification session. Please register again.');
            }
            window.location.href = '<?php echo site_url('/zonatech-register/'); ?>';
            return;
        }
        
        var $link = $(this);
        $link.html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_resend_verification',
                nonce: zonatech_ajax.nonce,
                pending_user_id: pendingUserId
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
});
</script>
