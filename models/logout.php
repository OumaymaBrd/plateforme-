<?php
session_start();
session_destroy();
header("Location: ../assets/pages/login.php");
exit();

?>

