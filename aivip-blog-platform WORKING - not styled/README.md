# AIVIP Blog Platform

A modern, secure, and feature-rich blog platform with a powerful admin panel.

## Overview

AIVIP Blog is a complete blogging platform that includes:
- Secure user authentication and authorization
- Content management system
- Media management
- User management
- Email notifications
- Responsive admin interface

## Directory Structure

```
aivip-blog/
├── admin/                 # Admin panel files
│   ├── assets/          # CSS, images, and other static assets
│   ├── js/              # JavaScript files
│   ├── pages/           # Admin panel page templates
│   ├── index.php        # Admin dashboard
│   ├── login.php        # Login page
│   ├── logout.php       # Logout handler
│   ├── forgot-password.php  # Password recovery
│   └── reset-password.php   # Password reset
├── api/                  # API endpoints
│   ├── auth/            # Authentication endpoints
│   ├── posts/           # Post management endpoints
│   ├── users/           # User management endpoints
│   └── media/           # Media upload endpoints
├── config/              # Configuration files
│   ├── database.php     # Database connection settings
│   ├── mail.php         # Email configuration
│   └── schema.sql       # Database schema
├── includes/            # PHP includes
│   └── Mail.php         # Email handling class
├── lib/                 # Third-party libraries
│   └── PHPMailer/       # Email library
└── uploads/             # Media upload directory
```

## Features

### User Management
- User registration and authentication
- Role-based access control (Admin/Author)
- Password reset functionality
- Remember me functionality
- Session management
- User profile management
- Sortable user list with search and pagination

### Content Management
- Create, edit, and delete blog posts
- Rich text editor
- Meta title and description support
- Featured image support
- Post status management (draft/published/archived)
- Post search and filtering
- Pagination for post lists

### Media Management
- Image upload support
- File type validation
- Size restrictions
- Organized media library
- Secure file storage

### Security Features
- Password hashing
- SQL injection prevention
- XSS protection
- CSRF protection
- Input validation
- Secure session handling
- Rate limiting
- File upload restrictions

### Email Features
- Password reset emails
- Account notifications
- HTML email templates
- SMTP configuration

## Installation

1. Clone the repository
2. Configure your database connection in `config/database.php`
3. Import the database schema from `config/schema.sql`
4. Configure email settings in `config/mail.php`
5. Set up your web server to point to the project directory
6. Ensure the `uploads` directory is writable

## Database Schema

The system uses the following tables:

### users
- id (INT, AUTO_INCREMENT)
- username (VARCHAR, UNIQUE)
- email (VARCHAR, UNIQUE)
- password (VARCHAR)
- first_name (VARCHAR)
- last_name (VARCHAR)
- role (ENUM: 'admin', 'author')
- status (ENUM: 'active', 'inactive')
- last_login (DATETIME)
- created_at (DATETIME)
- updated_at (DATETIME)

### posts
- id (INT, AUTO_INCREMENT)
- title (VARCHAR)
- content (TEXT)
- meta_title (VARCHAR)
- meta_description (TEXT)
- featured_image (VARCHAR)
- author_id (INT)
- status (ENUM: 'draft', 'published', 'archived')
- created_at (DATETIME)
- updated_at (DATETIME)

### media
- id (INT, AUTO_INCREMENT)
- filename (VARCHAR)
- path (VARCHAR)
- type (VARCHAR)
- size (INT)
- uploaded_by (INT)
- created_at (DATETIME)

### remember_tokens
- id (INT, AUTO_INCREMENT)
- user_id (INT)
- token (VARCHAR)
- expires_at (DATETIME)
- created_at (DATETIME)

### password_resets
- id (INT, AUTO_INCREMENT)
- user_id (INT)
- token (VARCHAR)
- expires_at (DATETIME)
- used (TINYINT)
- created_at (DATETIME)

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

#### Logout
```
POST /api/auth/logout.php
```

### Posts

#### List Posts
```
GET /api/posts/list.php
```
Query Parameters:
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10, max: 50)
- `status` (optional): Filter by status
- `search` (optional): Search in title and content

#### Create Post
```
POST /api/posts/create.php
```
Request body:
```json
{
    "title": "string (required)",
    "content": "string (required)",
    "meta_title": "string (optional)",
    "meta_description": "string (optional)",
    "featured_image": "string (optional)"
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
    "meta_title": "string (optional)",
    "meta_description": "string (optional)",
    "featured_image": "string (optional)",
    "status": "string (optional)"
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

### Media Upload
```
POST /api/media/upload.php
```
- Method: POST
- Content-Type: multipart/form-data
- Field name: "image"
- Supported types: JPG, PNG, GIF
- Max size: 5MB

### User Management

#### Create User
```
POST /api/users/create.php
```
Request body:
```json
{
    "username": "string (required)",
    "email": "string (required)",
    "password": "string (required)",
    "first_name": "string (required)",
    "last_name": "string (required)",
    "role": "string (required: admin|author)"
}
```

#### Update User
```
POST /api/users/update.php
```
Request body:
```json
{
    "id": "integer (required)",
    "username": "string (required)",
    "email": "string (required)",
    "first_name": "string (required)",
    "last_name": "string (required)",
    "role": "string (required)",
    "status": "string (required)"
}
```

#### Delete User
```
POST /api/users/delete.php
```
Request body:
```json
{
    "id": "integer (required)"
}
```

#### Reset Password
```
POST /api/users/reset-password.php
```
Request body:
```json
{
    "id": "integer (required)"
}
```

## Error Handling

All API endpoints return errors in the following format:
```json
{
    "success": false,
    "message": "Error message description"
}
```

Common error messages:
- "Unauthorized access"
- "Invalid credentials"
- "Resource not found"
- "Permission denied"
- "Invalid input"
- "Database error"

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- SSL certificate for secure connections
- PHP extensions:
  - mysqli
  - json
  - fileinfo
  - gd (for image processing)

## Security Considerations

1. All passwords are hashed using PHP's `password_hash()`
2. SQL queries use prepared statements
3. User input is sanitized and validated
4. Session management follows security best practices
5. Password reset tokens expire after 1 hour
6. Remember me tokens are securely stored
7. File uploads are validated and restricted
8. XSS protection is implemented
9. CSRF protection is in place

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 