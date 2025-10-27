# Ticket Management System - PHP/Twig Version

A modern, responsive ticket management system built with PHP, Twig templating engine, and Tailwind CSS.

## Features

- 🎨 Beautiful, vibrant UI with purple (#A855F7) and pink (#EC4899) color scheme
- 🎫 Full CRUD operations for tickets
- 👤 Simple authentication system
- 📊 Dashboard with statistics
- 🔍 Search and filter tickets
- 📱 Fully responsive design
- ♿ WCAG Level AA compliant
- 🎯 Status-based color coding (green for open, amber for in-progress, gray for closed)

## Requirements

- PHP 7.4 or higher
- Composer (PHP dependency manager)
- Web server (Apache/Nginx) with mod_rewrite enabled, OR PHP built-in server

## Installation & Setup

### Step 1: Install Dependencies

```bash
# Navigate to the project directory
cd php-project

# Install Composer dependencies
composer install
```

### Step 2: Set Permissions

```bash
# Make sure the data directory is writable
chmod 755 data
```

## Running the Application

You have two options to run the application:

### Option 1: Using PHP Built-in Server (Easiest for Development)

```bash
# From the php-project directory, run:
php -S localhost:8000

# The application will be available at:
# http://localhost:8000
```

### Option 2: Using Apache/Nginx

#### For Apache:

1. Make sure mod_rewrite is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

2. Point your virtual host document root to the `php-project` directory

3. Make sure `.htaccess` is working (AllowOverride All)

#### For Nginx:

Add this to your server block configuration:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Usage

### 1. Landing Page
- Visit `http://localhost:8000/` to see the landing page
- Click "Get Started" to create an account or "Sign In" to log in

### 2. Authentication
- **Sign Up**: Create a new account with name, email, and password (min 6 characters)
- **Login**: Sign in with any email and password (min 6 characters)
- Note: This is a demo system, so any credentials will work as long as they meet validation requirements

### 3. Dashboard
- After logging in, you'll see the dashboard with ticket statistics:
  - Total tickets
  - Open tickets
  - In-progress tickets
  - Closed tickets

### 4. Tickets Management
- Click "View All Tickets" or navigate to the Tickets page
- **Create**: Click "New Ticket" button to create a new ticket
- **Edit**: Click the three dots menu on any ticket and select "Edit"
- **Delete**: Click the three dots menu on any ticket and select "Delete"
- **Filter**: Use the status filter buttons (All, Open, In Progress, Closed)
- **Search**: Type in the search box to search by title or assignee

## Project Structure

```
php-project/
├── index.php              # Application entry point & router
├── composer.json          # PHP dependencies
├── .htaccess             # Apache rewrite rules
├── README.md             # This file
├── src/                  # PHP classes
│   ├── Router.php        # Request routing & handling
│   └── TicketManager.php # Ticket CRUD operations
├── templates/            # Twig templates
│   ├── base.twig        # Base layout template
│   ├── landing.twig     # Landing page
│   ├── dashboard.twig   # Dashboard page
│   ├── 404.twig         # 404 error page
│   ├── auth/            # Authentication templates
│   │   ├── login.twig
│   │   └── signup.twig
│   ├── tickets/         # Ticket templates
│   │   └── index.twig
│   └── components/      # Reusable components
│       └── navbar.twig
├── data/                # Data storage (auto-created)
│   └── tickets.json     # JSON file to store tickets
└── vendor/              # Composer dependencies (auto-created)
```

## Features Details

### Color Scheme
- **Primary Color**: Purple (#A855F7 / hsl(280, 90%, 60%))
- **Accent Color**: Pink/Magenta (#EC4899 / hsl(330, 85%, 60%))
- **Neon Green Borders**: All primary action buttons have neon green borders (#10B981)
- **Status Colors**:
  - Open: Green tones
  - In Progress: Amber tones
  - Closed: Gray tones

### Design Elements
- Gradient backgrounds (purple to pink)
- Rounded corners (rounded-xl, rounded-2xl, rounded-3xl)
- Smooth transitions and hover effects
- Shadow effects with glow
- Wave background on landing page
- Responsive grid layouts

### Data Storage
- Tickets are stored in `data/tickets.json` file
- Session-based authentication (no database required)
- Default sample tickets are created on first run

## Development Notes

### Cache
- Twig caching is disabled for development
- To enable caching in production, edit `index.php` and change:
  ```php
  'cache' => __DIR__ . '/cache',
  ```

### Security
- This is a demo application with simplified authentication
- In production, implement proper password hashing, CSRF protection, and use a database
- Validate and sanitize all user inputs
- Use prepared statements for database queries

### Customization
- Edit `templates/base.twig` to modify the global layout
- Color scheme can be adjusted in the Tailwind config in `base.twig`
- Icons are from Lucide (https://lucide.dev)

## Troubleshooting

### Issue: 404 errors for all routes
**Solution**: Make sure mod_rewrite is enabled (Apache) or try_files is configured (Nginx)

### Issue: "vendor/autoload.php not found"
**Solution**: Run `composer install` in the php-project directory

### Issue: "Permission denied" when creating tickets
**Solution**: Make sure the `data` directory exists and is writable:
```bash
mkdir -p data
chmod 755 data
```

### Issue: Blank page or PHP errors
**Solution**: Check PHP error logs or enable error display:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## Browser Compatibility

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License

This is a demo project for educational purposes.

## Credits

- **Tailwind CSS**: https://tailwindcss.com
- **Twig**: https://twig.symfony.com
- **Lucide Icons**: https://lucide.dev
