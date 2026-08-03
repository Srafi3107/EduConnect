EduConnect
EduConnect is a modern, responsive web application that connects students (and their guardians) with qualified home tutors. It features a robust role-based system for Admins, Tutors, and Students, complete with a job board, salary negotiation, and a built-in review system.

🌟 Key Features
👨‍🎓 For Students & Guardians
Job Board: Post public tuition requests with specific requirements (Subject, Class Level, Location, Salary, Gender Preference).
Find Tutors: Search and filter a database of available tutors based on specific criteria.
Application Management: Review applications from interested tutors, and accept, reject, or send counter-offers.
Tutor Reviews: Leave 1-to-5 star ratings and written reviews for hired tutors.
👩‍🏫 For Tutors
Customizable Profiles: Highlight teaching experience, location, primary/secondary subjects, and expected salary.
Browse Jobs: View a smart-filtered job board that automatically highlights tuition requests matching your subjects.
Negotiation System: Apply to jobs and negotiate salaries with guardians directly through the platform.
Earnings Estimator: Dashboard analytics to track active students and expected monthly earnings.
🛡️ For Administrators
Dashboard Analytics: View system-wide metrics including total active tuitions, users, and job posts.
User Management: Monitor the platform and easily Ban or Unban users who violate terms.
Data Export: Export detailed CSV reports of all tuition requests and applications.
🛠️ Technology Stack
Backend: PHP (Vanilla)
Database: MySQL (via PDO)
Frontend: HTML5, CSS3, JavaScript
Styling/UI: Bootstrap 5 with a custom premium SaaS aesthetic (Inter font, glassmorphism, gradient navbars).
🚀 Installation & Setup
Prerequisites

Install XAMPP or a similar local web server stack.
Make sure Apache and MySQL are running.
Clone the Repository

Clone or copy this project into your XAMPP htdocs directory (e.g., C:\xampp\htdocs\educonnect).
Database Setup

Open phpMyAdmin (usually http://localhost/phpmyadmin).
Create a new, empty database named hometutor_db.
Configuration

Open config.php in the root of the project.
Update the $port if your MySQL is running on a non-standard port (default is 3306, some XAMPP setups use 3307).
Ensure the $username and $password match your local MySQL credentials.
Run Migrations

Open your browser and navigate to the setup scripts to initialize the database tables:
Run http://localhost/educonnect/setup.php
Run http://localhost/educonnect/migrate.php
Run http://localhost/educonnect/migrate_v3.php
Access the Application

Navigate to http://localhost/educonnect in your web browser.
You can register a new account as a Student or Tutor.
Default Admin Account:
Email: 
admin@hometutor.com
Password: admin123
🎨 UI / UX Notes
The application has recently undergone a major UI overhaul to feature a premium, dynamic design. It uses modern typography, soft drop shadows, rounded corners, and deep indigo gradients to ensure an excellent user experience across both desktop and mobile devices.
