<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: VIEWS/index.php');
exit;