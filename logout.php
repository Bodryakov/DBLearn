<?php

// Файл logout.php - Выход из системы

session_start();

// Удаляем все данные сессии
$_SESSION = array();
session_destroy();

// Удаляем куки
setcookie('remember_me', '', time() - 3600, '/');

// Перенаправляем на страницу входа
header('Location: /login');
exit;