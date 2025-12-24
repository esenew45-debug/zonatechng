/**
 * ZonaTech NG - Main JavaScript
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        ZonaTech.init();
    });
    
    const ZonaTech = {
        init: function() {
            this.initScrollProgress();
            this.initAnimations();
            this.initDigitalClock();
            this.initTabs();
            this.initMobileMenu();
            this.initNotifications();
        },
        
        // Scroll Progress Bar
        initScrollProgress: function() {
            const progressBar = $('<div class="scroll-progress"></div>');
            $('body').prepend(progressBar);
            
            $(window).on('scroll', function() {
                const scrollTop = $(window).scrollTop();
                const docHeight = $(document).height() - $(window).height();
                const scrollPercent = (scrollTop / docHeight) * 100;
                progressBar.css('width', scrollPercent + '%');
            });
        },
        
        // Scroll Animations
        initAnimations: function() {
            const animatedElements = $('.animate-on-scroll');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        $(entry.target).addClass('animated');
                    }
                });
            }, { threshold: 0.1 });
            
            animatedElements.each(function() {
                observer.observe(this);
            });
            
            // Add button animations
            $('.btn').addClass('btn-animated btn-ripple');
        },
        
        // Digital Clock
        initDigitalClock: function() {
            const clockElement = $('#digital-clock');
            if (!clockElement.length) return;
            
            const updateClock = () => {
                const now = new Date();
                let hours = now.getHours();
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                
                hours = hours % 12;
                hours = hours ? hours : 12;
                hours = String(hours).padStart(2, '0');
                
                const options = { weekday: 'short', month: 'short', day: 'numeric' };
                const dateStr = now.toLocaleDateString('en-US', options);
                
                clockElement.find('.clock-time').text(`${hours}:${minutes}:${seconds}`);
                clockElement.find('.clock-date').text(dateStr);
                clockElement.find('.clock-ampm').text(ampm);
            };
            
            updateClock();
            setInterval(updateClock, 1000);
        },
        
        // Tab Navigation
        initTabs: function() {
            $('.tab-btn').on('click', function() {
                const tabId = $(this).data('tab');
                const tabGroup = $(this).closest('.tab-container');
                
                tabGroup.find('.tab-btn').removeClass('active');
                $(this).addClass('active');
                
                tabGroup.find('.tab-content').removeClass('active');
                tabGroup.find('#' + tabId).addClass('active');
            });
        },
        
        // Mobile Menu
        initMobileMenu: function() {
            const toggle = $('.mobile-menu-toggle');
            const sidebar = $('.dashboard-sidebar');
            const overlay = $('.sidebar-overlay');
            
            toggle.on('click', function() {
                sidebar.toggleClass('active');
                overlay.toggleClass('active');
            });
            
            overlay.on('click', function() {
                sidebar.removeClass('active');
                overlay.removeClass('active');
            });
        },
        
        // Notifications
        initNotifications: function() {
            window.ZonaTechNotify = {
                show: function(message, type = 'info', duration = 3000) {
                    const container = $('#zonatech-notifications');
                    if (!container.length) {
                        $('body').append('<div id="zonatech-notifications" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
                    }
                    
                    const notification = $(`
                        <div class="glass-effect notification-enter" style="padding: 1rem; margin-bottom: 0.5rem; border-radius: 0.5rem; max-width: 350px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fas ${this.getIcon(type)}" style="color: ${this.getColor(type)};"></i>
                                <span style="font-size: 0.875rem;">${message}</span>
                            </div>
                        </div>
                    `);
                    
                    $('#zonatech-notifications').append(notification);
                    
                    setTimeout(() => {
                        notification.addClass('notification-exit');
                        setTimeout(() => notification.remove(), 300);
                    }, duration);
                },
                
                getIcon: function(type) {
                    const icons = {
                        success: 'fa-check-circle',
                        error: 'fa-exclamation-circle',
                        warning: 'fa-exclamation-triangle',
                        info: 'fa-info-circle'
                    };
                    return icons[type] || icons.info;
                },
                
                getColor: function(type) {
                    const colors = {
                        success: '#22c55e',
                        error: '#ef4444',
                        warning: '#f59e0b',
                        info: '#3b82f6'
                    };
                    return colors[type] || colors.info;
                }
            };
        },
        
        // AJAX Helper
        ajax: function(action, data, callback) {
            data = data || {};
            data.action = action;
            data.nonce = zonatech_ajax.nonce;
            
            $.ajax({
                url: zonatech_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (callback) callback(response);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    if (callback) callback({ success: false, data: { message: 'Request failed. Please try again.' }});
                }
            });
        },
        
        // Format currency
        formatCurrency: function(amount) {
            return '₦' + parseFloat(amount).toLocaleString('en-NG');
        },
        
        // Format date
        formatDate: function(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-NG', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    };
    
    // Expose globally
    window.ZonaTech = ZonaTech;
    
})(jQuery);
