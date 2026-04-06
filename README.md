# Student Clubs Management Platform

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php)
![React](https://img.shields.io/badge/React-18.x-61DAFB?style=flat-square&logo=react)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

**A modern, centralized platform for managing student clubs, events, announcements, and resource reservations within university environments.**

[Features](#features) • [Quick Start](#quick-start) • [API Documentation](#api-documentation) • [Contributing](#contributing)

</div>

---

## Project Overview

### The Problem

Student clubs are vital to university life, but many institutions still rely on fragmented tools:

- WhatsApp groups
- Google Forms
- Email chains
- Social media pages

This creates:

- Lack of coordination
- Information loss
- Room reservation conflicts
- Poor visibility

### The Solution

**Student Clubs Management Platform** is a centralized digital solution that streamlines:

- Club management and profiles
- Event announcements and activities
- Room and material reservations
- Role-based access control
- Real-time status tracking

---

## Key Features

### Club Management

- Create and manage clubs with profiles
- Update club information, logo, contact, and social media
- Manage club administrators with auto-credentialing
- Activate/deactivate clubs

### Announcements & Events

- Post announcements with image uploads
- Create and manage club activities
- Multi-image galleries for events
- Searchable and paginated content
- Real-time content management

### Room Reservations

- Request room bookings with time-based scheduling
- Automatic conflict detection (prevents double-booking)
- Status tracking (pending → approved/rejected → finished)
- Auto-mark as "finished" when scheduled time passes
- Admin approval workflow

### Material Reservations

- Submit material requests via PDF documents
- Request tracking and status updates
- Admin approval workflow (pending → approved/rejected)
- History and audit trail

### Security & Access Control

- Role-based access control (Super Admin, Club Admin, Users)
- API token authentication (Laravel Sanctum)
- Secure password management
- Email notifications for admins
- Protected sensitive routes

---

## System Architecture

### Architecture Diagram

```
┌─────────────────────────────────────────────────┐
│  Frontend: React.js SPA (Vite + Axios)          │
└────────────────┬──────────────────────────────┘
                 │ HTTP/JSON Requests
┌────────────────▼──────────────────────────────┐
│  Backend: Laravel 10 REST API                  │
│  ├─ 8 Controllers                              │
│  ├─ 6 Eloquent Models                          │
│  ├─ Role-based Middleware                      │
│  └─ Sanctum Authentication                     │
└────────────────┬──────────────────────────────┘
                 │ Database Queries
┌────────────────▼──────────────────────────────┐
│  Database: MySQL 8.0+                          │
│  └─ 7 Main Tables + Pivot Tables               │
└─────────────────────────────────────────────────┘
```

### Backend Architecture

```
Request → Routes → Middleware → Controllers → Models → Database
           ↓
         Response
```

| Layer           | Responsibility                                        |
| --------------- | ----------------------------------------------------- |
| **Routes**      | Define API endpoints with role-based protection       |
| **Middleware**  | Authentication, authorization, CORS                   |
| **Controllers** | Handle requests, validate input, coordinate responses |
| **Models**      | Represent database entities, manage relationships     |
| **Migrations**  | Define database schema with versioning                |

---

## Technology Stack

### Backend

- **Framework**: Laravel 10.x
- **Language**: PHP 8.1+
- **API**: RESTful API
- **ORM**: Eloquent
- **Authentication**: Laravel Sanctum v3.3
- **Build Tool**: Vite

### Frontend

- **Library**: React.js 18.x
- **HTTP Client**: Axios
- **Build Tool**: Vite
- **Styling**: Tailwind CSS

### Database

- **System**: MySQL 8.0+
- **ORM**: Eloquent
- **Migrations**: Laravel Migrations

### Development Tools

- **Version Control**: Git & GitHub
- **Testing**: PHPUnit 10.1
- **Code Quality**: Laravel Pint
- **API Testing**: Postman
- **Documentation**: Figma, StarUML
- **IDE**: Visual Studio Code

---

## User Roles & Permissions

### Visitor (Unauthenticated)

- Browse clubs and their information
- View announcements and activities
- Search events and content

### Club Administrator

- Manage own club profile (name, logo, contact, social media)
- Post announcements with images
- Create activities with multi-image galleries
- Request room and material reservations
- Track reservation status
- View club-specific data

### Super Administrator

- Manage all clubs (create, activate, deactivate, delete)
- Create club administrator accounts
- Manage system rooms/salles
- Approve/reject room and material reservations
- Manage all users on the platform
- View platform-wide statistics
- Override any club-specific permissions

---

## Database Schema

### Core Models & Relationships

```
User
├─ belongsTo: Club
└─ Roles: super_admin, admin_club

Club
├─ hasMany: Users, Announcements, Activities
├─ hasMany: Salle_Reservations, Material_Reservations
└─ Properties: name, logo, description, email, phone, social_media

Announcement
├─ belongsTo: Club
└─ Properties: title, description, image

Activity
├─ belongsTo: Club
└─ Properties: title, description, images (JSON array)

Salle (Room)
├─ hasMany: Salle_Reservations
└─ Properties: name, availability

Salle_Reservation
├─ belongsTo: Club, Salle
├─ Status: pending, approved, rejected, finished
└─ Properties: reason, date, time, status

Material_Reservation
├─ belongsTo: Club
├─ Status: pending, approved, rejected
└─ Properties: club_id, pdf_demande, status
```

---

## Quick Start

### Prerequisites

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Node.js 16+ and npm/yarn
- Git

### Installation

#### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/gestion_clubs.git
cd gestion_clubs
```

#### 2. Install Backend Dependencies

```bash
composer install
```

#### 3. Configure Environment

```bash
# Copy the example environment file to create your local .env file
cp .env.example .env

# Generate application key
php artisan key:generate
```

**Important Notes:**

- `.env.example` is **safe to commit** to the repository (contains no secrets)
- `.env` is **automatically ignored by git** (contains your local secrets)
- Copy `.env.example` and customize it with your local values
- See `.env.example` for helpful comments on each configuration option

#### 4. Configure Database in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_clubs
DB_USERNAME=root
DB_PASSWORD= # Leave empty for local development or add your password
```

#### 5. Configure Mail (Optional - for admin account notifications)

Email is optional. If you want to enable email notifications for admin account creation:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com        # Gmail, Mailtrap, or your provider
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Club Management"
```

For development, consider using [Mailtrap](https://mailtrap.io) for free email testing.

#### 6. Run Migrations & Seeders

```bash
php artisan migrate
php artisan db:seed  # Optional: populate sample data
```

#### 7. Install Frontend Dependencies

```bash
npm install
```

#### 8. Start Development Server

```bash
# Terminal 1: Run Laravel backend
php artisan serve

# Terminal 2: Run Vite frontend build
npm run dev
```

The application will be available at:

- **Backend API**: `http://localhost:8000`
- **Frontend**: `http://localhost:3000` or configured Vite port

---

## API Documentation

### Base URL

```
http://localhost:8000/api
```

### Authentication

All authenticated endpoints require a Bearer token from `/api/login`:

```
Authorization: Bearer {token}
```

### Public Endpoints (No Auth)

```
GET    /clubs                    # List all clubs (paginated)
GET    /clubs/{id}               # Get club details
GET    /announcements            # List announcements (searchable)
GET    /announcements/{id}       # Get announcement details
GET    /activities               # List all activities
GET    /activities/{id}          # Get activity details
```

### Authentication Endpoints

```
POST   /login                    # Login (returns token)
POST   /logout                   # Logout
GET    /me                       # Get authenticated user info
```

### Club Admin Routes (Authenticated)

```
POST   /clubs/update/{id}        # Update own club
POST   /announcements            # Create announcement
POST   /announcements/{id}       # Update announcement
DELETE /announcements/{id}       # Delete announcement
POST   /activities               # Create activity
POST   /activities/{id}          # Update activity
DELETE /activities/{id}          # Delete activity
POST   /salle_reservation        # Request room reservation
GET    /salle_reservation        # List own reservations
POST   /materials                # Request material reservation
GET    /materials                # List own requests
```

### Super Admin Routes (Protected)

```
POST   /clubs                    # Create club
POST   /admins                   # Create club admin
GET    /users                    # List all users (searchable)
POST   /users                    # Create user
DELETE /users/{id}               # Delete user
GET    /salles                   # List rooms
POST   /salles                   # Create room
POST   /salles/{id}/status       # Update room status
POST   /materials/{id}/status    # Approve/reject material request
```

For detailed API documentation, see [API_DOCS.md](docs/API_DOCS.md) (if available)

---

## Project Structure

```
gestion_clubs/
├── app/
│   ├── Models/                 # Eloquent models (User, Club, etc.)
│   ├── Http/
│   │   ├── Controllers/        # API controllers (8 main)
│   │   ├── Middleware/         # Auth & custom middleware
│   │   └── Requests/           # Form request validation
│   ├── Mail/                   # Email templates
│   └── Providers/              # Service providers
│
├── routes/
│   ├── api.php                 # API routes
│   └── web.php                 # Web routes
│
├── database/
│   ├── migrations/             # Database schema
│   ├── seeders/                # Sample data
│   └── factories/              # Model factories for testing
│
├── resources/
│   ├── js/                     # React.js frontend
│   └── views/                  # Blade templates
│
├── storage/
│   ├── app/                    # Application files
│   └── logs/                   # Application logs
│
├── tests/                      # PHPUnit tests
├── .env.example                # Environment template
├── .gitignore                  # Git ignore rules
├── composer.json               # PHP dependencies
├── package.json                # Node dependencies
└── README.md                   # This file
```

---

## Configuration

### Environment Variables

All configuration is done via environment variables in the `.env` file.

**Note:** Use `.env.example` as your reference guide. It contains helpful comments for each variable and is safe to commit to the repository.

Key configuration variables:

```env
# Application Settings
APP_NAME="Student Clubs Management"
APP_ENV=local                  # 'local' for development, 'production' for live
APP_KEY=                       # Generate with: php artisan key:generate (REQUIRED)
APP_DEBUG=true                 # Set to false in production
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_clubs
DB_USERNAME=root
DB_PASSWORD=                   # Leave empty for local, use strong password in production

# Email Configuration (Optional - for admin notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com       # or smtp.mailtrap.io for development
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=noreply@example.com

# API Authentication
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000
```

For a complete list of all available options with descriptions, see the `.env.example` file in the project root.

---

## Testing

### Run All Tests

```bash
# Run PHPUnit tests
php artisan test

# Run with coverage
php artisan test --coverage
```

### Run Specific Test

```bash
php artisan test tests/Feature/AuthControllerTest.php
```

---

## Checklist Before Deployment

- [ ] Copy `.env.example` to `.env` and configure with production values
- [ ] Set `APP_DEBUG=false` in production
- [ ] Generate new `APP_KEY` if deploying fresh: `php artisan key:generate`
- [ ] Set `APP_ENV=production`
- [ ] Configure strong database credentials and backup procedures
- [ ] Set up email service with working SMTP credentials
- [ ] Run `composer install --no-dev` to remove development dependencies
- [ ] Run migrations on production database: `php artisan migrate --force`
- [ ] Set proper file permissions on `storage/` directory (must be writable)
- [ ] Enable HTTPS/SSL certificates (required for production)
- [ ] Configure CORS properly in `.env` for frontend domain
- [ ] Set up regular log rotation and monitoring
- [ ] Configure database connection pooling for high traffic
- [ ] Ensure `.env` file is never committed to git (check `.gitignore`)

---

## Troubleshooting

### Common Issues

**Migrations fail:**

```bash
# Fresh migration (development only!)
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status
```

**CORS errors:**

- Ensure `SANCTUM_STATEFUL_DOMAINS` includes frontend domain
- Check `config/cors.php` for proper wildcard settings

**Token authentication failed:**

- Verify Sanctum is installed: `composer show laravel/sanctum`
- Check `X-CSRF-TOKEN` and `Authorization` headers

**Mail not sending:**

- Verify SMTP credentials in `.env`
- Check `storage/logs/` for error details
- Test with Mailtrap for development

**File upload issues:**

- Ensure `storage/app/public` is writable
- Check max upload size in `php.ini`
- Verify disk configuration in `config/filesystems.php`

---

## Contributing

We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create feature branch**: `git checkout -b feature/amazing-feature`
3. **Commit changes**: `git commit -m 'Add amazing feature'`
4. **Push to branch**: `git push origin feature/amazing-feature`
5. **Open Pull Request**

### Coding Standards

- Follow PSR-12 PHP coding standards
- Use Laravel conventions
- Write meaningful commit messages
- Add tests for new features
- Update documentation

---

## License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

---

## Authors & Contact

**Project**: Student Clubs Management Platform  
**Institution**: ENSET Mohammedia  
**Academic Year**: 2024-2025

### Questions or Issues?

- Email: support@example.com
- GitHub Issues: [Report an issue](https://github.com/yourusername/gestion_clubs/issues)
- Discussions: [Join the discussion](https://github.com/yourusername/gestion_clubs/discussions)

---

## Acknowledgments

- ENSET Mohammedia for the project scope and support
- Laravel community for excellent documentation
- React community for frontend libraries
- All contributors and team members

---

**Made for better university club management**
└── seeders

config/

````

---

# Installation

## 1. Clone the repository

```bash
git clone https://github.com/ELHAIDYousef/gestion_clubs.git
cd gestion_clubs
````

---

## 2. Install dependencies

```bash
composer install
```

---

## 3. Configure environment variables

Copy the `.env` file:

```bash
cp .env.example .env
```

Update database configuration:

```
DB_DATABASE=clubs_db
DB_USERNAME=root
DB_PASSWORD=
```

---

## 4. Generate application key

```bash
php artisan key:generate
```

---

## 5. Run migrations

```bash
php artisan migrate
```

---

## 6. Start the server

```bash
php artisan serve
```

The API will run on:

```
http://127.0.0.1:8000
```

---

# API Testing

You can test the API using:

- Postman
- Insomnia
- Thunder Client (VSCode)

Example endpoint:

```
POST /api/login
POST /api/clubs
GET  /api/events
POST /api/reservations
```

---

# Future Improvements

Planned improvements include:

- Mobile application
- Real-time notifications
- Advanced analytics dashboard
- Event registration system
- Integration with university systems

---

# Project Team

- AIT SAIDOU-ALI Saad
- BAROU Charaf
- BEN TALEB Ilyasse
- ELGHOUALI Zakariae
- ELHAID Yousef

Supervisor:

**Dr. Soufiane Hamida**

ENSET Mohammedia — 2024/2025
