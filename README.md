# Darasa - Student Management System

A web-based student management system built to help students and lecturers efficiently manage academic journey, courses, grades.

## Features

- Student or Lecturer registration
- Join classes
- Track assignments
- Attendance monitoring
- User-friendly interface

## Getting Started

### Prerequisites
- PHP 7.4 or higher
- Composer installed
- Git installed
- MySQL/MariaDB database
- Apache/Nginx server

### Installation Steps

1. Clone the repository
```bash
git clone https://github.com/yourusername/darasa.git
cd darasa
```

2. Install dependencies
```bash
composer install
```

3. Configure database settings
- Create a `.env` file in root directory
- Add your database credentials:
```
DB_HOST=localhost
DB_NAME=darasa
DB_USER=your_username
DB_PASS=your_password
```

4. Configure web server
- Point your web server to the `public` directory
- Ensure mod_rewrite is enabled for Apache

5. Run the application
- Access through your web server
- Example: `http://localhost/darasa`

## Technologies

- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Database: MySQL/MariaDB
- Package Manager: Composer

