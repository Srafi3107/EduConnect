# EduConnect - Home Tutor Finding System
# EduConnect
EduConnect is a comprehensive web-based platform designed to bridge the gap between students (or guardians) and qualified home tutors. The system simplifies the process of posting tuition requirements, finding matching tutors, and managing applications.
EduConnect is a modern, responsive web application that connects students (and their guardians) with qualified home tutors. It features a robust role-based system for Admins, Tutors, and Students, complete with a job board, salary negotiation, and a built-in review system.
## 🚀 Features
## 🌟 Key Features
### For Students/Guardians
* **Post Tuition Jobs**: Create detailed requests specifying subject, class level, location, and proposed salary.
* **Review Applications**: View applications from interested tutors.
* **Accept/Reject**: Manage applications and select the best fit.
* **Track Status**: Monitor the status of your posted requests.
### 👨‍🎓 For Students & Guardians
- **Job Board:** Post public tuition requests with specific requirements (Subject, Class Level, Location, Salary, Gender Preference).
- **Find Tutors:** Search and filter a database of available tutors based on specific criteria.
- **Application Management:** Review applications from interested tutors, and accept, reject, or send counter-offers.
- **Tutor Reviews:** Leave 1-to-5 star ratings and written reviews for hired tutors.
### For Tutors
* **Profile Management**: Set your preferences including subject, location, class level, and availability.
* **Smart Job Board**: Automatically filters and displays only the tuition jobs that perfectly match your profile criteria.
* **Apply for Jobs**: Send applications with a proposed salary and a custom message.
* **Job Limit System**: Tutors can have a maximum of 2 active/accepted tuitions at a time to ensure quality education.
### 👩‍🏫 For Tutors
- **Customizable Profiles:** Highlight teaching experience, location, primary/secondary subjects, and expected salary.
- **Browse Jobs:** View a smart-filtered job board that automatically highlights tuition requests matching your subjects.
- **Negotiation System:** Apply to jobs and negotiate salaries with guardians directly through the platform.
- **Earnings Estimator:** Dashboard analytics to track active students and expected monthly earnings.
### For Administrators
* **User Management**: View and manage all registered users across the platform.
* **Request Monitoring**: Oversee all tuition requests, their current status, and who filled them.
### 🛡️ For Administrators
- **Dashboard Analytics:** View system-wide metrics including total active tuitions, users, and job posts.
- **User Management:** Monitor the platform and easily Ban or Unban users who violate terms.
- **Data Export:** Export detailed CSV reports of all tuition requests and applications.
## 🛠️ Technology Stack
* **Backend**: PHP (Procedural with PDO for secure database interactions)
* **Database**: MySQL (Relational database)
* **Frontend**: HTML5, CSS3 (Custom styles), JavaScript (Vanilla)
* **UI Framework**: Bootstrap 5 (Responsive design, components), FontAwesome (Icons)
## ⚙️ Installation & Setup
- **Backend:** PHP (Vanilla)
- **Database:** MySQL (via PDO)
- **Frontend:** HTML5, CSS3, JavaScript
- **Styling/UI:** Bootstrap 5 with a custom premium SaaS aesthetic (Inter font, glassmorphism, gradient navbars).
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
## 🚀 Installation & Setup
## 🔒 Security Measures
* **SQL Injection Prevention**: Uses PHP Data Objects (PDO) with prepared statements for all database queries.
* **Password Hashing**: Uses PHP's native `password_hash()` and `password_verify()` for secure credential storage.
* **Role-based Access Control**: Session-based middleware function (`checkRole()`) restricts unauthorized access to specific dashboards.
1. **Prerequisites**
   - Install [XAMPP](https://www.apachefriends.org/index.html) or a similar local web server stack.
   - Make sure Apache and MySQL are running.
## 📝 Default Credentials
* **Admin Email**: `admin@hometutor.com`
* **Admin Password**: `admin123`
2. **Clone the Repository**
   - Clone or copy this project into your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\educonnect`).
## 👨‍💻 Author
Developed as a project to demonstrate full-stack PHP capabilities, secure authentication, and complex relational database queries.
