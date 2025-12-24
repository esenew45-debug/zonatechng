<?php
/**
 * Register Template
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
                <h2 class="text-white">Create Account</h2>
                <p class="text-muted">Join thousands of students preparing for success</p>
            </div>
            
            <form id="zonatech-register-form">
                <div class="row">
                    <div class="col col-sm-12" style="flex: 1; min-width: 140px;">
                        <div class="form-group">
                            <label for="first_name"><i class="fas fa-user"></i> First Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="first_name" id="first_name" class="form-control form-control-icon" placeholder="First name" required>
                            </div>
                        </div>
                    </div>
                    <div class="col col-sm-12" style="flex: 1; min-width: 140px;">
                        <div class="form-group">
                            <label for="last_name"><i class="fas fa-user"></i> Last Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="last_name" id="last_name" class="form-control form-control-icon" placeholder="Last name" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" id="email" class="form-control form-control-icon" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number <span class="text-muted">(Optional)</span></label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" name="phone" id="phone" class="form-control form-control-icon" placeholder="e.g., 08012345678">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="form-control form-control-icon" placeholder="Create a password (min. 6 characters)" required minlength="6">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
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
    </div>
</div>
