# TableTap Reservation System

A complete, production-ready MVP restaurant reservation web application built with Core PHP, MySQL, and Bootstrap 5.

## Features

### Customer Features
- **Restaurant Discovery**: Browse and search restaurants
- **Live Availability Check**: Real-time table availability by date, time, and party size
- **Table Reservation**: Easy booking flow with step-by-step interface
- **Menu Browsing**: View full restaurant menu with categories
- **Pre-Ordering**: Add items to cart and attach to reservations
- **Customer Accounts**: Register, login, and manage profile
- **Reservation Management**: View upcoming reservations and history
- **Reservation Cancellation**: Cancel reservations (within policy rules)
- **Guest Booking**: Book without account (with guest details)

### Admin Features
- **Dashboard**: Overview of reservations and statistics
- **Reservation Management**: View, confirm, cancel, and update reservation status
- **Daily Schedule**: View reservations by date
- **Export Functionality**: Export reservations to CSV
- **Status Updates**: Update reservation status (pending, confirmed, seated, completed, cancelled, no-show)

## Technology Stack

- **Backend**: Core PHP (No frameworks)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **UI Framework**: Bootstrap 5
- **Database Access**: PDO with prepared statements
- **Authentication**: Session-based
- **Security**: CSRF protection, password hashing, input sanitization

## Architecture

The application follows a clean MVC architecture:

```
TableBook/
├── app/
│   ├── Core/           # Core framework classes
│   │   ├── Application.php
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   └── Autoloader.php
│   ├── Controllers/    # Request handlers
│   ├── Repositories/   # Data access layer
│   ├── Services/       # Business logic
│   ├── Middleware/     # Authentication & CSRF
│   └── Views/          # PHP templates
├── config/             # Configuration files
├── database/           # SQL schema and seeds
├── public/             # Public assets (CSS, JS, images)
└── index.php           # Front controller
```

### Key Components

- **Front Controller Pattern**: All requests routed through `index.php`
- **Custom Router**: URL routing with parameter extraction
- **Repository Pattern**: Database access abstraction
- **Service Layer**: Business logic separation
- **Middleware**: Authentication and CSRF protection

## Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled (or Nginx)
- XAMPP/WAMP/LAMP stack

### Step 1: Clone/Download Project

Place the project in your web server directory:
- XAMPP: `C:\xampp\htdocs\TableBook`
- WAMP: `C:\wamp\www\TableBook`
- LAMP: `/var/www/html/TableBook`

### Step 2: Database Setup

1. Open phpMyAdmin or MySQL command line
2. Import the database schema:
   ```sql
   mysql -u root -p < database/schema.sql
   ```
   Or use phpMyAdmin to import `database/schema.sql`

3. Import seed data:
   ```sql
   mysql -u root -p tabletap < database/seeds.sql
   ```
   Or use phpMyAdmin to import `database/seeds.sql`

### Step 3: Configuration

Edit `config/config.php` and update database credentials if needed:

```php
'database' => [
    'host' => 'localhost',
    'dbname' => 'tabletap',
    'username' => 'root',
    'password' => '',  // Update if you have a password
    'charset' => 'utf8mb4'
],
```

Update the application URL if needed:
```php
'app' => [
    'url' => 'http://localhost/TableBook',  // Update to match your setup
    // ...
],
```

### Step 4: Web Server Configuration

#### Apache (.htaccess already included)
Ensure mod_rewrite is enabled. The `.htaccess` file is already configured.

#### Nginx Configuration
Add this to your Nginx server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Step 5: Permissions

Ensure the web server has read permissions on all files and write permissions on any directories that need it (if you add file uploads later).

### Step 6: Access the Application

1. **Public Site**: `http://localhost/TableBook`
2. **Admin Panel**: `http://localhost/TableBook/admin/login`

## Default Credentials

### Admin Login
- **Email**: `admin@tabletap.com`
- **Password**: `password`

### Demo Customer Accounts
All demo customers use password: `password`

1. **John Doe**
   - Email: `john@example.com`
   - Password: `password`

