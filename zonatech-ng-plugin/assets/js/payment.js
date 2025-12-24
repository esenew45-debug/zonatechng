/**
 * ZonaTech NG - Payment JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        ZonaTechPayment.init();
    });
    
    const ZonaTechPayment = {
        init: function() {
            this.initPaymentButtons();
            this.initNINService();
            this.initScratchCards();
        },
        
        // Initialize payment buttons
        initPaymentButtons: function() {
            const self = this;
            
            // Buy subject access
            $(document).on('click', '#buy-subject-btn', function() {
                const examType = $(this).data('exam');
                const subject = $(this).data('subject');
                
                self.initiatePayment('subject', zonatech_ajax.subject_price, {
                    exam_type: examType,
                    subject: subject
                });
            });
            
            // Buy scratch card
            $(document).on('click', '.buy-scratch-card-btn', function() {
                const cardType = $(this).data('card-type');
                
                self.initiatePayment('scratch_card', zonatech_ajax.scratch_card_price, {
                    card_type: cardType
                });
            });
            
            // Buy NIN slip
            $(document).on('click', '#buy-nin-slip-btn', function() {
                const nin = $(this).data('nin');
                
                self.initiatePayment('nin_slip', zonatech_ajax.nin_price, {
                    nin_number: nin
                });
            });
        },
        
        // Initiate payment
        initiatePayment: function(paymentType, amount, metaData) {
            const self = this;
            
            // Show loading
            ZonaTechNotify.show('Initializing payment...', 'info');
            
            $.ajax({
                url: zonatech_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'zonatech_initialize_payment',
                    nonce: zonatech_ajax.nonce,
                    payment_type: paymentType,
                    amount: amount,
                    meta_data: JSON.stringify(metaData)
                },
                success: function(response) {
                    if (response.success) {
                        self.openPaystack(response.data);
                    } else {
                        ZonaTechNotify.show(response.data.message, 'error');
                    }
                },
                error: function() {
                    ZonaTechNotify.show('Failed to initialize payment. Please try again.', 'error');
                }
            });
        },
        
        // Open Paystack inline
        openPaystack: function(data) {
            const self = this;
            
            const handler = PaystackPop.setup({
                key: data.public_key,
                email: data.email,
                amount: data.amount,
                currency: data.currency,
                ref: data.reference,
                metadata: data.metadata,
                onClose: function() {
                    ZonaTechNotify.show('Payment window closed.', 'info');
                },
                callback: function(response) {
                    self.verifyPayment(response.reference);
                }
            });
            
            handler.openIframe();
        },
        
        // Verify payment
        verifyPayment: function(reference) {
            ZonaTechNotify.show('Verifying payment...', 'info');
            
            $.ajax({
                url: zonatech_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'zonatech_verify_payment',
                    nonce: zonatech_ajax.nonce,
                    reference: reference
                },
                success: function(response) {
                    if (response.success) {
                        ZonaTechNotify.show(response.data.message, 'success');
                        
                        // Refresh page after short delay
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        ZonaTechNotify.show(response.data.message, 'error');
                    }
                },
                error: function() {
                    ZonaTechNotify.show('Failed to verify payment. Please contact support.', 'error');
                }
            });
        },
        
        // NIN Service
        initNINService: function() {
            const self = this;
            
            // Verify NIN
            $('#verify-nin-btn').on('click', function() {
                const nin = $('#nin-input').val().trim();
                
                if (!nin) {
                    ZonaTechNotify.show('Please enter your NIN number.', 'warning');
                    return;
                }
                
                if (!/^\d{11}$/.test(nin)) {
                    ZonaTechNotify.show('NIN must be 11 digits.', 'error');
                    return;
                }
                
                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Verifying...');
                
                $.ajax({
                    url: zonatech_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'zonatech_verify_nin',
                        nonce: zonatech_ajax.nonce,
                        nin: nin
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html(originalText);
                        
                        if (response.success) {
                            self.showNINResult(response.data.data, nin);
                        } else {
                            ZonaTechNotify.show(response.data.message, 'error');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html(originalText);
                        ZonaTechNotify.show('Verification failed. Please try again.', 'error');
                    }
                });
            });
        },
        
        // Show NIN verification result
        showNINResult: function(data, nin) {
            const resultContainer = $('#nin-result');
            
            resultContainer.html(`
                <div class="glass-card" style="margin-top: 1.5rem;">
                    <div class="d-flex align-center gap-2 mb-2">
                        <i class="fas fa-check-circle text-success" style="font-size: 1.5rem;"></i>
                        <h4 style="margin: 0;">NIN Verified</h4>
                    </div>
                    <p><strong>NIN:</strong> ${data.nin}</p>
                    <p><strong>Status:</strong> <span class="badge badge-success">Verified</span></p>
                    <p class="text-muted">${data.note}</p>
                    <div class="mt-2">
                        <p style="font-size: 1.25rem; font-weight: 700; color: var(--zona-purple);">
                            ₦${zonatech_ajax.nin_price.toLocaleString()}
                        </p>
                        <button class="btn btn-primary" id="buy-nin-slip-btn" data-nin="${nin}">
                            <i class="fas fa-download"></i> Download Premium NIN Slip
                        </button>
                    </div>
                </div>
            `).show();
        },
        
        // Scratch Cards
        initScratchCards: function() {
            // Load user cards
            if ($('#user-cards-container').length) {
                this.loadUserCards();
            }
        },
        
        // Load user's purchased cards
        loadUserCards: function() {
            const container = $('#user-cards-container');
            container.html('<div class="loading"><div class="spinner"></div></div>');
            
            $.ajax({
                url: zonatech_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'zonatech_get_user_cards',
                    nonce: zonatech_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.cards.length > 0) {
                        let html = '<div class="cards-grid">';
                        
                        response.data.cards.forEach(function(card) {
                            html += `
                                <div class="glass-card">
                                    <div class="d-flex justify-between align-center mb-2">
                                        <span class="badge badge-primary">${card.card_type.toUpperCase()}</span>
                                        <small class="text-muted">${new Date(card.sold_at).toLocaleDateString()}</small>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">PIN:</small>
                                        <p style="font-family: monospace; font-size: 1.1rem; margin: 0.25rem 0; word-break: break-all;">
                                            ${card.pin}
                                        </p>
                                    </div>
                                    <div>
                                        <small class="text-muted">Serial:</small>
                                        <p style="font-family: monospace; font-size: 0.9rem; margin: 0.25rem 0;">
                                            ${card.serial_number}
                                        </p>
                                    </div>
                                    <button class="btn btn-sm btn-secondary mt-2 copy-pin-btn" data-pin="${card.pin}">
                                        <i class="fas fa-copy"></i> Copy PIN
                                    </button>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        container.html(html);
                        
                        // Copy PIN handler
                        container.find('.copy-pin-btn').on('click', function() {
                            const pin = $(this).data('pin');
                            navigator.clipboard.writeText(pin).then(function() {
                                ZonaTechNotify.show('PIN copied to clipboard!', 'success');
                            });
                        });
                    } else {
                        container.html(`
                            <div class="empty-state">
                                <i class="fas fa-credit-card"></i>
                                <p>You haven't purchased any scratch cards yet.</p>
                            </div>
                        `);
                    }
                },
                error: function() {
                    container.html('<div class="alert alert-error">Failed to load cards. Please refresh.</div>');
                }
            });
        }
    };
    
    window.ZonaTechPayment = ZonaTechPayment;
    
})(jQuery);
