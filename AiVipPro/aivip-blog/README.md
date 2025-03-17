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

### Posts
- **List Posts**
  - **Endpoint**: `GET /api/posts/list.php`
  - **Description**: Retrieves a list of posts with optional filtering by status and search terms.
  - **Query Parameters**:
    - `page` (optional): Page number (default: 1)
    - `limit` (optional): Items per page (default: 10, max: 50)
    - `status` (optional): Filter by status
    - `search` (optional): Search in title and content

- **Create Post**
  - **Endpoint**: `POST /api/posts/create.php`
  - **Description**: Creates a new blog post.
  - **Request Body**:
    ```json
    {
        "title": "string (required)",
        "content": "string (required)",
        "meta_title": "string (optional)",
        "meta_description": "string (optional)",
        "featured_image": "string (optional)"
    }
    ```

- **Update Post**
  - **Endpoint**: `POST /api/posts/update.php`
  - **Description**: Updates an existing blog post.
  - **Request Body**:
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

- **Delete Post**
  - **Endpoint**: `POST /api/posts/delete.php`
  - **Description**: Deletes a blog post.
  - **Request Body**:
    ```json
    {
        "id": "integer (required)"
    }
    ```

### Categories
- **List Categories**
  - **Endpoint**: `GET /api/categories/list.php`
  - **Description**: Retrieves a list of categories with details.

- **Create Category**
  - **Endpoint**: `POST /api/categories/create.php`
  - **Description**: Creates a new category.
  - **Request Body**:
    ```json
    {
        "name": "string (required)",
        "slug": "string (optional)"
    }
    ```

- **Update Category**
  - **Endpoint**: `POST /api/categories/update.php`
  - **Description**: Updates an existing category.
  - **Request Body**:
    ```json
    {
        "id": "integer (required)",
        "name": "string (required)"
    }
    ```

- **Delete Category**
  - **Endpoint**: `POST /api/categories/delete.php`
  - **Description**: Deletes a category.
  - **Request Body**:
    ```json
    {
        "id": "integer (required)"
    }
    ```

### Users
- **Create User**
  - **Endpoint**: `POST /api/users/create.php`
  - **Description**: Creates a new user.
  - **Request Body**:
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

- **Update User**
  - **Endpoint**: `POST /api/users/update.php`
  - **Description**: Updates an existing user.
  - **Request Body**:
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

- **Delete User**
  - **Endpoint**: `POST /api/users/delete.php`
  - **Description**: Deletes a user.
  - **Request Body**:
    ```json
    {
        "id": "integer (required)"
    }
    ```

- **Reset Password**
  - **Endpoint**: `POST /api/users/reset-password.php`
  - **Description**: Resets a user's password.
  - **Request Body**:
    ```json
    {
        "id": "integer (required)"
    }
    ```

- **Change Password**
  - **Endpoint**: `POST /api/users/change-password.php`
  - **Description**: Allows a user to change their password.
  - **Request Body**:
    ```json
    {
        "current_password": "string (required)",
        "new_password": "string (required)"
    }
    ```

- **Update Profile**
  - **Endpoint**: `POST /api/users/update-profile.php`
  - **Description**: Updates a user's profile information.
  - **Request Body**:
    ```json
    {
        "username": "string (required)",
        "email": "string (required)"
    }
    ```

### Media
- **Upload Media**
  - **Endpoint**: `POST /api/media/upload.php`
  - **Description**: Uploads a media file.
  - **Method**: `POST`
  - **Content-Type**: `multipart/form-data`
  - **Field Name**: `image`
  - **Supported Types**: JPG, PNG, GIF
  - **Max Size**: 5MB

### Authentication
- **Login**
  - **Endpoint**: `POST /api/auth/login.php`
  - **Description**: Authenticates a user and returns a session token.
  - **Request Body**:
    ```json
    {
        "username": "string (required)",
        "password": "string (required)"
    }
    ```

- **Logout**
  - **Endpoint**: `POST /api/auth/logout.php`
  - **Description**: Logs out the current user and destroys the session.

## Authentication
- **Session Management**: The application uses PHP sessions for user authentication. Session tokens are securely stored and managed.
- **Remember Me**: Users can opt to stay logged in using a remember me token, which is securely stored in the database.

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

## Dependencies
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- SSL certificate for secure connections
- PHP extensions:
  - mysqli
  - json
  - fileinfo
  - gd (for image processing)
- PHPMailer for email functionality

## Environment Variables
- `DB_HOST`: Database host
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password
- `SMTP_HOST`: SMTP server host
- `SMTP_USER`: SMTP username
- `SMTP_PASS`: SMTP password
- `SMTP_PORT`: SMTP port
- `SMTP_ENCRYPTION`: SMTP encryption type

