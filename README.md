# 🏥 Hospital Appointment Booking System

A **PHP and MySQL based Hospital Appointment Booking System** that allows patients to find doctors, check available appointment slots, book and manage appointments. The system provides separate dashboards for **Patients, Doctors, and Administrators**.

The application follows an **MVC-style architecture** using PHP, MySQL, HTML, CSS, and JavaScript.

---

## 📌 Features

### 👤 Authentication & User Management

* Patient registration and login
* Session-based authentication
* Role-based access:

  * Patient
  * Doctor
  * Admin
* Profile management
* Password change
* User activation/deactivation

### 🧑‍⚕️ Doctor Management

* Add, edit, and deactivate doctors
* Assign specializations
* Doctor biography and consultation fee
* Doctor photo upload
* Configure available working days

### 📅 Appointment Management

* Browse doctors by specialization
* View doctor profiles
* Check available appointment slots
* Book appointments
* Prevent double booking
* View appointment history
* Cancel appointments
* Update appointment status
* Reschedule appointments
* Appointment statuses:

  * Pending
  * Confirmed
  * Completed
  * Cancelled
  * No-Show

### 👨‍⚕️ Doctor Dashboard

* View today's appointments
* View upcoming appointments
* Weekly schedule
* Appointment status management
* Appointment rescheduling
* Income summary

### 🛠️ Admin Dashboard

* View total doctors, patients, and appointments
* View total and today's revenue
* Doctor revenue statistics
* Manage doctors
* Manage users
* Manage specializations
* Manage appointments
* View revenue reports

### 🔌 API

The project includes API functionality for:

* Doctor listing and filtering
* Doctor statistics
* Available doctor slots
* Appointment cancellation
* Appointment status updates
* User activation

---

## 🛠️ Tech Stack

| Technology             | Purpose                                  |
| ---------------------- | ---------------------------------------- |
| PHP                    | Backend development                      |
| MySQL                  | Database                                 |
| HTML5                  | Page structure                           |
| CSS3                   | UI styling                               |
| JavaScript             | Client-side interaction                  |
| PDO                    | Database connection and prepared queries |
| Apache                 | Web server                               |
| XAMPP                  | Local development environment            |
| MVC-style Architecture | Application organization                 |

---

## 📂 Project Structure

```text
hospital/
│
├── config/
│   ├── app.php
│   ├── database.php
│   └── schema.sql
│
├── controllers/
│   ├── AdminController.php
│   ├── ApiController.php
│   ├── AppointmentController.php
│   ├── AuthController.php
│   ├── DoctorController.php
│   └── PatientController.php
│
├── models/
│   ├── AppointmentModel.php
│   ├── DoctorModel.php
│   ├── SpecializationModel.php
│   └── UserModel.php
│
├── views/
│   ├── admin/
│   ├── auth/
│   ├── doctor/
│   ├── errors/
│   ├── layouts/
│   ├── patient/
│   └── landing.php
│
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── uploads/
│       └── doctors/
│
├── index.php
├── .htaccess
└── README.md
```

---

## 🗄️ Database Structure

The MySQL database contains four main tables.

### 👤 Users

Stores patient, doctor, and admin accounts.

**Main fields:**

* ID
* Name
* Email
* Password Hash
* Role
* Date of Birth
* Blood Group
* Phone
* Active Status
* Created Date

### 🩺 Specializations

Stores doctor specialization categories such as:

* Cardiology
* Dermatology
* Neurology
* Orthopedics
* Pediatrics
* General Medicine
* Gynecology

### 👨‍⚕️ Doctors

Stores doctor-specific information including:

* User ID
* Specialization
* Biography
* Consultation Fee
* Photo
* Available Days

### 📅 Appointments

Stores patient-doctor appointment information including:

* Appointment Date
* Appointment Time
* Booking Fee
* Reason
* Status
* Cancellation Reason

---

## 🔗 Database Relationships

```text
Users
 │
 ├── 1 : 1 ── Doctors
 │               │
 │               └── N : 1 ── Specializations
 │
 └── 1 : N ── Appointments ── N : 1 ── Doctors
```

---

# ⚙️ Project Setup

## 1. Install and Start XAMPP

Install **XAMPP** and start:

* Apache
* MySQL

from the XAMPP Control Panel.

---

## 2. Copy the Project

Place the project inside:

```text
C:\xampp\htdocs\hospital
```

---

## 3. Create the Database

Open:

```text
http://localhost/phpmyadmin
```

Go to the **SQL** tab and run:

```text
config/schema.sql
```

This creates the `hospital_db` database and the required tables.

---

## 4. Check Database Configuration

Open:

```text
config/database.php
```

The project uses:

```text
Host: localhost
Database: hospital_db
Username: root
Password: empty by default
```

Update these values if your MySQL configuration is different.

---

## 5. Check Application URL

Open:

```text
config/app.php
```

Make sure the `BASE_URL` matches your project folder name:

