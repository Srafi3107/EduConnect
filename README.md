# EduConnect - Home Tutor Finding System

EduConnect is a comprehensive web-based platform designed to bridge the gap between students (or guardians) and qualified home tutors. The system simplifies the process of posting tuition requirements, finding matching tutors, and managing applications.

## 🚀 Features

### For Students/Guardians
* **Post Tuition Jobs**: Create detailed requests specifying subject, class level, location, and proposed salary.
* **Review Applications**: View applications from interested tutors.
* **Accept/Reject**: Manage applications and select the best fit.
* **Track Status**: Monitor the status of your posted requests.

### For Tutors
* **Profile Management**: Set your preferences including subject, location, class level, and availability.
* **Smart Job Board**: Automatically filters and displays only the tuition jobs that perfectly match your profile criteria.
* **Apply for Jobs**: Send applications with a proposed salary and a custom message.
* **Job Limit System**: Tutors can have a maximum of 2 active/accepted tuitions at a time to ensure quality education.

### For Administrators
* **User Management**: View and manage all registered users across the platform.
* **Request Monitoring**: Oversee all tuition requests, their current status, and who filled them.

## 🛠️ Technology Stack
* **Backend**: PHP (Procedural with PDO for secure database interactions)
* **Database**: MySQL (Relational database)
* **Frontend**: HTML5, CSS3 (Custom styles), JavaScript (Vanilla)
* **UI Framework**: Bootstrap 5 (Responsive design, components), FontAwesome (Icons)

## ⚙️ Installation & Setup

1. **Prerequisites**: 
   * XAMPP/WAMP or any local server stack installed.
   * PHP 7.4+ and MySQL.
2. **Clone the Repository**:
   * Place the `educonnect` folder inside your `htdocs` (XAMPP) or `www` (WAMP) directory.
3. **Database Setup**:
   * Open phpMyAdmin (`http://localhost/phpmyadmin`).
   * Import the `database.sql` file provided in the root directory.
4. **Configuration**:
   * Open `config.php` and update the database credentials (port, username, password) if necessary to match your local environment.
5. **Run the Project**:
   * Visit `http://localhost/educonnect` in your web browser.

## 🔒 Security Measures
* **SQL Injection Prevention**: Uses PHP Data Objects (PDO) with prepared statements for all database queries.
* **Password Hashing**: Uses PHP's native `password_hash()` and `password_verify()` for secure credential storage.
* **Role-based Access Control**: Session-based middleware function (`checkRole()`) restricts unauthorized access to specific dashboards.

## 📝 Default Credentials
* **Admin Email**: `admin@hometutor.com`
* **Admin Password**: `admin123`

## 👨‍💻 Author
Developed as a project to demonstrate full-stack PHP capabilities, secure authentication, and complex relational database queries.
