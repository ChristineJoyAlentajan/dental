## Dental Clinic Management System - Installation Guide

### System Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/XAMPP with mod_rewrite enabled
- Modern web browser

### Installation Steps

#### 1. Create Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `dental`
3. Go to the **SQL** tab
4. Open and execute the `database.sql` file
5. All tables and sample data will be created automatically

#### 2. Configure Database Connection
The database configuration is in `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'dental');
```

Update these values if your database credentials are different.

#### 3. Access the Application
Open your browser and go to:
```
http://localhost/dental/
```

You will be automatically redirected to the login page.

### Demo Credentials

#### Patient
- Username: `patient1`
- Password: `password`

#### Dentist
- Username: `dentist1`
- Password: `password`

#### Staff
- Username: `staff1`
- Password: `password`

#### Admin
- Username: `admin`
- Password: `password`

### Features

#### For Patients
- Register and manage profile
- Book appointments with dentists
- View appointment history
- Track treatment records
- View notifications
- Manage billing information

#### For Dentists
- Manage personal schedule
- View patient appointments
- Confirm/cancel appointments
- Add treatment records
- Track patient treatment history
- View patient list

#### For Staff
- Manage all appointments
- View all patients
- Process payments
- Generate reports

#### For Admin
- Manage all users (create, activate, deactivate)
- View system reports and analytics
- Monitor appointments
- View revenue reports

### Key Features

1. **Role-Based Access Control**: Different dashboards for different user roles
2. **Appointment Management**: Book, confirm, and manage appointments
3. **Treatment Records**: Detailed treatment tracking with follow-ups
4. **Payment Management**: Track payments and billing
5. **Notifications**: Real-time notifications for users
6. **Reports**: Comprehensive analytics and reporting
7. **Responsive Design**: Works on desktop and mobile devices
8. **Security**: Password hashing and session management

### Files Structure

```
dental/
├── index.php              # Home page
├── login.php              # Login page
├── logout.php             # Logout page  
├── profile.php            # User profile
├── notifications.php      # Notifications
├── database.sql           # Database schema
├── includes/
│   ├── config.php         # Database configuration
│   ├── functions.php      # Helper functions
│   ├── header.php         # Header template
│   └── footer.php         # Footer template
├── css/
│   └── style.css          # Custom styles
├── js/
│   └── script.js          # Custom JavaScript
├── patient/
│   ├── index.php          # Patient dashboard
│   ├── appointments.php   # Manage appointments
│   ├── book-appointment.php # Book appointment
│   └── treatment-history.php # View treatments
├── dentist/
│   ├── index.php          # Dentist dashboard
│   ├── appointments.php   # Manage appointments
│   ├── schedule.php       # Manage schedule
│   ├── patients.php       # View patients
│   └── treatment-records.php # Add treatments
├── staff/
│   ├── index.php          # Staff dashboard
│   ├── appointments.php   # Manage appointments
│   ├── patients.php       # Manage patients
│   └── payments.php       # Process payments
└── admin/
    ├── index.php          # Admin dashboard
    ├── users.php          # Manage users
    ├── appointments.php   # View appointments
    └── reports.php        # View reports
```

### Customization

#### Change Database Credentials
Edit `includes/config.php` to update database connection details.

#### Change Application Title
Edit `includes/config.php`:
```php
define('SITE_NAME', 'Your Clinic Name');
```

#### Modify Styles
Edit `css/style.css` to customize the appearance.

### Troubleshooting

**Q: Getting "Connection failed" error?**
A: Check your database credentials in `includes/config.php` and ensure MySQL is running.

**Q: Cannot login with demo credentials?**
A: Make sure the database was created with the provided `database.sql` file.

**Q: Notifications not working?**
A: Enable PHP's mail extension or configure SMTP settings in `includes/functions.php`.

### Support & Contribution

For issues or improvements, please contact the development team.

### License

This project is provided as-is for educational and commercial use.

---

**Version**: 1.0.0
**Last Updated**: March 15, 2026
