<?php
session_start();
session_destroy();

// Redirect to login page
header("Location: login.php");
echo "<script>window.location.href = 'login.php';</script>";
exit();
?>