<?php
session_start();
session_destroy();
header("Location: ../../../frontend/auth/sign-in/login.php"); 
exit();
?>