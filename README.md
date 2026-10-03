# PHP Cloud Storage

REST API for a cloud file storage built with PHP and MySQL.

## Features

- User registration and authentication
- Bearer token authorization
- User profile management
- Password reset via email
- File upload and download
- File rename and deletion
- File and directory management
- File sharing between users
- Shared file access control
- Administrator user management
- MySQL database
- Filesystem-based file storage
- 2 GB maximum file size
- REST API architecture

## Technologies

- PHP 7.4+
- MySQL 5.7+
- Apache
- XAMPP
- PDO
- Composer
- REST API

## Project Structure

```text
cloud-storage/
├── config/
├── database/
├── src/
│   ├── Controllers/
│   ├── Core/
│   ├── Repositories/
│   └── Services/
├── storage/
│   └── files/
├── static/
├── index.html
├── index.php
├── .htaccess
├── composer.json
└── README.md
```

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/lThe-onlyl/php-cloud-storage.git
cd php-cloud-storage
```

### 2. Install Composer dependencies

```bash
composer install
```

### 3. Create the database

Create a MySQL database named `cloud_storage`.

Import:

```text
database/init.sql
```

### 4. Configure the database

Edit:

```text
config/database.php
```

and set the MySQL connection parameters.

### 5. Configure PHP

In `php.ini`:

```ini
upload_max_filesize = 2G
post_max_size = 2G
```

Restart Apache after changing the configuration.

### 6. Start the project

Start Apache and MySQL in XAMPP.

Open:

```text
http://localhost/cloud-storage/
```

## API

### Authentication

```text
POST /users/register
POST /users/login
GET  /users/me
GET  /users/logout
```

### Users

```text
GET /users/list
GET /users/get/{id}
PUT /users/update
GET /user/search/{email}
```

### Admin

```text
GET    /admin/users/list
GET    /admin/users/get/{id}
PUT    /admin/users/update/{id}
DELETE /admin/users/delete/{id}
```

### Files

```text
GET    /files/list
GET    /files/get/{id}
POST   /files/add
PUT    /files/rename
DELETE /files/remove/{id}
PUT    /files/move
```

### Directories

```text
POST   /directories/add
GET    /directories/get/{id}
PUT    /directories/rename
DELETE /directories/delete/{id}
```

### Sharing

```text
GET    /files/share/{id}
PUT    /files/share/{id}/{user_id}
DELETE /files/share/{id}/{user_id}
```

## Authorization

Protected endpoints use Bearer token authentication:

```text
Authorization: Bearer <token>
```

## File Storage

Uploaded files are stored in:

```text
storage/files/
```

The original file name and metadata are stored in MySQL.

## License

Educational project.
