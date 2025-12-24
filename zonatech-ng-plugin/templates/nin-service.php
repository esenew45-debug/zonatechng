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
                <a href="<?php echo home_url(); ?>">Home</a>
                <a href="<?php echo home_url('/zonatech-past-questions/'); ?>">Past Questions</a>
                <a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>">Scratch Cards</a>
                <a href="<?php echo home_url('/zonatech-nin-service/'); ?>" class="active">NIN Service</a>
                <a href="<?php echo home_url('/zonatech-dashboard/'); ?>">Dashboard</a>
            </nav>
        </div>
        
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
                        <h4>Premium NIN Slip Download</h4>
                        <p>High-quality PDF with full details</p>
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
                        <h4>Premium Quality</h4>
                        <p>High-resolution PDF slip suitable for all official purposes</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Instant Download</h4>
                        <p>Get your slip immediately after payment</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Secure & Verified</h4>
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
    </div>
</div>
