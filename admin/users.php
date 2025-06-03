<?php
session_start();

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index');
}

// Redirect to the actual users page
header('Location: allusers.php');
?> 