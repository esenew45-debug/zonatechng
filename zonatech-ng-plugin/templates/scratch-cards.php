<?php
/**
 * Scratch Cards Template
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
                <a href="<?php echo home_url('/zonatech-scratch-cards/'); ?>" class="active">Scratch Cards</a>
                <a href="<?php echo home_url('/zonatech-nin-service/'); ?>">NIN Service</a>
                <a href="<?php echo home_url('/zonatech-dashboard/'); ?>">Dashboard</a>
            </nav>
        </div>
        
        <!-- Page Header -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-credit-card"></i> Scratch Cards & PINs</h2>
                <p>Purchase WAEC, NECO, and JAMB scratch cards and PINs instantly</p>
            </div>
        </div>
        
        <!-- Card Types -->
        <div class="cards-grid mb-3">
            <?php foreach ($card_types as $type => $card): ?>
                <div class="service-card animate-card">
                    <div class="service-card-icon" style="background: linear-gradient(135deg, <?php echo $card['color']; ?>20 0%, <?php echo $card['color']; ?>10 100%); color: <?php echo $card['color']; ?>;">
                        <i class="<?php echo esc_attr($card['icon']); ?>"></i>
                    </div>
                    <h3 class="service-card-title"><?php echo esc_html($card['full_name']); ?></h3>
                    <p class="service-card-desc"><?php echo esc_html($card['description']); ?></p>
                    <p class="service-card-price">₦<?php echo number_format($card['price']); ?></p>
                    <button class="btn btn-primary buy-scratch-card-btn" data-card-type="<?php echo esc_attr($type); ?>">
                        <i class="fas fa-shopping-cart"></i> Buy Now
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- My Purchased Cards -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-ticket-alt"></i> My Purchased Cards</h2>
                <p>View all the scratch cards and PINs you've purchased</p>
            </div>
            <div id="user-cards-container">
                <div class="loading"><div class="spinner"></div></div>
            </div>
        </div>
        
        <!-- How It Works -->
        <div class="section">
            <div class="section-header">
                <h2>How It Works</h2>
            </div>
            <div class="cards-grid" style="max-width: 900px; margin: 0 auto;">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <div class="feature-content">
                        <h4>1. Select Card</h4>
                        <p>Choose the type of scratch card you need</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="feature-content">
                        <h4>2. Make Payment</h4>
                        <p>Pay securely with Paystack</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="feature-content">
                        <h4>3. Get Your PIN</h4>
                        <p>Instantly receive your PIN and serial number</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Important Notice -->
        <div class="alert alert-warning" style="max-width: 800px; margin: 2rem auto;">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Important:</strong> Once purchased, scratch cards cannot be refunded. 
            Please ensure you purchase the correct card type for your needs.
        </div>
    </div>
</div>
