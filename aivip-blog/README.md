# AIVIP Blog

A modern blog platform built with PHP and MySQL.

## Project Structure

```
aivip-blog/
├── admin/                    # Admin panel
│   ├── assets/              # Admin assets (CSS, JS, images)
│   ├── js/                  # Admin JavaScript files
│   ├── pages/               # Admin pages
│   ├── index.php            # Admin dashboard
│   ├── login.php            # Admin login
│   ├── logout.php           # Admin logout
│   ├── forgot-password.php  # Password recovery
│   └── reset-password.php   # Password reset
├── api/                     # API endpoints
│   ├── auth/               # Authentication endpoints
│   ├── categories/         # Category endpoints
│   ├── contact/            # Contact form endpoints
│   ├── digital_analysis/   # Digital analysis endpoints
│   ├── media/              # Media endpoints
│   ├── newsletter/         # Newsletter endpoints
│   ├── posts/              # Post endpoints
│   └── users/              # User endpoints
├── config/                  # Configuration files
│   ├── database.php        # Database configuration
│   ├── mail.php            # Mail configuration
│   └── schema.sql          # Database schema
├── includes/                # Core includes
│   └── Mail.php            # Mail class
├── lib/                    # Third-party libraries
│   └── PHPMailer/         # PHPMailer library
├── uploads/                # Uploaded files
│   ├── images/            # Uploaded images
│   └── temp/              # Temporary uploads
└── README.md              # Project documentation
```

## Features

### Admin Panel
- User authentication and authorization
- Password recovery and reset
- Post management (CRUD)
- Category management
- Media library with image upload
- Newsletter subscription management
- Contact form submissions
- User management
- Site settings

### API Endpoints
- Authentication
  - Login
  - Logout
  - Password reset
  - Password recovery
- Posts
  - List posts
  - Create post
  - Update post
  - Delete post
  - Update post status
- Categories
  - List categories
  - Create category
  - Update category
  - Delete category
- Media
  - Upload media
  - List media
  - Delete media
- Newsletter
  - Subscribe
  - Unsubscribe
  - List subscribers
- Users
  - Create user
  - Update user
  - Delete user
  - Reset password
- Contact
  - Submit contact form
  - List submissions
- Digital Analysis
  - Various analysis endpoints

## API Documentation

### Authentication

