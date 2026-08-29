<?php
require __DIR__ . '/bootstrap.php';
Gfc\Auth::startSession();
session_destroy();
header('Location: login.php');
