# AIVIP Blog Platform

A modern, secure, and feature-rich blog platform with a powerful admin panel.

## Overview

AIVIP Blog is a complete blogging platform that includes:
- Secure user authentication and authorization
- Content management system with categories
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
- Category management
- Featured image support
- Post status management (draft/published/archived)
- Post search and filtering
- Pagination for post lists
- URL-friendly slugs

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

## Default Users

The system comes with two default users:

1. Admin User
   - Username: admin
   - Password: admin123
   - Role: admin

2. Author User
   - Username: author
   - Password: author123
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

### Tips for Testing
1. Always test with the default admin user first
2. Create test data in a logical order (users → categories → posts)
3. Use environment variables for dynamic values
4. Save successful responses as examples
5. Test error cases by sending invalid data
6. Check response status codes and messages
7. Verify database changes after each request
8. Test pagination with different limit values
9. Test search functionality with various terms
10. Test file upload with different image types and sizes

### Common Issues
1. 401 Unauthorized: Check if you're logged in
2. 403 Forbidden: Verify user permissions
3. 404 Not Found: Check resource IDs
4. 422 Validation Error: Review request body
5. 500 Server Error: Check server logs
6. File Upload Issues: Verify file size and type
7. Database Errors: Check connection settings
8. Session Issues: Clear browser cookies
9. CSRF Errors: Check token implementation
10. Rate Limiting: Wait between requests 