2. **Jane Smith**
   - Email: `jane@example.com`
   - Password: `password`

3. **Bob Johnson**
   - Email: `bob@example.com`
   - Password: `password`

## Usage Guide

### For Customers

1. **Browse Restaurants**: Visit the homepage or `/restaurants` to see available restaurants
2. **View Restaurant Details**: Click on a restaurant to see menu, hours, and booking options
3. **Make a Reservation**:
   - Select date, time, and party size
   - Click "Check Availability"
   - Choose an available table
   - Fill in guest details (or auto-filled if logged in)
   - Confirm reservation
4. **Pre-Order Food**: Click the cart icon on menu items to add to cart
5. **Manage Account**: Login to view reservations, history, and update profile

### For Admins

1. **Login**: Go to `/admin/login` and use admin credentials
2. **Dashboard**: View today's reservations and statistics
3. **Manage Reservations**: Go to `/admin/reservations` to view and update reservations
4. **Update Status**: Use the dropdown to change reservation status
5. **Export**: Click "Export CSV" to download reservations for a date

## Database Schema

### Key Tables

- **customers**: Customer account information
- **restaurants**: Restaurant details
- **tables**: Table information (capacity, sections)
- **reservations**: Reservation records
- **menu_items**: Menu items with prices
- **carts**: Shopping carts for pre-orders
- **cart_items**: Items in carts

### Relationships

- Reservations link to customers (nullable for guest bookings)
- Reservations link to tables and restaurants
- Carts can be attached to reservations
- Menu items belong to categories and restaurants

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with bcrypt
- **CSRF Protection**: All forms include CSRF tokens
- **Prepared Statements**: All database queries use PDO prepared statements
- **Input Sanitization**: Output is escaped using `htmlspecialchars()`
- **Session Security**: HTTP-only cookies, secure session configuration
- **SQL Injection Prevention**: PDO with parameterized queries
- **XSS Prevention**: Output escaping

## Configuration Options

Edit `config/config.php` to customize:

- **Reservation Duration**: Default 90 minutes
- **Reservation Buffer**: 10 minutes between reservations
- **Cancellation Cutoff**: 2 hours before reservation
- **Password Minimum Length**: 8 characters

## API Endpoints (AJAX)

- `GET /availability` - Check table availability
- `POST /cart/add` - Add item to cart
- `POST /cart/update` - Update cart item quantity
- `POST /cart/attach` - Attach cart to reservation
- `POST /admin/reservations/{id}/status` - Update reservation status

## Troubleshooting

### Common Issues

1. **404 Errors**: Ensure mod_rewrite is enabled and `.htaccess` is working
2. **Database Connection Error**: Check credentials in `config/config.php`
3. **Session Issues**: Ensure PHP sessions are enabled and writable
4. **CSRF Token Errors**: Clear browser cookies and try again

### Debug Mode

To enable error display, check `index.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Disable in production!**

## File Structure

```
TableBook/
├── .htaccess              # URL rewriting rules
├── index.php              # Front controller
├── README.md              # This file
├── app/
│   ├── Core/             # Framework core
│   ├── Controllers/      # Request handlers
│   ├── Repositories/     # Data access
│   ├── Services/         # Business logic
│   ├── Middleware/       # Middleware classes
│   └── Views/            # PHP templates
│       ├── layouts/      # Layout templates
│       ├── public/       # Public pages
│       ├── customer/     # Customer pages
│       ├── admin/        # Admin pages
│       └── errors/       # Error pages
├── config/
│   └── config.php        # Application config
├── database/
│   ├── schema.sql        # Database schema
│   └── seeds.sql         # Seed data
└── public/               # Public assets (create if needed)
```

## Future Enhancements

Potential features for future versions:

- Email notifications
- SMS reminders
- Payment integration
- Multi-restaurant support
- Advanced analytics
- Customer reviews and ratings
- Waitlist functionality
- Recurring reservations
- Table management UI for admins
- Menu management UI for admins

## License

This project is provided as-is for educational and commercial use.

## Support

For issues or questions, please refer to the code comments or documentation.

---

**Built with ❤️ using Core PHP**

