<?php
session_start();
session_unset();
session_destroy();
header("Location: /EduConnect/auth/login.php");
exit();
?>
