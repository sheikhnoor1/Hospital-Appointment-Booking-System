🏥 Hospital Appointment Booking System
A PHP and MySQL based hospital appointment booking web application that allows patients to find doctors, check available appointment slots, and manage appointments. The system provides separate dashboards for patients, doctors, and administrators.
The application follows an MVC-style architecture using PHP, MySQL, HTML, CSS and JavaScript.

📌 Features
👤 Authentication & User Management
● Patient registration and login
● Session-based authentication
● Role-based access: Patient, Doctor and Admin
● Profile management
● Password change
● User activation/deactivation
🧑‍⚕️ Doctor Management
● Add, edit and deactivate doctors
● Assign specializations
● Doctor biography and consultation fee
● Doctor photo upload
● Configure available working days
📅 Appointment Management
● Browse doctors by specialization
● View doctor profiles
● Check available appointment slots
● Book appointments
● Prevent double booking
● View appointment history
● Cancel appointments
● Update appointment status
● Reschedule appointments
● Statuses: Pending, Confirmed, Completed, Cancelled and No-Show
👨‍⚕️ Doctor Dashboard
● Today’s appointments
● Upcoming appointments
● Weekly schedule
● Appointment status management
● Appointment rescheduling
● Income summary
🛠️ Admin Dashboard
● Total doctors, patients and appointments
● Total and today’s revenue
● Doctor revenue statistics
● Manage doctors, users and specializations
● Manage all appointments
● Revenue reports
🔌 API
The project includes API functionality for doctor listing/filtering, doctor statistics, available slots, appointment cancellation, appointment status updates and user activation.

🛠️ Tech Stack
|Technology            |Purpose                             |
|----------------------|------------------------------------|
|PHP                   |Backend development                 |
|MySQL                 |Database                            |
|HTML5                 |Page structure                      |
|CSS3                  |UI styling                          |
|JavaScript            |Client-side interaction             |
|PDO                   |Database connection/prepared queries|
|Apache                |Web server                          |
|XAMPP                 |Local development environment       |
|MVC-style Architecture|Application organization            |

📂 Project Structure
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
│   ├── css/style.css
│   ├── js/main.js
│   └── uploads/doctors/
│
├── index.php
├── .htaccess
└── README.md
```

🗄️ Database Structure
The MySQL database contains four main tables:
users
Stores patient, doctor and admin accounts.
Main fields: ID, Name, Email, Password Hash, Role, DOB, Blood Group, Phone, Active Status and Created Date.
specializations
Stores doctor categories such as Cardiology, Dermatology, Neurology, Orthopedics, Pediatrics, General Medicine, Gynecology and Ophthalmology.
doctors
Stores doctor-specific information including User ID, Specialization, Biography, Consultation Fee, Photo and Available Days.
appointments
Stores patient-doctor appointment information including date, time, fee, reason, status and cancellation reason.
🔗 Relationships
```text
Users
 │
 ├── 1 : 1 ── Doctors
 │               │
 │               └── N : 1 ── Specializations
 │
 └── 1 : N ── Appointments ── N : 1 ── Doctors
```

⚙️ Project Setup
1. Install and Start XAMPP
Start Apache and MySQL from the XAMPP Control Panel.
2. Copy the Project
Place the project inside:
```text
C:\xampp\htdocs\hospital
```
3. Create the Database
Open:
```text
http://localhost/phpmyadmin
```
Go to SQL and run:
```text
config/schema.sql
```
This creates the hospital_db database and required tables.
4. Check Database Configuration
Open config/database.php. The project uses:
```text
Host: localhost
Database: hospital_db
Username: root
Password: empty by default
```
Update these values if your MySQL configuration is different.
5. Check Application URL
Open config/app.php and make sure:
```php
define('BASE_URL', 'http://localhost/hospital');
```
matches your project folder name.
For example, if the folder is hospital_final:
```php
define('BASE_URL', 'http://localhost/hospital_final');
```
6. Run the Application
Open:
```text
http://localhost/hospital
```

🔐 Default Admin Account
The database schema contains a default administrator account:
```text
Email: admin@hospital.com
Password: password
```
⚠️ Change the default password after the first login.

🌐 Main Application Pages
Authentication
```text
/login
/register
/profile
```
Patient
```text
patient/home
patient/doctor-profile
patient/book
patient/confirmation
patient/appointments
```
Doctor
```text
doctor/dashboard
doctor/appointments
doctor/reschedule
doctor/schedule
```
Admin
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

🔌 API Endpoints
The source includes API functionality for:
```text
GET   /api/doctors
GET   /api/doctor-stats
GET   /api/doctor-slots
POST  /api/appointments/cancel
PUT   /api/appointments/{id}
```

🏠 Landing Page
<img width="1893" height="931" alt="image" src="https://github.com/user-attachments/assets/dd7b6e7c-aa0e-4707-8900-c4e254735910" />

🔐 Login Page
Login Page
🧑‍🤝‍🧑 Patient Dashboard
Patient Dashboard
📅 Appointment Booking
Appointment Booking
👨‍⚕️ Doctor Dashboard
Doctor Dashboard
🛠️ Admin Dashboard
Admin Dashboard
> The supplied project archive did not contain UI screenshots, so the README uses ready-to-fill relative screenshot paths rather than inventing screenshots. Once the images are placed in `screenshots/`, GitHub will display them automatically.

🔒 Security & Validation
The project includes:
● Session-based authentication
● Role-based authorization
● Password hashing
● PDO prepared statements
● Input sanitization
● Email, date and phone validation
● Appointment slot validation
● Double-booking prevention
● Doctor photo upload size limitation

🎯 Project Objective
The main objective is to provide a centralized online platform for managing hospital doctor appointments.
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
The system simplifies appointment management for patients while giving doctors and administrators tools to manage schedules, appointments, users, doctors, specializations and revenue.

🚀 Future Improvements
● Online payment integration
● Email/SMS appointment notifications
● Doctor ratings and reviews
● Prescription management
● Medical records
● Appointment reminders
● Advanced analytics
● Cloud deployment
● API documentation
● Additional security features

📄 License
This project is developed for educational and academic purposes.

