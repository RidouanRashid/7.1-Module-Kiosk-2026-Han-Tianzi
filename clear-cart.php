<?php
session_start();

$_SESSION['cart'] = [];

header("Location: index.php");
exit;
