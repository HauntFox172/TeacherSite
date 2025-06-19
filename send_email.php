<?php
// Включить вывод всех ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Убедиться, что нет лишних пробелов/выводов до этого места
header('Content-Type: application/json');

// Проверка метода
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit;
}

// Проверка существования полей
$required = ['name', 'email', 'message'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Поле $field обязательно"]);
        exit;
    }
}

// Обработка данных
$name = strip_tags($_POST['name']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$message = strip_tags($_POST['message']);

// Валидация email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email']);
    exit;
}

try {
    $mail = new PHPMailer(true);
    
    // Конфигурация SMTP (пример для Mail.ru)
    $mail->isSMTP();
    $mail->Host = 'smtp.mail.ru';
    $mail->SMTPAuth = true;
    $mail->Username = 'oksana.gurova.02@bk.ru'; // Замените на реальный
    $mail->Password = 'your_app_password'; // Пароль приложения
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    
    // От кого
    $mail->setFrom('your_email@mail.ru', 'Site Form');
    // Кому
    $mail->addAddress('oksana.gurova.02@bk.ru'); // Замените на реальный
    
    // Содержание
    $mail->isHTML(true);
    $mail->Subject = "Сообщение от $name";
    $mail->Body = "
        <h3>Новое сообщение с сайта</h3>
        <p><strong>Имя:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Сообщение:</strong></p>
        <p>" . nl2br(htmlspecialchars($message)) . "</p>
    ";
    
    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Сообщение успешно отправлено!']);
    
} catch (Exception $e) {
    // Логирование ошибки
    error_log('Mailer Error: ' . $e->getMessage());
    
    // Ответ с информацией об ошибке
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при отправке письма',
        'error' => $e->getMessage() // Только для отладки!
    ]);
}