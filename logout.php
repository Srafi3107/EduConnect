<?php
session_start();
session_unset();
session_destroy();
header("Location: /HomeTutor/auth/login.php");
exit();
?>
