<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Инициализация переменных
$errors = [];
$formData = [];
$cookie_name = 'form_data';
$error_cookie_name = 'form_errors';

// Функция для получения данных из Cookies
function getFromCookies($name) {
    return isset($_COOKIE[$name]) ? json_decode($_COOKIE[$name], true) : null;
}

// Проверяем наличие ошибок в Cookies
if ($error_data = getFromCookies($error_cookie_name)) {
    $errors = $error_data;
    setcookie('form_errors', '', time() - 3600, '/');
}

// Проверяем наличие сохраненных данных в Cookies
if ($saved_data = getFromCookies($cookie_name)) {
    $formData = $saved_data;
}

// Функция для получения CSS класса ошибки
function getErrorClass($field) {
    global $errors;
    return isset($errors[$field]) ? 'field-error' : '';
}

// Функция для получения сообщения об ошибке
function getErrorMessage($field) {
    global $errors;
    if (isset($errors[$field])) {
        return $errors[$field]['message'] . ' ' . $errors[$field]['allowed_chars'];
    }
    return '';
}
?>