<?php
/**
 * Scratch Cards Template
 */

if (!defined('ABSPATH')) exit;
$is_guest = !is_user_logged_in();

// Get OtaPay cards (WAEC/NECO) with new pricing
$otapay_cards = ZonaTech_OtaPay::get_supported_cards();
$otapay_configured = ZonaTech_OtaPay::get_instance()->is_configured();
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
                <a href="<?php echo site_url('/zonatech-scratch-cards/'); ?>" class="active"><i class="fas fa-credit-card"></i> Scratch Cards</a>
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
            <a href="<?php echo site_url('/zonatech-past-questions/'); ?>"><i class="fas fa-book-open"></i> Past Questions</a>
            <a href="<?php echo site_url('/zonatech-scratch-cards/'); ?>" class="active"><i class="fas fa-credit-card"></i> Scratch Cards</a>
            <a href="<?php echo site_url('/zonatech-nin-service/'); ?>"><i class="fas fa-id-card"></i> NIN Service</a>
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
            <h3 class="text-white"><i class="fas fa-lock"></i> Login Required to Purchase</h3>
            <p class="text-muted" style="margin-bottom: 1.5rem;">Create an account or login to purchase scratch cards and PINs.</p>
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
        
        <!-- Page Title -->
        <div class="section-header" style="text-align: center; margin-bottom: 2rem;">
            <h1 class="text-white"><i class="fas fa-credit-card" style="color: #f59e0b;"></i> Buy WAEC & NECO Result Checker PINs</h1>
            <p class="text-muted">Get instant access to your exam results with our automated scratch card service</p>
        </div>
        
        <!-- Card Types - OtaPay Integrated -->
        <div class="cards-grid mb-3">
            <?php foreach ($otapay_cards as $type => $card): ?>
                <div class="service-card animate-card">
                    <div class="service-card-icon" style="background: linear-gradient(135deg, <?php echo $card['color']; ?>20 0%, <?php echo $card['color']; ?>10 100%); color: <?php echo $card['color']; ?>;">
                        <i class="<?php echo esc_attr($card['icon']); ?>"></i>
                    </div>
                    <h3 class="service-card-title"><?php echo esc_html($card['full_name']); ?></h3>
                    <p class="service-card-desc"><?php echo esc_html($card['description']); ?></p>
                    <p class="service-card-price">₦<?php echo number_format($card['price']); ?></p>
                    
                    <?php if (!$otapay_configured): ?>
                        <span class="btn btn-secondary" style="cursor: not-allowed; opacity: 0.6;">
                            <i class="fas fa-clock"></i> Coming Soon
                        </span>
                    <?php elseif ($is_guest): ?>
                        <a href="<?php echo site_url('/zonatech-register/'); ?>" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Register to Buy
                        </a>
                    <?php else: ?>
                        <button class="btn btn-primary buy-otapay-card-btn" data-card-type="<?php echo esc_attr($type); ?>" data-price="<?php echo esc_attr($card['price']); ?>">
                            <i class="fas fa-shopping-cart"></i> Buy Now
                        </button>
                    <?php endif; ?>
                    
                    <!-- Instant Delivery Badge -->
                    <div style="margin-top: 0.75rem;">
                        <span style="display: inline-block; padding: 0.25rem 0.75rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                            <i class="fas fa-bolt"></i> Instant Delivery
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!$is_guest): ?>
        <!-- My Purchased Cards -->
        <div class="section">
            <div class="section-header">
                <h2 class="text-white"><i class="fas fa-ticket-alt"></i> My Purchased Cards</h2>
                <p class="text-muted">View all the scratch cards and PINs you've purchased</p>
            </div>
            <div id="user-cards-container">
                <div class="loading"><div class="spinner"></div></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- How It Works -->
        <div class="section">
            <div class="section-header">
                <h2 class="text-white"><i class="fas fa-question-circle"></i> How It Works</h2>
            </div>
            <div class="cards-grid" style="max-width: 900px; margin: 0 auto;">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">1. Select Card</h4>
                        <p>Choose WAEC or NECO result checker</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">2. Make Payment</h4>
                        <p>Pay securely with Paystack</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="text-white">3. Get Your PIN Instantly</h4>
                        <p>Receive your PIN and serial number immediately</p>
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

