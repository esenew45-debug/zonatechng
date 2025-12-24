/**
 * ZonaTech NG - PWA JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        ZonaTechPWA.init();
    });
    
    const ZonaTechPWA = {
        deferredPrompt: null,
        
        init: function() {
            this.registerServiceWorker();
            this.handleInstallPrompt();
            this.initPromptUI();
        },
        
        // Register Service Worker
        registerServiceWorker: function() {
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register(zonatech_ajax.ajax_url.replace('/wp-admin/admin-ajax.php', '') + '/wp-content/plugins/zonatech-ng-plugin/sw.js')
                        .then(function(registration) {
                            console.log('ServiceWorker registered:', registration.scope);
                        })
                        .catch(function(error) {
                            console.log('ServiceWorker registration failed:', error);
                        });
                });
            }
        },
        
        // Handle install prompt
        handleInstallPrompt: function() {
            const self = this;
            
            window.addEventListener('beforeinstallprompt', function(e) {
                // Prevent Chrome 67+ from showing prompt automatically
                e.preventDefault();
                
                // Store event for later use
                self.deferredPrompt = e;
                
                // Check if user has dismissed before
                if (!localStorage.getItem('zonatech_pwa_dismissed')) {
                    self.showInstallPrompt();
                }
            });
            
            // Track when app is installed
            window.addEventListener('appinstalled', function() {
                self.deferredPrompt = null;
                self.hideInstallPrompt();
                ZonaTechNotify.show('App installed successfully!', 'success');
            });
        },
        
        // Initialize prompt UI
        initPromptUI: function() {
            const self = this;
            
            // Install button
            $('#zonatech-pwa-install').on('click', function() {
                self.installApp();
            });
            
            // Dismiss button
            $('#zonatech-pwa-dismiss').on('click', function() {
                self.hideInstallPrompt();
                localStorage.setItem('zonatech_pwa_dismissed', 'true');
                
                // Reset after 7 days
                setTimeout(function() {
                    localStorage.removeItem('zonatech_pwa_dismissed');
                }, 7 * 24 * 60 * 60 * 1000);
            });
        },
        
        // Show install prompt
        showInstallPrompt: function() {
            $('#zonatech-pwa-prompt').fadeIn(300);
        },
        
        // Hide install prompt
        hideInstallPrompt: function() {
            $('#zonatech-pwa-prompt').fadeOut(300);
        },
        
        // Install app
        installApp: function() {
            const self = this;
            
            if (!this.deferredPrompt) {
                ZonaTechNotify.show('Installation not available. Please try again later.', 'info');
                return;
            }
            
            // Show install prompt
            this.deferredPrompt.prompt();
            
            // Wait for user response
            this.deferredPrompt.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted install');
                } else {
                    console.log('User dismissed install');
                }
                self.deferredPrompt = null;
                self.hideInstallPrompt();
            });
        },
        
        // Check if running as PWA
        isPWA: function() {
            return window.matchMedia('(display-mode: standalone)').matches || 
                   window.navigator.standalone === true;
        }
    };
    
    window.ZonaTechPWA = ZonaTechPWA;
    
})(jQuery);
