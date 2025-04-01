# AIVIP Blog Platform

A modern, secure, and feature-rich blog platform with a powerful admin panel.

## Overview

AIVIP Blog is a complete blogging platform that includes:
- Secure user authentication and authorization
- Content management system with categories
- Media management
- User management
- Newsletter subscription system
- Contact form management
- Email notifications
- Responsive admin interface

## Directory Structure

```
aivip-blog/
├── admin/                 # Admin panel files
│   ├── assets/          # CSS, images, and other static assets
│   ├── js/              # JavaScript files
│   ├── pages/           # Admin panel page templates
│   │   ├── subscribers.php    # Newsletter subscribers management
│   │   ├── contact-form.php   # Contact form submissions management
│   │   └── ...               # Other admin pages
│   ├── index.php        # Admin dashboard
│   ├── login.php        # Login page
│   ├── logout.php       # Logout handler
│   ├── forgot-password.php  # Password recovery
│   └── reset-password.php   # Password reset
├── api/                  # API endpoints
│   ├── auth/            # Authentication endpoints
│   ├── posts/           # Post management endpoints
│   ├── users/           # User management endpoints
│   ├── media/           # Media upload endpoints
│   ├── newsletter/      # Newsletter subscription endpoints
│   └── contact/         # Contact form endpoints
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
- Category management
- Featured image support
- Post status management (draft/published/archived)
- Post search and filtering
- Pagination for post lists
- URL-friendly slugs

### Newsletter Management
- Newsletter subscription system
- Subscriber management interface
- Sortable subscriber list with search and pagination
- Privacy consent tracking
- Subscription preferences
- URL tracking for subscriptions

### Contact Form Management
- Contact form submission handling
- Submission management interface
- Sortable submission list with search and pagination
- Privacy consent tracking
- Company information tracking
- URL tracking for submissions

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
- Newsletter communications
- Contact form notifications
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
- slug (VARCHAR, UNIQUE)
- content (TEXT)
- excerpt (TEXT)
- featured_image (VARCHAR)
- author_id (INT)
- status (ENUM: 'draft', 'published', 'archived')
- published_at (DATETIME)
- created_at (DATETIME)
- updated_at (DATETIME)

### categories
- id (INT, AUTO_INCREMENT)
- name (VARCHAR)
- slug (VARCHAR, UNIQUE)
- description (TEXT)
- created_at (DATETIME)
- updated_at (DATETIME)

### post_categories
- post_id (INT)
- category_id (INT)
- created_at (DATETIME)

### media
- id (INT, AUTO_INCREMENT)
- filename (VARCHAR)
- filepath (VARCHAR)
- filetype (VARCHAR)
- filesize (INT)
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

### newsletter
- id (INT, AUTO_INCREMENT)
- nome_cognome (VARCHAR)
- email (VARCHAR, UNIQUE)
- preferenza_invio (VARCHAR)
- url_invio (VARCHAR)
- privacy (TINYINT)
- created_at (DATETIME)

### contact_form
- id (INT, AUTO_INCREMENT)
- nome_cognome (VARCHAR)
- email (VARCHAR)
- telefono (VARCHAR)
- ragione_sociale (VARCHAR)
- messaggio (TEXT)
- privacy (TINYINT)
- url_invio (VARCHAR)
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

### Categories

#### List Categories
```
GET /api/categories/list.php
```
Query Parameters:
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10, max: 50)
- `search` (optional): Search in name
- `sort` (optional): Sort field (name)
- `order` (optional): Sort order (ASC/DESC)

#### Create Category
```
POST /api/categories/create.php
```
Request body:
```json
{
    "name": "string (required)",
    "description": "string (optional)"
}
```

#### Update Category
```
POST /api/categories/update.php
```
Request body:
```json
{
    "id": "integer (required)",
    "name": "string (required)",
    "description": "string (optional)"
}
```

#### Delete Category
```
POST /api/categories/delete.php
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
    "nome_cognome": "John Doe",
    "email": "john@example.com",
    "preferenza_invio": "mensile",
    "privacy": true,
    "url_invio": "http://example.com/newsletter"
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
    "nome_cognome": "John Doe",
    "email": "john@example.com",
    "telefono": "+39 1234567890",
    "ragione_sociale": "Example Ltd.",
    "messaggio": "I'd like to learn more about your services.",
    "privacy": true,
    "url_invio": "http://example.com/contact"
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