<!-- Purchase Modal -->
<div class="zonatech-modal" id="purchaseModal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content glass-card" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="text-white"><i class="fas fa-shopping-cart"></i> <span id="modal-card-title">Purchase Card</span></h3>
            <button class="modal-close" onclick="closePurchaseModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="purchase-step-1">
                <div style="text-align: center; padding: 1.5rem;">
                    <div id="modal-card-icon" style="width: 80px; height: 80px; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 2rem;"></div>
                    <h4 class="text-white" id="modal-card-name"></h4>
                    <p class="text-muted" id="modal-card-desc"></p>
                    <p class="text-purple" style="font-size: 2rem; font-weight: 700; margin: 1rem 0;">
                        ₦<span id="modal-card-price"></span>
                    </p>
                </div>
                <button class="btn btn-primary btn-lg" style="width: 100%;" onclick="proceedToPayment()">
                    <i class="fas fa-credit-card"></i> Proceed to Payment
                </button>
            </div>
            <div id="purchase-step-2" style="display: none;">
                <div style="text-align: center; padding: 2rem;">
                    <div class="spinner" style="margin: 0 auto;"></div>
                    <p class="text-muted" style="margin-top: 1rem;">Processing your purchase...</p>
                </div>
            </div>
            <div id="purchase-step-3" style="display: none;">
                <div style="text-align: center; padding: 1.5rem;">
                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; background: rgba(34, 197, 94, 0.2); border-radius: 50%; font-size: 2.5rem; color: #22c55e;">
                        <i class="fas fa-check"></i>
                    </div>
                    <h3 class="text-white" style="margin-bottom: 1rem;">Purchase Successful!</h3>
                    
                    <div style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 15px; padding: 1.5rem; margin: 1rem 0; text-align: left;">
                        <div style="margin-bottom: 1rem;">
                            <span style="color: #a1a1aa; font-size: 0.8rem; text-transform: uppercase;">Card Type</span>
                            <div id="result-card-type" style="color: #fff; font-weight: 600;"></div>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <span style="color: #a1a1aa; font-size: 0.8rem; text-transform: uppercase;">PIN</span>
                            <div id="result-pin" style="color: #22c55e; font-size: 1.5rem; font-weight: 700; font-family: monospace; letter-spacing: 2px;"></div>
                        </div>
                        <div id="result-serial-container">
                            <span style="color: #a1a1aa; font-size: 0.8rem; text-transform: uppercase;">Serial Number</span>
                            <div id="result-serial" style="color: #fff; font-family: monospace;"></div>
                        </div>
                    </div>
                    
                    <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 1rem; margin: 1rem 0;">
                        <p style="color: #f59e0b; font-size: 0.85rem; margin: 0;">
                            <i class="fas fa-info-circle"></i> Your PIN has been sent to your email. Keep it safe!
                        </p>
                    </div>
                    
                    <button class="btn btn-secondary" onclick="closePurchaseModal()">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.zonatech-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.zonatech-modal .modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}
.zonatech-modal .modal-content {
    position: relative;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}
.zonatech-modal .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 1rem;
}
.zonatech-modal .modal-close {
    background: none;
    border: none;
    color: #a1a1aa;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.2s;
}
.zonatech-modal .modal-close:hover {
    color: #fff;
}
</style>

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
    
    // Load user's purchased cards
    <?php if (!$is_guest): ?>
    loadUserCards();
    <?php endif; ?>
    
    function loadUserCards() {
        $.ajax({
            url: zonatech_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zonatech_get_user_cards',
                nonce: zonatech_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.cards) {
                    displayUserCards(response.data.cards);
                } else {
                    $('#user-cards-container').html('<p class="text-muted text-center">No cards purchased yet.</p>');
                }
            },
            error: function() {
                $('#user-cards-container').html('<p class="text-muted text-center">Failed to load cards.</p>');
            }
        });
    }
    
    function displayUserCards(cards) {
        if (cards.length === 0) {
            $('#user-cards-container').html('<p class="text-muted text-center">No cards purchased yet. Buy your first scratch card above!</p>');
            return;
        }
        
        var html = '<div class="cards-grid">';
        cards.forEach(function(card) {
            var cardColor = card.card_type === 'waec' ? '#22c55e' : '#f59e0b';
            html += '<div class="glass-card" style="border-left: 4px solid ' + cardColor + ';">';
            html += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">';
            html += '<span style="padding: 0.25rem 0.75rem; background: ' + cardColor + '20; color: ' + cardColor + '; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">' + card.card_type.toUpperCase() + '</span>';
            html += '<span class="text-muted" style="font-size: 0.8rem;">' + new Date(card.sold_at).toLocaleDateString() + '</span>';
            html += '</div>';
            html += '<div style="margin-bottom: 0.5rem;">';
            html += '<span style="color: #a1a1aa; font-size: 0.75rem;">PIN</span>';
            html += '<div style="color: #22c55e; font-size: 1.25rem; font-weight: 700; font-family: monospace; letter-spacing: 1px;">' + card.pin + '</div>';
            html += '</div>';
            if (card.serial_number) {
                html += '<div>';
                html += '<span style="color: #a1a1aa; font-size: 0.75rem;">Serial</span>';
                html += '<div style="color: #fff; font-family: monospace;">' + card.serial_number + '</div>';
                html += '</div>';
            }
            html += '</div>';
        });
        html += '</div>';
        
        $('#user-cards-container').html(html);
    }
    
    // Buy card button click handlers
    $('.buy-otapay-card-btn').on('click', function() {
        var cardType = $(this).data('card-type');
        var price = $(this).data('price');
        openPurchaseModal(cardType, price);
    });
});

