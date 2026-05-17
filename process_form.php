<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Настройки подключения к БД
$host = 'localhost';
$dbname = 'u82814';
$username = 'u82814';
$password = '3096918';

// Функция для безопасного получения POST данных
function getPostValue($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

// Функция для сохранения данных в Cookies на 1 год
function saveToCookies($data) {
    $cookie_value = json_encode($data);
    setcookie('form_data', $cookie_value, time() + (365 * 24 * 60 * 60), '/');
}

// Функция для сохранения ошибок в Cookies до конца сессии
function saveErrorsToCookies($errors) {
    $cookie_value = json_encode($errors);
    setcookie('form_errors', $cookie_value, 0, '/');
}

// Подключение к базе данных
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Функции валидации
function validateFullname($fullname) {
    if (empty($fullname)) {
        return ['valid' => false, 'message' => 'ФИО обязательно для заполнения.', 'allowed_chars' => 'Допустимые символы: буквы русского и английского алфавита, пробелы и дефисы.'];
    }
    if (strlen($fullname) > 150) {
        return ['valid' => false, 'message' => 'ФИО не должно превышать 150 символов.', 'allowed_chars' => 'Максимальная длина: 150 символов.'];
    }
    if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $fullname)) {
        return ['valid' => false, 'message' => 'ФИО содержит недопустимые символы.', 'allowed_chars' => 'Допустимые символы: буквы русского и английского алфавита, пробелы и дефисы.'];
    }
    return ['valid' => true];
}

function validatePhone($phone) {
    if (!empty($phone)) {
        if (strlen($phone) > 50) {
            return ['valid' => false, 'message' => 'Телефон не должен превышать 50 символов.', 'allowed_chars' => 'Максимальная длина: 50 символов.'];
        }
        if (!preg_match('/^[\+\d\s\-\(\)]+$/', $phone)) {
            return ['valid' => false, 'message' => 'Некорректный формат телефона.', 'allowed_chars' => 'Допустимые символы: цифры, знак +, пробелы, дефисы и скобки.'];
        }
        if (!preg_match('/\d/', $phone)) {
            return ['valid' => false, 'message' => 'Телефон должен содержать хотя бы одну цифру.', 'allowed_chars' => 'Допустимые символы: цифры, знак +, пробелы, дефисы и скобки.'];
        }
    }
    return ['valid' => true];
}

function validateEmail($email) {
    if (empty($email)) {
        return ['valid' => false, 'message' => 'E-mail обязателен для заполнения.', 'allowed_chars' => 'Допустимый формат: example@domain.com'];
    }
    if (strlen($email) > 100) {
        return ['valid' => false, 'message' => 'E-mail не должен превышать 100 символов.', 'allowed_chars' => 'Максимальная длина: 100 символов.'];
    }
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        return ['valid' => false, 'message' => 'Некорректный формат e-mail.', 'allowed_chars' => 'Допустимые символы: латинские буквы, цифры, точка, дефис, подчеркивание, знак @'];
    }
    return ['valid' => true];
}

function validateBirthdate($birthdate) {
    if (!empty($birthdate)) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
            return ['valid' => false, 'message' => 'Некорректный формат даты.', 'allowed_chars' => 'Допустимый формат: ГГГГ-ММ-ДД'];
        }
        $date = DateTime::createFromFormat('Y-m-d', $birthdate);
        if (!$date || $date->format('Y-m-d') !== $birthdate) {
            return ['valid' => false, 'message' => 'Некорректная дата рождения.', 'allowed_chars' => 'Допустимый формат: ГГГГ-ММ-ДД'];
        }
        if ($date > new DateTime()) {
            return ['valid' => false, 'message' => 'Дата рождения не может быть в будущем.', 'allowed_chars' => 'Допустимый формат: ГГГГ-ММ-ДД'];
        }
    }
    return ['valid' => true];
}

function validateGender($gender) {
    $allowed = ['male', 'female', 'other', 'unspecified'];
    if (!in_array($gender, $allowed)) {
        return ['valid' => false, 'message' => 'Некорректное значение пола.', 'allowed_chars' => 'Допустимые значения: male, female, other, unspecified'];
    }
    return ['valid' => true];
}

function validateLanguages($languages, $pdo) {
    if (empty($languages)) {
        return ['valid' => false, 'message' => 'Выберите хотя бы один язык программирования.', 'allowed_chars' => 'Необходимо выбрать хотя бы один язык из списка'];
    }
    if (count($languages) > 12) {
        return ['valid' => false, 'message' => 'Выбрано слишком много языков.', 'allowed_chars' => 'Максимальное количество языков: 12'];
    }
    
    foreach ($languages as $lang) {
        if (!preg_match('/^[a-zA-Z\+\#]+$/', $lang)) {
            return ['valid' => false, 'message' => 'Название языка содержит недопустимые символы.', 'allowed_chars' => 'Допустимые символы: латинские буквы, знаки + и #'];
        }
    }
    
    $placeholders = str_repeat('?,', count($languages) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id FROM programming_languages WHERE name IN ($placeholders)");
    $stmt->execute($languages);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($existing) != count($languages)) {
        return ['valid' => false, 'message' => 'Один или несколько выбранных языков не поддерживаются.', 'allowed_chars' => 'Выберите языки из предложенного списка'];
    }
    return ['valid' => true];
}