#### Change Password
```
POST /api/users/change-password.php
```
Request body:
```json
{
    "current_password": "string (required)",
    "new_password": "string (required)",
    "confirm_password": "string (required)"
}
```

## Default Users

The system comes with two default users:

1. Admin User
   - Username: admin
   - Password: doaoakero
   - Role: admin

2. Author User
   - Username: author
   - Password: doaoakero
   - Role: author

## Security Notes

1. Change default passwords immediately after installation
2. Configure proper file permissions for the uploads directory
3. Use HTTPS in production
4. Regularly update dependencies
5. Monitor error logs
6. Implement rate limiting for API endpoints
7. Use strong passwords
8. Enable CSRF protection
9. Validate all user inputs
10. Sanitize output data

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Testing with Postman

### Setup
1. Download and install [Postman](https://www.postman.com/downloads/)
2. Create a new collection named "AIVIP Blog API"
3. Set up environment variables:
   - `base_url`: Your API base URL (e.g., `http://localhost/aivip-blog/api`)
   - `token`: Will be set after login (optional)

### Authentication
1. Login Request:
   ```
   POST {{base_url}}/auth/login.php
   Body (raw JSON):
   {
       "username": "admin",
       "password": "doaoakero"
   }
   ```

2. Logout Request:
   ```
   POST {{base_url}}/auth/logout.php
   ```

### Posts
1. List Posts:
   ```
   GET {{base_url}}/posts/list.php
   Query Params (optional):
   - page: 1
   - limit: 10
   - status: published
   - search: technology
   - category: 1
   - author: 1
   - sort: published_at
   - order: DESC
   ```

2. Create Post:
   ```
   POST {{base_url}}/posts/create.php
   Body (raw JSON):
   {
       "title": "Test Post",
       "content": "This is a test post content.",
       "excerpt": "A brief summary of the post",
       "status": "draft",
       "categories": [1]
   }
   ```

3. Update Post:
   ```
   POST {{base_url}}/posts/update.php
   Body (raw JSON):
   {
       "id": 1,
       "title": "Updated Post Title",
       "content": "Updated content here.",
       "excerpt": "Updated excerpt",
       "status": "published",
       "categories": [1, 2]
   }
   ```

4. Delete Post:
   ```
   POST {{base_url}}/posts/delete.php
   Body (raw JSON):
   {
       "id": 1
   }
   ```

### Categories
1. List Categories:
   ```
   GET {{base_url}}/categories/list.php
   Query Params (optional):
   - page: 1
   - limit: 10
   - search: technology
   - sort: name
   - order: ASC
   ```

2. Create Category:
   ```
   POST {{base_url}}/categories/create.php
   Body (raw JSON):
   {
       "name": "Technology",
       "description": "Posts about technology and innovation"
   }
   ```

3. Update Category:
   ```
   POST {{base_url}}/categories/update.php
   Body (raw JSON):
   {
       "id": 1,
       "name": "Tech & Innovation",
       "description": "Updated description"
   }
   ```

4. Delete Category:
   ```
   POST {{base_url}}/categories/delete.php
   Body (raw JSON):
   {
       "id": 1
   }
   ```

### Newsletter
1. Subscribe:
   ```
   POST {{base_url}}/newsletter/subscribe.php
   Body (raw JSON):
   {
       "nome_cognome": "John Doe",
       "email": "john@example.com",
       "preferenza_invio": "mensile",
       "privacy": true,
       "url_invio": "http://example.com/newsletter"
   }
   ```

2. List Subscribers:
   ```
   GET {{base_url}}/newsletter/list.php
   Query Params (optional):
   - page: 1
   - limit: 10
   - search: John
   - sort: email
   - order: ASC
   ```

### Contact Form
1. Submit:
   ```
   POST {{base_url}}/contact/submit.php
   Body (raw JSON):
   {
       "nome_cognome": "John Doe",
       "email": "john@example.com",
       "telefono": "+39 1234567890",
       "ragione_sociale": "Example Ltd.",
       "messaggio": "I'd like to learn more about your services.",
       "privacy": true,
       "url_invio": "http://example.com/contact"
   }
   ```

2. List Submissions:
   ```
   GET {{base_url}}/contact/list.php
   Query Params (optional):
   - page: 1
   - limit: 10
   - search: John
   - sort: created_at
   - order: DESC
   ```

### Media
1. Upload Image:
   ```
   POST {{base_url}}/media/upload.php
   Body (form-data):
   - Key: image
   - Value: [Select image file]
   ```

### Users
1. Create User:
   ```
   POST {{base_url}}/users/create.php
   Body (raw JSON):
   {
       "username": "newuser",
       "email": "user@example.com",
       "password": "password123",
       "first_name": "New",
       "last_name": "User",
       "role": "author"
   }
   ```

2. Update User:
   ```
   POST {{base_url}}/users/update.php
   Body (raw JSON):
   {
       "id": 2,
       "username": "updateduser",
       "email": "updated@example.com",
       "first_name": "Updated",
       "last_name": "User",
       "role": "author",
       "status": "active"
   }
   ```

3. Delete User:
   ```
   POST {{base_url}}/users/delete.php
   Body (raw JSON):
   {
       "id": 2
   }
   ```

4. Reset Password:
   ```
   POST {{base_url}}/users/reset-password.php
   Body (raw JSON):
   {
       "id": 2
   }
   ```

5. Change Password:
   ```
   POST {{base_url}}/users/change-password.php
   Body (raw JSON):
   {
       "current_password": "oldpassword123",
       "new_password": "newpassword123",
       "confirm_password": "newpassword123"
   }
   ```

### Common Issues and Solutions

#### Authentication Issues
1. 401 Unauthorized
   - Cause: Not logged in or session expired
   - Solution: Login again or refresh session
   - Example: "Unauthorized: Please login to continue"

2. 403 Forbidden
   - Cause: Insufficient permissions
   - Solution: Use an account with appropriate role
   - Example: "Forbidden: You don't have permission to perform this action"

#### Resource Issues
3. 404 Not Found
   - Cause: Resource doesn't exist
   - Solution: Check resource ID or URL
   - Example: "Not Found: Post with ID 123 does not exist"

4. 422 Validation Error
   - Cause: Invalid input data
   - Solution: Review request body and fix validation errors
   - Example: "Validation Error: Email must be a valid email address"

#### Server Issues
5. 500 Server Error
   - Cause: Internal server error
   - Solution: Check server logs and contact support
   - Example: "Internal Server Error: Database connection failed"

#### File Upload Issues
6. File Upload Errors
   - Cause: Invalid file type or size
   - Solution: Check file requirements
   - Example: "File Upload Error: Maximum file size exceeded (5MB limit)"

#### Database Issues
7. Database Errors
   - Cause: Connection or query issues
   - Solution: Check database configuration
   - Example: "Database Error: Could not connect to database"

#### Session Issues
8. Session Problems
   - Cause: Invalid or expired session
   - Solution: Clear cookies and login again
   - Example: "Session Error: Your session has expired"

#### Security Issues
9. CSRF Errors
   - Cause: Missing or invalid CSRF token
   - Solution: Include valid CSRF token in request
   - Example: "CSRF Error: Invalid security token"

10. Rate Limiting
    - Cause: Too many requests
    - Solution: Wait before making more requests
    - Example: "Rate Limit Exceeded: Please wait 60 seconds before trying again" 