## Deployment Instructions
1. Clone the repository.
2. Configure your database connection in `config/database.php`.
3. Import the database schema from `config/schema.sql`.
4. Configure email settings in `config/mail.php`.
5. Set up your web server to point to the project directory.
6. Ensure the `uploads` directory is writable.

## Testing
- Unit tests and integration tests are available in the `tests` directory.
- Run tests using PHPUnit.

## Postman Testing Guide

### Setup
1. Download and install [Postman](https://www.postman.com/downloads/)
2. Create a new collection named "AIVIP Blog API"
3. Set up environment variables:
   - `base_url`: Your API base URL (e.g., `http://localhost/aivip-blog/api`)
   - `token`: Will be automatically set after login

### Authentication Flow
1. **Login**
   - Method: POST
   - URL: `{{base_url}}/auth/login.php`
   - Body (raw JSON):
     ```json
     {
         "username": "admin",
         "password": "your_password"
     }
     ```
   - After successful login, save the session token from cookies

### API Endpoints

#### Posts
1. **List Posts**
   - Method: GET
   - URL: `{{base_url}}/posts/list.php`
   - Query Params (optional):
     - `page`: 1
     - `limit`: 10
     - `status`: published
     - `search`: test

2. **Create Post**
   - Method: POST
   - URL: `{{base_url}}/posts/create.php`
   - Body (raw JSON):
     ```json
     {
         "title": "Test Post",
         "content": "This is a test post content",
         "meta_title": "Test Post Meta Title",
         "meta_description": "Test post meta description",
         "featured_image": "test-image.jpg"
     }
     ```

3. **Update Post**
   - Method: POST
   - URL: `{{base_url}}/posts/update.php`
   - Body (raw JSON):
     ```json
     {
         "id": 1,
         "title": "Updated Test Post",
         "content": "Updated content",
         "status": "published"
     }
     ```

4. **Delete Post**
   - Method: POST
   - URL: `{{base_url}}/posts/delete.php`
   - Body (raw JSON):
     ```json
     {
         "id": 1
     }
     ```

#### Categories
1. **List Categories**
   - Method: GET
   - URL: `{{base_url}}/categories/list.php`

2. **Create Category**
   - Method: POST
   - URL: `{{base_url}}/categories/create.php`
   - Body (raw JSON):
     ```json
     {
         "name": "Test Category",
         "slug": "test-category"
     }
     ```

3. **Update Category**
   - Method: POST
   - URL: `{{base_url}}/categories/update.php`
   - Body (raw JSON):
     ```json
     {
         "id": 1,
         "name": "Updated Category"
     }
     ```

4. **Delete Category**
   - Method: POST
   - URL: `{{base_url}}/categories/delete.php`
   - Body (raw JSON):
     ```json
     {
         "id": 1
     }
     ```

#### Users
1. **Create User**
   - Method: POST
   - URL: `{{base_url}}/users/create.php`
   - Body (raw JSON):
     ```json
     {
         "username": "testuser",
         "email": "test@example.com",
         "password": "password123",
         "first_name": "Test",
         "last_name": "User",
         "role": "author"
     }
     ```

2. **Update User**
   - Method: POST
   - URL: `{{base_url}}/users/update.php`
   - Body (raw JSON):
     ```json
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

3. **Delete User**
   - Method: POST
   - URL: `{{base_url}}/users/delete.php`
   - Body (raw JSON):
     ```json
     {
         "id": 2
     }
     ```

4. **Change Password**
   - Method: POST
   - URL: `{{base_url}}/users/change-password.php`
   - Body (raw JSON):
     ```json
     {
         "current_password": "oldpassword",
         "new_password": "newpassword"
     }
     ```

#### Media
1. **Upload Media**
   - Method: POST
   - URL: `{{base_url}}/media/upload.php`
   - Body (form-data):
     - Key: `image`
     - Type: File
     - Value: Select an image file (JPG, PNG, or GIF)

### Testing Tips
1. Always test the login endpoint first to get a valid session
2. Use environment variables to store and reuse values between requests
3. Test both successful and error cases
4. Verify response status codes and error messages
5. Test with different user roles (admin/author)
6. Test file upload size limits and type restrictions
7. Test pagination and search functionality
8. Verify that unauthorized access is properly blocked

### Common Response Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error

### Troubleshooting
1. If you get a 401 Unauthorized error:
   - Check if you're logged in
   - Verify your session token
   - Try logging in again

2. If you get a 400 Bad Request error:
   - Check if all required fields are provided
   - Verify data types and formats
   - Check for validation errors in the response

3. If you get a 500 Internal Server Error:
   - Check server logs
   - Verify database connection
   - Ensure all required PHP extensions are installed

## Changelog
- **Version 1.0.0**: Initial release of the AIVIP Blog Platform.

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