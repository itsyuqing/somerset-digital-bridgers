<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name    = trim(htmlspecialchars($_POST["name"] ?? ""));
    $email   = trim($_POST["email"] ?? "");
    $message = trim(htmlspecialchars($_POST["message"] ?? ""));

    if ($name === "" || $message === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Please fill in your name, a valid email, and a message.";
        exit;
    }
    $email = htmlspecialchars($email);

    $mail = new PHPMailer(true);

    try {
        $smtpUser = getenv('CONTACT_SMTP_USER');
        $smtpPass = getenv('CONTACT_SMTP_PASS');
        $toAddress = getenv('CONTACT_TO_EMAIL') ?: $smtpUser;

        if (!$smtpUser || !$smtpPass) {
            http_response_code(500);
            echo "Server email is not configured.";
            exit;
        }

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($smtpUser, 'Somerset Digital Bridgers Website');
        $mail->addReplyTo($email, $name);
        $mail->addAddress($toAddress);

        $mail->isHTML(true);
        $mail->Subject = "New Contact Form Message";
        $mail->Body    = "
            <h3>New message from your site</h3>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Message:</strong><br>{$message}</p>
        ";

        $mail->send();
        echo "Message sent successfully.";
    } catch (Exception $e) {
        http_response_code(500);
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
