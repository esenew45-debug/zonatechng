<?php
/**
 * Login Template
 */

if (!defined('ABSPATH')) exit;
?>

<div class="zonatech-container">
    <div class="zonatech-wrapper">
        <div class="auth-card glass-effect">
            <div class="auth-header">
                <div class="zonatech-logo mb-2">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
                <h2 class="text-white">Welcome Back</h2>
                <p class="text-muted">Sign in to your account</p>
            </div>
            
            <form id="zonatech-login-form">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="email" class="form-control form-control-icon" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="form-control form-control-icon" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="form-group d-flex justify-between align-center">
                    <label style="display: flex; align-items: center; gap: 0.5rem; margin: 0; cursor: pointer;">
                        <input type="checkbox" name="remember" style="width: auto;">
                        <span style="font-size: 0.8rem;" class="text-white">Remember me</span>
                    </label>
                    <a href="#" id="forgot-password-link" style="font-size: 0.8rem;"><i class="fas fa-question-circle"></i> Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> <span>Sign In</span>
                </button>
            </form>
            
            <div class="auth-divider">
                <span>or</span>
            </div>
            
            <p class="text-center text-muted" style="font-size: 0.85rem;">
                Don't have an account? 
                <a href="<?php echo home_url('/zonatech-register/'); ?>"><i class="fas fa-user-plus"></i> Create one</a>
            </p>
        </div>
        
        <!-- Forgot Password Modal -->
        <div id="forgot-password-modal" class="glass-card" style="display: none; max-width: 420px; margin: 2rem auto;">
            <h3 class="text-white"><i class="fas fa-key"></i> Reset Password</h3>
            <p class="text-muted">Enter your email to receive a password reset link.</p>
            
            <form id="zonatech-reset-form">
                <div class="form-group">
                    <label for="reset-email"><i class="fas fa-envelope"></i> Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="reset-email" class="form-control form-control-icon" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
                    <button type="button" class="btn btn-ghost" id="back-to-login"><i class="fas fa-arrow-left"></i> Back to Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#forgot-password-link').on('click', function(e) {
        e.preventDefault();
        $('.auth-card').hide();
        $('#forgot-password-modal').fadeIn();
    });
    
    $('#back-to-login').on('click', function() {
        $('#forgot-password-modal').hide();
        $('.auth-card').fadeIn();
    });
});
</script>
