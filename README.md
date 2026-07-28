# EduConnect — Home Tutor Finding System

EduConnect is a web-based platform that bridges the gap between students/guardians
and home tutors in Dhaka, Bangladesh.

## 🎯 Features
- **Student/Guardian Portal** — Post tuition requests with subject, class level, location, and budget (BDT)
- **Tutor Portal** — Browse matching job requests filtered by their preferred subject, class, and location
- **Smart Matching** — Tutors only see jobs that exactly match their profile preferences
- **Application System** — Tutors apply with a proposed salary; guardians can Accept, Reject, or Counter-offer
- **2-Tuition Limit** — Tutors are automatically marked "Not Available" once they accept 2 active jobs
- **Admin Dashboard** — Admins can manage all users and tuition requests, with live Open/Filled status
- **Session-based Auth** — Secure role-based access for Admin, Tutor, and Student roles

## 🛠️ Tech Stack
- **Backend:** PHP (Procedural)
- **Database:** MySQL via PDO (Prepared Statements)
- **Frontend:** HTML, Bootstrap 5, FontAwesome
- **Server:** XAMPP (Apache + MySQL)

## 👥 User Roles
| Role | Capabilities |
|------|-------------|
| Admin | Manage users, view & delete all tuition requests |
| Tutor | Set profile preferences, browse & apply for matching jobs |
| Student/Guardian | Post tuition requests, review & accept tutor applications |