#### Login
```
POST /api/auth/login.php
```
Request body:
```json
{
    "username": "string",
    "password": "string"
}
```
Response:
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user_id": "integer",
        "username": "string",
        "role": "string"
    }
}
```

#### Logout
```
POST /api/auth/logout.php
```
Response:
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

#### Password Recovery
```
POST /api/auth/forgot-password.php
```
Request body:
```json
{
    "email": "string"
}
```

#### Password Reset
```
POST /api/auth/reset-password.php
```
Request body:
```json
{
    "token": "string",
    "password": "string"
}
```

### Posts

#### List Posts
```
GET /api/posts/list.php
```
Query Parameters:
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10, max: 50)
- `status` (optional): Filter by status (draft/published/archived)
- `search` (optional): Search in title, content, and excerpt
- `category` (optional): Filter by category ID
- `author` (optional): Filter by author ID
- `sort` (optional): Sort field (created_at/updated_at/published_at/title)
- `order` (optional): Sort order (ASC/DESC)

#### Create Post
```
POST /api/posts/create.php
```
Request body:
```json
{
    "title": "string (required)",
    "content": "string (required)",
    "excerpt": "string (optional)",
    "featured_image": "string (optional)",
    "status": "string (optional: draft/published/archived)",
    "categories": ["integer"] (optional)
}
```

#### Update Post
```
POST /api/posts/update.php
```
Request body:
```json
{
    "id": "integer (required)",
    "title": "string (required)",
    "content": "string (required)",
    "excerpt": "string (optional)",
    "featured_image": "string (optional)",
    "status": "string (optional: draft/published/archived)",
    "categories": ["integer"] (optional)
}
```

#### Delete Post
```
POST /api/posts/delete.php
```
Request body:
```json
{
    "id": "integer (required)"
}
```

### Media

#### Upload Media
```
POST /api/media/upload.php
```
- Method: POST
- Content-Type: multipart/form-data
- Field name: "image"
- Supported types: JPG, PNG, GIF
- Max size: 5MB

#### List Media
```
GET /api/media/list.php
```
Query Parameters:
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10, max: 50)
- `search` (optional): Search in filename
- `sort` (optional): Sort field (created_at/filename)
- `order` (optional): Sort order (ASC/DESC)

#### Delete Media
```
POST /api/media/delete.php
```
Request body:
```json
{
    "id": "integer (required)"
}
```

### Newsletter

#### Subscribe
```
POST /api/newsletter/subscribe.php
```
Request body:
```json
{
    "nome_cognome": "string (required)",
    "email": "string (required)",
    "preferenza_invio": "string (required)",
    "privacy": "boolean (required)",
    "url_invio": "string (required)"
}
```

#### List Subscribers
```
GET /api/newsletter/list.php
```
Query Parameters:
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10, max: 50)
- `search` (optional): Search in name and email
- `sort` (optional): Sort field (created_at/email/nome_cognome)
- `order` (optional): Sort order (ASC/DESC)

### Contact Form

#### Submit Contact Form
```
POST /api/contact/submit.php
```
Request body:
```json
{
    "nome_cognome": "string (required)",
    "email": "string (required)",
    "telefono": "string (optional)",
    "ragione_sociale": "string (optional)",
    "messaggio": "string (required)",
    "privacy": "boolean (required)",
    "url_invio": "string (required)"
}
```

#### List Submissions
```
GET /api/contact/list.php
```
Query Parameters:
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10, max: 50)
- `search` (optional): Search in name, email, and company
- `sort` (optional): Sort field (created_at/email/nome_cognome/ragione_sociale)
- `order` (optional): Sort order (ASC/DESC)

### Digital Analysis

#### Submit Analysis Request
```
POST /api/digital_analysis/submit.php
```
Request body:
```json
{
    "website": "string (required)",
    "email": "string (required)",
    "privacy": "boolean (required)"
}
```
Response:
```json
{
    "success": true,
    "message": "Analysis request submitted successfully",
    "data": {
        "id": "integer",
        "website": "string",
        "email": "string",
        "privacy": "boolean",
        "url_invio": "string",
        "timestamp": "string"
    }
}
```
Note: The `url_invio` field is automatically populated from the request's referrer header.

#### Update Analysis Status
```
POST /api/digital_analysis/update.php
```
Request body:
```json
{
    "id": "integer (required)",
    "action": "string (required: check|reset)"
}
```
Response:
```json
{
    "success": true,
    "message": "Status updated successfully"
}
```

## User Guide

### Admin Access
1. Visit `/admin` in your browser
2. Login with your admin credentials
3. If you've forgotten your password:
   - Click "Forgot Password"
   - Enter your email address
   - Check your email for password reset instructions
   - Follow the link to reset your password

### Dashboard
The dashboard provides an overview of:
- Total posts count (published, draft, archived)
- Recent posts with quick actions
- Post statistics by status
- Quick access to main features

### Post Management
1. Creating a New Post:
   - Click "New Post" in the sidebar
   - Enter post title and content
   - Add categories
   - Upload a featured image
   - Set post status (draft/published/archived)
   - Click "Save"

2. Managing Posts:
   - View all posts in a paginated list
   - Search posts by title or content
   - Filter by status or category
   - Sort by various columns
   - Edit existing posts
   - Delete posts
   - View post details

### Media Library
1. Uploading Media:
   - Click "Upload Media" button
   - Select files or drag and drop
   - Supported formats: JPG, PNG, GIF
   - Maximum file size: 5MB

2. Managing Media:
   - View all uploaded files
   - Search by filename
   - Sort by date or name
   - Delete files
   - Copy file URLs

### Newsletter Management
1. Viewing Subscribers:
   - Access the Newsletter section
   - View subscriber list
   - Search and filter subscribers
   - Export subscriber data

2. Managing Subscriptions:
   - Add new subscribers
   - Remove subscribers
   - Update subscriber preferences

### User Management
1. Creating Users:
   - Click "New User"
   - Enter user details
   - Set user role (admin/author)
   - Set initial password

2. Managing Users:
   - View user list
   - Edit user details
   - Reset passwords
   - Deactivate/activate accounts

### Contact Form Management
1. Viewing Submissions:
   - Access the Contact section
   - View all submissions
   - Filter by date or status
   - Export submission data

2. Managing Submissions:
   - Mark as read/unread
   - Delete submissions
   - Export data

### Digital Analysis
1. Viewing Analysis Requests:
   - Access the Digital Analysis section
   - View all analysis requests
   - Filter by status
   - View request details

2. Managing Analysis:
   - Mark requests as checked
   - Reset analysis status
   - View website and contact details

## Installation

1. Clone the repository
2. Create a MySQL database
3. Import the database schema from `config/schema.sql`
4. Copy `config/database.php` and update the credentials
5. Copy `config/mail.php` and update the mail settings
6. Set up your web server to point to the project directory
7. Make sure the `uploads` directory and its subdirectories are writable:
   ```bash
   chmod -R 755 uploads
   chmod -R 777 uploads/images
   chmod -R 777 uploads/temp
   ```

## Configuration

### Database
Update `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
```

### Mail
Update `config/mail.php` with your mail server settings:
```php
define('MAIL_HOST', 'your_smtp_host');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your_smtp_username');
define('MAIL_PASSWORD', 'your_smtp_password');
define('MAIL_FROM', 'your_from_email');
define('MAIL_FROM_NAME', 'Your Name');
```

## Security

- Password hashing using PHP's password_hash()
- Prepared statements for all database queries
- Input validation and sanitization
- CSRF protection
- Session management
- Role-based access control
- Secure file upload handling
- XSS prevention
- SQL injection prevention

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 