```php
define('BASE_URL', 'http://localhost/hospital');
```

If your project folder is named `hospital_final`, use:

```php
define('BASE_URL', 'http://localhost/hospital_final');
```

---

## 6. Run the Application

Make sure **Apache and MySQL** are running in XAMPP.

Then open:

```text
http://localhost/hospital
```

---

## 🔐 Default Admin Account

The database schema contains a default administrator account:

```text
Email: admin@hospital.com
Password: password
```

> ⚠️ Change the default password after the first login.

---

# 🌐 Main Application Pages

## 🔐 Authentication

```text
/login
/register
/profile
```

## 👤 Patient

```text
patient/home
patient/doctor-profile
patient/book
patient/confirmation
patient/appointments
```

## 👨‍⚕️ Doctor

```text
doctor/dashboard
doctor/appointments
doctor/reschedule
doctor/schedule
```

## 🛠️ Admin

```text
admin/dashboard
admin/doctors
admin/doctor-edit
admin/users
admin/user-edit
admin/specializations
admin/appointments
admin/revenue-report
```

---

# 🔌 API Endpoints

The source includes API functionality for:

```text
GET   /api/doctors
GET   /api/doctor-stats
GET   /api/doctor-slots
POST  /api/appointments/cancel
PUT   /api/appointments/{id}
```

---

# 📸 Screenshots

## 🏠 Landing Page

<img width="1893" height="931" alt="Landing Page" src="https://github.com/user-attachments/assets/dd7b6e7c-aa0e-4707-8900-c4e254735910" />

<img width="1890" height="967" alt="Landing Page" src="https://github.com/user-attachments/assets/dc0fdfb0-4c1d-4a47-a694-e8f7550ce7fc" />

---

## 🔐 Login Page

<img width="1891" height="960" alt="Login Page" src="https://github.com/user-attachments/assets/14a4f5a4-5527-4594-b237-8bb0efefa6b2" />

---

## 🧑‍🤝‍🧑 Patient Dashboard

<img width="1918" height="955" alt="Patient Dashboard" src="https://github.com/user-attachments/assets/bea8c38c-d4a9-4310-91f1-cb989e062151" />

---

## 📅 Appointment Booking

<img width="1912" height="912" alt="Appointment Booking" src="https://github.com/user-attachments/assets/96ca9b88-50fa-46ea-be2f-39ac8cfae4a9" />

---

## 👨‍⚕️ Doctor Dashboard

<img width="1877" height="943" alt="Doctor Dashboard" src="https://github.com/user-attachments/assets/e44be040-0950-4556-9c60-0228276e16ea" />

<img width="1877" height="953" alt="Doctor Dashboard" src="https://github.com/user-attachments/assets/0b64c497-1e22-4dad-bca8-45ccfe41de17" />

---

## 🛠️ Admin Dashboard

<img width="1905" height="893" alt="Admin Dashboard" src="https://github.com/user-attachments/assets/160be30c-320a-4635-99d1-81d37bb455c6" />

<img width="1878" height="930" alt="Admin Dashboard" src="https://github.com/user-attachments/assets/3c14f82e-759e-46c6-9363-e9e658385d48" />

<img width="1871" height="935" alt="Admin Dashboard" src="https://github.com/user-attachments/assets/386a57a7-ecdb-41fc-b2ea-8ac9471191ef" />

<img width="1880" height="887" alt="Admin Dashboard" src="https://github.com/user-attachments/assets/7b8be9f9-f98c-436a-8fc6-4119e54543c9" />

<img width="1882" height="958" alt="Admin Dashboard" src="https://github.com/user-attachments/assets/936e7b22-1be7-4f0e-91eb-58bef79ff6bb" />

<img width="1892" height="890" alt="Admin Dashboard" src="https://github.com/user-attachments/assets/b658a5ab-629d-413b-a4f6-68527db0e663" />

---

# 🔒 Security & Validation

The project includes:

* Session-based authentication
* Role-based authorization
* Password hashing
* PDO prepared statements
* Input sanitization
* Email validation
* Date validation
* Phone validation
* Appointment slot validation
* Double-booking prevention
* Doctor photo upload size limitation

---

# 🎯 Project Objective

The main objective is to provide a centralized online platform for managing hospital doctor appointments.

The main workflow is:

```text
Patient
   ↓
Doctor Search
   ↓
Doctor Profile
   ↓
Available Slot
   ↓
Appointment Booking
   ↓
Appointment Management
```

The system simplifies appointment management for patients while providing doctors and administrators with tools to manage:

* Doctors
* Patients
* Appointments
* Schedules
* Specializations
* Users
* Revenue

---

# 🚀 Future Improvements

* Online payment integration
* Email/SMS appointment notifications
* Doctor ratings and reviews
* Prescription management
* Medical records
* Appointment reminders
* Advanced analytics
* Cloud deployment
* API documentation
* Additional security features

---

# 📄 License

This project is developed for **educational and academic purposes**.

