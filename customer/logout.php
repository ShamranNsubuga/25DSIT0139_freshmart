<?php
require_once '../includes/config.php';
session_destroy();
header('Location: /freshmart/index.php');
exit;
