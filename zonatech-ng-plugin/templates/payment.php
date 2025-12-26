<?php
/**
 * Payment Template
 */

if (!defined('ABSPATH')) exit;

// Get payment parameters from URL
$payment_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
$exam_type = isset($_GET['exam_type']) ? sanitize_text_field($_GET['exam_type']) : '';
$subject = isset($_GET['subject']) ? sanitize_text_field($_GET['subject']) : '';
$redirect_url = isset($_GET['redirect']) ? esc_url($_GET['redirect']) : site_url('/zonatech-past-questions/');

// Check if user is logged in
$is_logged_in = is_user_logged_in();
$user = $is_logged_in ? wp_get_current_user() : null;

// Get payment info
$payment_amount = 0;
$payment_item = '';
$meta_data = array();

switch ($payment_type) {
    case 'subject':
        $payment_amount = ZONATECH_SUBJECT_PRICE;
        $payment_item = strtoupper($exam_type) . ' ' . $subject . ' Past Questions';
        $meta_data = array(
            'exam_type' => $exam_type,
            'subject' => $subject
        );
        break;
    case 'nin_slip':
        $payment_amount = ZONATECH_NIN_SLIP_PRICE;
        $payment_item = 'Premium NIN Slip';
        break;
    case 'nin_standard_slip':
        $payment_amount = ZONATECH_NIN_STANDARD_SLIP_PRICE;
        $payment_item = 'Standard NIN Slip';
        break;
    case 'scratch_card':
        $payment_amount = ZONATECH_SCRATCH_CARD_PRICE;
        $card_type = isset($_GET['card_type']) ? sanitize_text_field($_GET['card_type']) : '';
        $payment_item = strtoupper($card_type) . ' Scratch Card';
        $meta_data = array('card_type' => $card_type);
        break;
}
?>

