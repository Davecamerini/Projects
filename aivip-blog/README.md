# AIVIP Blog

A modern blog platform built with PHP and MySQL.

## Project Structure

```
aivip-blog/
├── admin/                    # Admin panel
│   ├── assets/              # Admin assets (CSS, JS, images)
│   ├── includes/            # Admin includes (header, footer, sidebar)
│   └── pages/               # Admin pages
│       ├── categories.php   # Category management
│       ├── dashboard.php    # Admin dashboard
│       ├── media.php        # Media library
│       ├── newsletter.php   # Newsletter management
│       ├── posts.php        # Post management
│       ├── settings.php     # Site settings
│       └── users.php        # User management
├── api/                     # API endpoints
│   ├── auth/               # Authentication endpoints
│   ├── categories/         # Category endpoints
│   ├── contact/            # Contact form endpoints
│   ├── media/              # Media endpoints
│   ├── newsletter/         # Newsletter endpoints
│   ├── posts/              # Post endpoints
│   └── users/              # User endpoints
├── assets/                  # Frontend assets
│   ├── css/                # Frontend styles
│   ├── js/                 # Frontend scripts
│   └── images/             # Frontend images
├── config/                  # Configuration files
│   ├── database.php        # Database configuration
│   └── mail.php            # Mail configuration
├── includes/                # Core includes
│   ├── Database.php        # Database class
│   ├── Mail.php            # Mail class
│   └── functions.php       # Helper functions
├── uploads/                 # Uploaded files
│   └── images/             # Uploaded images
└── index.php               # Frontend entry point
```

## Features

### Admin Panel
- User authentication and authorization
- Post management (CRUD)
- Category management
- Media library with image upload
- Newsletter subscription management
- Contact form submissions
- User management
- Site settings

### Frontend
- Responsive design
- Blog post listing
- Category filtering
- Search functionality
- Newsletter subscription
- Contact form
- Post preview

### API Endpoints
- Authentication
  - Login
  - Logout
  - Password reset
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

### Users

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

## Installation

1. Clone the repository
2. Create a MySQL database
3. Import the database schema from `database.sql`
4. Copy `config/database.example.php` to `config/database.php` and update the credentials
5. Copy `config/mail.example.php` to `config/mail.php` and update the mail settings
6. Set up your web server to point to the project directory
7. Make sure the `uploads` directory is writable

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

## Usage

### Admin Access
1. Visit `/admin` in your browser
2. Login with your admin credentials
3. Default admin credentials:
   - Username: admin
   - Password: admin123

### Creating Posts
1. Navigate to Posts in the admin panel
2. Click "New Post"
3. Fill in the post details
4. Add categories and featured image
5. Set the post status
6. Click "Save"

### Managing Media
1. Navigate to Media in the admin panel
2. Click "Upload" to add new media
3. Use the media library to manage uploaded files
4. Delete unwanted media files

### Newsletter Management
1. Navigate to Newsletter in the admin panel
2. View and manage subscribers
3. Export subscriber list
4. Send newsletter emails

## Security

- Password hashing using PHP's password_hash()
- Prepared statements for all database queries
- Input validation and sanitization
- CSRF protection
- Session management
- Role-based access control

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 