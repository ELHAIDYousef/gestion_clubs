
# Student Clubs Management Platform

A modern **web platform designed to centralize and simplify the management of student clubs, events, announcements, and resource reservations within a university environment.**

This project was developed as part of an academic project at **ENSET Mohammedia** to address the lack of a centralized system for managing student club activities.

The platform allows students to explore clubs and events, while club administrators and school administrators manage announcements, reservations, and resources through a structured digital system.

---

# Project Overview

Student clubs play a key role in university life by encouraging collaboration, innovation, and leadership among students.

However, many institutions still rely on **non-centralized tools** such as:

* WhatsApp groups
* Google Forms
* Emails
* Social media pages

These methods create several issues:

* Lack of coordination
* Information loss
* Room reservation conflicts
* Poor visibility of club activities

This platform provides a **centralized digital solution** that improves communication and management across the entire ecosystem.

---

# System Architecture

The system follows a **modern decoupled architecture**:

```
Frontend (React.js SPA)
        │
        │ HTTP Requests (Axios)
        ▼
Backend API (Laravel)
        │
        ▼
Database (MySQL)
```

### Backend Architecture

The backend follows the **MVC architecture pattern**:

```
Controllers → Business Logic → Models → Database
```

Responsibilities:

| Layer       | Role                           |
| ----------- | ------------------------------ |
| Controllers | Handle API requests            |
| Models      | Represent database entities    |
| Services    | Business logic                 |
| Routes      | API endpoints                  |
| Middleware  | Authentication & authorization |

---

# Technology Stack

## Backend

* Laravel
* PHP
* REST API
* Eloquent ORM

## Frontend

* React.js
* Axios
* Tailwind CSS
* JavaScript

## Database

* MySQL

## Tools

* Git & GitHub
* Postman
* Figma
* StarUML
* Visual Studio Code

---

# System Actors

The platform defines **three main roles**.

## 1️ Visitor

Users who are not logged in can:

* Browse clubs
* View announcements
* Explore upcoming events
* Access club profiles

---

## 2️ Club Manager

Each club has a responsible manager who can:

* Manage club profile
* Publish announcements
* Create events
* Request room reservations
* Request equipment reservations
* Track reservation status

---

## 3️ Administrator

The administrator supervises the entire platform.

Responsibilities include:

* Creating clubs
* Managing club managers
* Managing rooms
* Validating reservations
* Supervising platform activity

---

# Main Features

## Club Management

* Create and manage clubs
* Update club profiles
* Manage club administrators

---

## Events Management

* Create events
* Display upcoming activities
* Show event details

---

## Announcements

Clubs can publish announcements such as:

* Event announcements
* Recruitment messages
* Important updates

---

## Room Reservations

Club managers can request rooms for activities.

Reservation workflow:

```
Club Manager → Submit Request
        │
        ▼
Administrator Review
        │
        ├── Approved
        └── Rejected
```

---

## Equipment Reservations

Clubs can request equipment such as:

* Projectors
* Chairs
* Event materials

These requests must be **validated by the administrator**.

---

# Database Main Entities

Core system entities include:

* Users
* Clubs
* Announcements
* Activities
* Rooms
* Equipment
* Reservations

Example simplified relationship:

```
Club
 ├── Events
 ├── Announcements
 └── Reservations

User
 ├── Admin
 └── Club Manager
```

---

# UML Modeling

The system was designed using UML modeling.

### Diagrams created

* Use Case Diagram
* Class Diagram
* Sequence Diagrams

Examples include:

* Authentication process
* Club creation
* Room reservation
* Club manager account creation


```
docs/
 ├── usecase-diagram.png
 ├── class-diagram.png
 ├── sequence-auth.png
 ├── sequence-reservation.png
```

---

# User Interface (Frontend)

The frontend provides several interfaces:

* Home page
* Club profile page
* Event list
* Event details
* Reservation interface
* Admin dashboard

---

# Backend Repository (This Repository)

This repository contains the **Laravel backend API**.

Main responsibilities:

* Authentication
* Clubs management
* Announcements management
* Events management
* Reservation system
* Admin operations

---

# Project Structure

Example simplified structure:

```
gestion_clubs_backend/

app/
 ├── Models
 ├── Http
 │    ├── Controllers
 │    └── Middleware
 ├── Services

routes/
 ├── api.php

database/
 ├── migrations
 └── seeders

config/

```

---

# Installation

## 1️ Clone the repository

```bash
git clone https://github.com/ELHAIDYousef/gestion_clubs.git
cd gestion_clubs
```

---

## 2️ Install dependencies

```bash
composer install
```

---

## 3️ Configure environment variables

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

## 4️ Generate application key

```bash
php artisan key:generate
```

---

## 5️ Run migrations

```bash
php artisan migrate
```

---

## 6️ Start the server

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

* Postman
* Insomnia
* Thunder Client (VSCode)

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

* Mobile application
* Real-time notifications
* Advanced analytics dashboard
* Event registration system
* Integration with university systems

---

# Project Team

* AIT SAIDOU-ALI Saad
* BAROU Charaf
* BEN TALEB Ilyasse
* ELGHOUALI Zakariae
* ELHAID Yousef

Supervisor:

**Dr. Soufiane Hamida**

ENSET Mohammedia — 2024/2025