<div class="zonatech-container">
    <div class="zonatech-wrapper">
        <!-- Header -->
        <div class="zonatech-header glass-effect">
            <div class="zonatech-logo">
                <a href="<?php echo site_url(); ?>">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </a>
            </div>
            <nav class="zonatech-nav">
                <a href="<?php echo site_url(); ?>"><i class="fas fa-home"></i> Home</a>
                <a href="<?php echo site_url('/zonatech-past-questions/'); ?>"><i class="fas fa-book-open"></i> Past Questions</a>
                <?php if ($is_logged_in): ?>
                    <a href="<?php echo site_url('/zonatech-dashboard/'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php endif; ?>
            </nav>
        </div>

        <div class="glass-card" style="max-width: 550px; margin: 3rem auto; padding: 2rem;">
            <?php if (!$is_logged_in): ?>
                <!-- Not logged in -->
                <div style="text-align: center;">
                    <i class="fas fa-user-lock" style="font-size: 3rem; color: var(--zona-purple); margin-bottom: 1rem;"></i>
                    <h2 class="text-white">Login Required</h2>
                    <p class="text-muted">Please login to complete your purchase.</p>
                    <div class="mt-2">
                        <a href="<?php echo site_url('/zonatech-login/?redirect=' . urlencode($_SERVER['REQUEST_URI'])); ?>" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo site_url('/zonatech-register/'); ?>" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </div>
                </div>
            <?php elseif (empty($payment_type) || $payment_amount <= 0): ?>
                <!-- Invalid payment -->
                <div style="text-align: center;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--zona-warning); margin-bottom: 1rem;"></i>
                    <h2 class="text-white">Invalid Payment Request</h2>
                    <p class="text-muted">The payment request is invalid or incomplete.</p>
                    <a href="<?php echo site_url('/zonatech-past-questions/'); ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-arrow-left"></i> Browse Past Questions
                    </a>
                </div>
            <?php elseif (empty(ZONATECH_PAYSTACK_PUBLIC_KEY)): ?>
                <!-- Paystack not configured -->
                <div style="text-align: center;">
                    <i class="fas fa-cog" style="font-size: 3rem; color: var(--zona-warning); margin-bottom: 1rem;"></i>
                    <h2 class="text-white">Payment Not Available</h2>
                    <p class="text-muted">Payment system is not configured. Please contact support at <?php echo esc_html(ZONATECH_SUPPORT_EMAIL); ?></p>
                    <a href="mailto:<?php echo esc_attr(ZONATECH_SUPPORT_EMAIL); ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-envelope"></i> Contact Support
                    </a>
                </div>
            <?php else: ?>
                <!-- Payment form -->
                <div style="text-align: center;">
                    <i class="fas fa-credit-card" style="font-size: 3rem; color: var(--zona-purple); margin-bottom: 1rem;"></i>
                    <h2 class="text-white"><i class="fas fa-shopping-cart"></i> Complete Payment</h2>
                    
                    <div class="glass-effect" style="padding: 1.5rem; border-radius: 15px; margin: 1.5rem 0;">
                        <h3 class="text-white" style="margin-bottom: 0.5rem;"><?php echo esc_html($payment_item); ?></h3>
                        <p class="text-muted" style="margin-bottom: 1rem;"><?php echo esc_html($user->user_email); ?></p>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--zona-purple);">
                            ₦<?php echo number_format($payment_amount); ?>
                        </div>
                    </div>
                    
                    <button id="pay-now-btn" class="btn btn-primary btn-lg" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                        <i class="fas fa-lock"></i> Pay ₦<?php echo number_format($payment_amount); ?> Securely
                    </button>
                    
                    <p class="text-muted mt-2" style="font-size: 0.85rem;">
                        <i class="fas fa-shield-alt"></i> Secured by Paystack
                    </p>
                    
                    <a href="<?php echo esc_url($redirect_url); ?>" class="btn btn-ghost mt-2">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                </div>
                
                <script>
                jQuery(document).ready(function($) {
                    var paymentInitialized = false;
                    
                    $('#pay-now-btn').on('click', function() {
                        if (paymentInitialized) return;
                        
                        var $btn = $(this);
                        var originalText = $btn.html();
                        $btn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
                        paymentInitialized = true;
                        
                        // Initialize payment via AJAX
                        $.ajax({
                            url: zonatech_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'zonatech_initialize_payment',
                                nonce: zonatech_ajax.nonce,
                                payment_type: '<?php echo esc_js($payment_type); ?>',
                                amount: <?php echo intval($payment_amount); ?>,
                                meta_data: JSON.stringify(<?php echo wp_json_encode($meta_data); ?>)
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Open Paystack popup
                                    var handler = PaystackPop.setup({
                                        key: response.data.public_key,
                                        email: response.data.email,
                                        amount: response.data.amount,
                                        currency: response.data.currency,
                                        ref: response.data.reference,
                                        metadata: response.data.metadata,
                                        onClose: function() {
                                            $btn.html(originalText).prop('disabled', false);
                                            paymentInitialized = false;
                                            if (typeof ZonaTechNotify !== 'undefined') {
                                                ZonaTechNotify.show('Payment cancelled.', 'info');
                                            }
                                        },
                                        callback: function(paymentResponse) {
                                            // Verify payment
                                            $btn.html('<i class="fas fa-spinner fa-spin"></i> Verifying...').prop('disabled', true);
                                            
                                            $.ajax({
                                                url: zonatech_ajax.ajax_url,
                                                type: 'POST',
                                                data: {
                                                    action: 'zonatech_verify_payment',
                                                    nonce: zonatech_ajax.nonce,
                                                    reference: paymentResponse.reference
                                                },
                                                success: function(verifyResponse) {
                                                    if (verifyResponse.success) {
                                                        if (typeof ZonaTechNotify !== 'undefined') {
                                                            ZonaTechNotify.show('Payment successful! Redirecting...', 'success');
                                                        } else {
                                                            alert('Payment successful!');
                                                        }
                                                        
                                                        // Redirect back to questions page
                                                        setTimeout(function() {
                                                            window.location.href = '<?php echo esc_js($redirect_url); ?>?payment=success&exam=<?php echo esc_js($exam_type); ?>&subject=<?php echo esc_js(urlencode($subject)); ?>';
                                                        }, 1500);
                                                    } else {
                                                        $btn.html(originalText).prop('disabled', false);
                                                        paymentInitialized = false;
                                                        if (typeof ZonaTechNotify !== 'undefined') {
                                                            ZonaTechNotify.show(verifyResponse.data.message || 'Payment verification failed.', 'error');
                                                        } else {
                                                            alert(verifyResponse.data.message || 'Payment verification failed.');
                                                        }
                                                    }
                                                },
                                                error: function() {
                                                    $btn.html(originalText).prop('disabled', false);
                                                    paymentInitialized = false;
                                                    if (typeof ZonaTechNotify !== 'undefined') {
                                                        ZonaTechNotify.show('Verification error. Please contact support.', 'error');
                                                    }
                                                }
                                            });
                                        }
                                    });
                                    
                                    handler.openIframe();
                                } else {
                                    $btn.html(originalText).prop('disabled', false);
                                    paymentInitialized = false;
                                    if (typeof ZonaTechNotify !== 'undefined') {
                                        ZonaTechNotify.show(response.data.message || 'Failed to initialize payment.', 'error');
                                    } else {
                                        alert(response.data.message || 'Failed to initialize payment.');
                                    }
                                }
                            },
                            error: function() {
                                $btn.html(originalText).prop('disabled', false);
                                paymentInitialized = false;
                                if (typeof ZonaTechNotify !== 'undefined') {
                                    ZonaTechNotify.show('Network error. Please try again.', 'error');
                                } else {
                                    alert('Network error. Please try again.');
                                }
                            }
                        });
                    });
                });
                </script>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <footer class="zonatech-footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ZonaTech NG</span>
                </div>
                <p class="footer-copyright">
                    © <?php echo date('Y'); ?> ZonaTech NG. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</div>