// Card data for modals
var cardData = <?php echo json_encode($otapay_cards); ?>;
var currentCardType = '';
var currentCardPrice = 0;

function openPurchaseModal(cardType, price) {
    currentCardType = cardType;
    currentCardPrice = price;
    
    var card = cardData[cardType];
    
    document.getElementById('modal-card-title').textContent = 'Purchase ' + card.name + ' Card';
    document.getElementById('modal-card-name').textContent = card.full_name;
    document.getElementById('modal-card-desc').textContent = card.description;
    document.getElementById('modal-card-price').textContent = price.toLocaleString();
    document.getElementById('modal-card-icon').style.background = 'linear-gradient(135deg, ' + card.color + '30, ' + card.color + '10)';
    document.getElementById('modal-card-icon').style.color = card.color;
    document.getElementById('modal-card-icon').innerHTML = '<i class="' + card.icon + '"></i>';
    
    // Reset to step 1
    document.getElementById('purchase-step-1').style.display = 'block';
    document.getElementById('purchase-step-2').style.display = 'none';
    document.getElementById('purchase-step-3').style.display = 'none';
    
    document.getElementById('purchaseModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closePurchaseModal() {
    document.getElementById('purchaseModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal on overlay click
document.querySelector('#purchaseModal .modal-overlay')?.addEventListener('click', closePurchaseModal);

function proceedToPayment() {
    // Initialize Paystack payment
    var handler = PaystackPop.setup({
        key: zonatech_ajax.paystack_public_key,
        email: zonatech_ajax.user_email,
        amount: currentCardPrice * 100, // Amount in kobo
        currency: 'NGN',
        ref: 'ZT-CARD-' + Date.now() + '-' + Math.floor(Math.random() * 1000),
        metadata: {
            custom_fields: [
                {
                    display_name: "Card Type",
                    variable_name: "card_type",
                    value: currentCardType
                },
                {
                    display_name: "Payment Type",
                    variable_name: "payment_type",
                    value: "otapay_card"
                }
            ]
        },
        callback: function(response) {
            // Payment successful - now purchase the card from OtaPay
            purchaseCardFromOtaPay(response.reference);
        },
        onClose: function() {
            // User closed popup
        }
    });
    
    handler.openIframe();
}

function purchaseCardFromOtaPay(paymentReference) {
    // Show processing state
    document.getElementById('purchase-step-1').style.display = 'none';
    document.getElementById('purchase-step-2').style.display = 'block';
    
    jQuery.ajax({
        url: zonatech_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'zonatech_purchase_otapay_card',
            nonce: zonatech_ajax.nonce,
            card_type: currentCardType,
            reference: paymentReference
        },
        success: function(response) {
            document.getElementById('purchase-step-2').style.display = 'none';
            
            if (response.success) {
                // Show success with PIN
                document.getElementById('result-card-type').textContent = response.data.card_type + ' Result Checker';
                document.getElementById('result-pin').textContent = response.data.pin;
                
                if (response.data.serial) {
                    document.getElementById('result-serial').textContent = response.data.serial;
                    document.getElementById('result-serial-container').style.display = 'block';
                } else {
                    document.getElementById('result-serial-container').style.display = 'none';
                }
                
                document.getElementById('purchase-step-3').style.display = 'block';
                
                // Reload user cards list
                if (typeof loadUserCards === 'function') {
                    setTimeout(loadUserCards, 1000);
                } else {
                    setTimeout(function() { location.reload(); }, 2000);
                }
            } else {
                alert('Error: ' + (response.data?.message || 'Failed to purchase card. Please contact support.'));
                document.getElementById('purchase-step-1').style.display = 'block';
            }
        },
        error: function() {
            document.getElementById('purchase-step-2').style.display = 'none';
            document.getElementById('purchase-step-1').style.display = 'block';
            alert('Connection error. Please try again.');
        }
    });
}
</script>
