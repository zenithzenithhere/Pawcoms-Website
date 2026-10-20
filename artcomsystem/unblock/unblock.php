<?php
session_start();

//remove block
if (isset($_SESSION['is_blocked'])) {
    unset($_SESSION['is_blocked']);
}


//activity log
error_log("User unblocked at " . date('Y-m-d H:i:s'));

//return
header("Location: register.php");
exit();
?>