# ZonaTech NG - WordPress Plugin

A comprehensive educational platform WordPress plugin for zonatechng.com featuring past questions, payment integration, NIN services, and scratch card purchases.

## Features

### 🎓 Past Questions
- **JAMB, WAEC, NECO** past questions from 2010 to present
- **15+ subjects** including Mathematics, English, Sciences, Arts, and more
- Advanced **filtering and search** functionality
- **Practice quizzes** with timed tests
- **Instant corrections** with detailed explanations
- Score tracking and grade calculation

### 💳 Payment Integration
- **Paystack integration** for secure payments
- Subject access at **₦5,000** per subject
- Scratch cards at **₦5,000** each
- NIN slip download at **₦2,000**
- Payment history and receipts

### 📱 Progressive Web App (PWA)
- **Offline access** capability
- **Install prompt** for adding to home screen
- Mobile-optimized design
- Fast loading with service worker caching

### 👤 User Management
- Account registration and login
- Password reset functionality
- User dashboard with statistics
- Profile management
- Activity history logging

### 🎫 Scratch Cards & PINs
- WAEC result checker PIN
- NECO result checker PIN
- JAMB e-PIN
- Instant delivery after payment

### 🆔 NIN Service
- NIN number verification
- Premium NIN slip download
- Secure data handling

### 🎨 Design Features
- **Glassmorphism** UI design
- **Reflective black** background with **sparkling purple** and white accents
- Smooth **animations** and transitions
- **Scroll progress** indicator
- **Button animations** with hover effects
- Responsive design for all devices

### 💬 Support
- WhatsApp button (08035328591)
- Email support (henryudonnah524@gmail.com)
- Real-time digital clock on dashboard
- Activity logging for user actions

## Installation

1. Upload the `zonatech-ng-plugin` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure Paystack API keys in **ZonaTech NG → Settings**
4. The plugin will automatically create necessary pages

## Configuration

### Paystack Setup
1. Create a Paystack account at [paystack.com](https://paystack.com)
2. Get your API keys from Settings → API Keys & Webhooks
3. Enter keys in WordPress Admin → ZonaTech NG → Settings
4. Set up webhook URL: `https://yourdomain.com/wp-admin/admin-ajax.php?action=zonatech_paystack_webhook`

### Integration Recommendations

#### NIN Verification
For production NIN verification, integrate with:
- [NIMC API](https://nimc.gov.ng) - Official government API
- [Dojah](https://dojah.io) - Third-party verification
- [Prembly (Identitypass)](https://prembly.com)
- [Youverify](https://youverify.co)

#### Scratch Card Integration
For real scratch cards, partner with:
- [VTpass](https://vtpass.com)
- [Baxi](https://baxi.ng)

## File Structure

```
zonatech-ng-plugin/
├── zonatech-ng.php          # Main plugin file
├── manifest.json            # PWA manifest
├── sw.js                    # Service worker
├── includes/
│   ├── class-database.php      # Database operations
│   ├── class-user-auth.php     # Authentication
│   ├── class-paystack.php      # Payment processing
│   ├── class-past-questions.php # Questions handling
│   ├── class-nin-service.php   # NIN operations
│   ├── class-scratch-cards.php # Cards management
│   ├── class-quiz-system.php   # Quiz functionality
│   ├── class-activity-log.php  # Activity logging
│   ├── class-ajax-handlers.php # AJAX handlers
│   └── class-shortcodes.php    # Shortcode rendering
├── admin/
│   ├── class-admin.php         # Admin panel
│   └── views/                  # Admin templates
├── templates/                  # Frontend templates
├── assets/
│   ├── css/
│   │   ├── main.css           # Main styles
│   │   ├── glassmorphism.css  # Glass effects
│   │   ├── animations.css     # Animations
│   │   ├── dashboard.css      # Dashboard styles
│   │   └── admin.css          # Admin styles
│   ├── js/
│   │   ├── main.js            # Main JavaScript
│   │   ├── auth.js            # Authentication
│   │   ├── quiz.js            # Quiz system
│   │   ├── payment.js         # Payment handling
│   │   ├── pwa.js             # PWA functionality
│   │   └── admin.js           # Admin JavaScript
│   └── images/                # Icons and images
```

## Shortcodes

- `[zonatech_homepage]` - Main homepage
- `[zonatech_login]` - Login form
- `[zonatech_register]` - Registration form
- `[zonatech_dashboard]` - User dashboard
- `[zonatech_past_questions]` - Past questions page
- `[zonatech_nin_service]` - NIN service page
- `[zonatech_scratch_cards]` - Scratch cards page
- `[zonatech_payment]` - Payment page

## Database Tables

The plugin creates the following custom tables:
- `wp_zonatech_questions` - Past questions storage
- `wp_zonatech_purchases` - Payment records
- `wp_zonatech_quiz_results` - Quiz results
- `wp_zonatech_activity_log` - User activities
- `wp_zonatech_scratch_cards` - Card inventory
- `wp_zonatech_nin_requests` - NIN requests
- `wp_zonatech_user_access` - Subject access records
- `wp_zonatech_downloads` - Downloaded documents

## Pricing (Configurable)

| Service | Price |
|---------|-------|
| Subject Access | ₦5,000 |
| Scratch Cards | ₦5,000 |
| NIN Slip | ₦2,000 |

## Support

- **WhatsApp**: 08035328591
- **Email**: henryudonnah524@gmail.com

## License

GPL v2 or later

## Changelog

### 1.0.0
- Initial release
- Past questions for JAMB, WAEC, NECO (2010-present)
- Paystack payment integration
- User authentication system
- Quiz system with corrections
- NIN verification service
- Scratch card purchasing
- PWA functionality
- Glassmorphism design
- Activity logging