function validateBiography($bio) {
    if (!empty($bio)) {
        if (strlen($bio) > 10000) {
            return ['valid' => false, 'message' => 'Биография не должна превышать 10000 символов.', 'allowed_chars' => 'Максимальная длина: 10000 символов.'];
        }
        if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ0-9\s\.\,\!\?\-\:\;\"\'\(\)\[\]\{\}\@\#\$\%\^\&\*\+\=\/\\\|<>~`\_]*$/u', $bio)) {
            return ['valid' => false, 'message' => 'Биография содержит недопустимые символы.', 'allowed_chars' => 'Допустимые символы: буквы, цифры, пробелы, знаки препинания и специальные символы'];
        }
    }
    return ['valid' => true];
}

function validateContract($contract) {
    if (!isset($contract) || ($contract != 'on' && $contract != '1' && $contract !== true)) {
        return ['valid' => false, 'message' => 'Необходимо подтвердить ознакомление с контрактом.', 'allowed_chars' => 'Поставьте галочку для подтверждения'];
    }
    return ['valid' => true];
}

// Сбор данных
$formData = [
    'fullname' => getPostValue('fullname'),
    'phone' => getPostValue('phone'),
    'email' => getPostValue('email'),
    'birthdate' => getPostValue('birthdate'),
    'gender' => getPostValue('gender', 'unspecified'),
    'languages' => isset($_POST['fav_langs']) ? $_POST['fav_langs'] : [],
    'biography' => getPostValue('bio'),
    'contract' => isset($_POST['contract_agreed']) ? $_POST['contract_agreed'] : ''
];

// Валидация
$errors = [];
$validationResults = [];

$validationResults['fullname'] = validateFullname($formData['fullname']);
$validationResults['phone'] = validatePhone($formData['phone']);
$validationResults['email'] = validateEmail($formData['email']);
$validationResults['birthdate'] = validateBirthdate($formData['birthdate']);
$validationResults['gender'] = validateGender($formData['gender']);
$validationResults['languages'] = validateLanguages($formData['languages'], $pdo);
$validationResults['biography'] = validateBiography($formData['biography']);
$validationResults['contract'] = validateContract($formData['contract']);

foreach ($validationResults as $field => $result) {
    if (!$result['valid']) {
        $errors[$field] = [
            'message' => $result['message'],
            'allowed_chars' => $result['allowed_chars']
        ];
    }
}

// Если есть ошибки - сохраняем в Cookies и возвращаем на форму
if (!empty($errors)) {
    saveErrorsToCookies($errors);
    header('Location: form.html');
    exit;
}

// Если ошибок нет - сохраняем в БД
$success = false;
$error_message = '';

try {
    $pdo->beginTransaction();
    
    // Вставка в таблицу applications
    $stmt = $pdo->prepare("
        INSERT INTO applications (fullname, phone, email, birthdate, gender, biography, contract_agreed)
        VALUES (:fullname, :phone, :email, :birthdate, :gender, :biography, :contract)
    ");
    
    $stmt->execute([
        ':fullname' => $formData['fullname'],
        ':phone' => $formData['phone'] ?: null,
        ':email' => $formData['email'],
        ':birthdate' => $formData['birthdate'] ?: null,
        ':gender' => $formData['gender'],
        ':biography' => $formData['biography'] ?: null,
        ':contract' => $formData['contract'] == 'on' ? 1 : 0
    ]);
    
    $applicationId = $pdo->lastInsertId();
    
    // Вставка языков программирования
    $stmtLang = $pdo->prepare("SELECT id FROM programming_languages WHERE name = ?");
    $stmtInsert = $pdo->prepare("
        INSERT INTO application_languages (application_id, language_id)
        VALUES (?, ?)
    ");
    
    foreach ($formData['languages'] as $langName) {
        $stmtLang->execute([$langName]);
        $langId = $stmtLang->fetchColumn();
        if ($langId) {
            $stmtInsert->execute([$applicationId, $langId]);
        }
    }
    
    $pdo->commit();
    
    // Сохраняем успешные данные в Cookies на 1 год
    saveToCookies($formData);
    
    $success = true;
    
} catch (Exception $e) {
    $pdo->rollBack();
    $error_message = $e->getMessage();
}

// Перенаправляем на страницу результата
if ($success) {
    header('Location: success.html');
} else {
    header('Location: error.html?message=' . urlencode($error_message));
}
exit;
?>
