<?php
session_start();
session_unset();
session_destroy();

// Cookie: Clear "Remember Me" cookies on logout
setcookie('remember_email', '', time() - 3600, '/EduConnect/');
setcookie('remember_password', '', time() - 3600, '/EduConnect/');

header("Location: /EduConnect/auth/login.php");
exit();
?>
