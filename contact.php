<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    $mail = new PHPMailer(true);

    try {
        // Mailtrap SMTP setup
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fead2ad7782a4b'; 
        $mail->Password   = '159e7fe69f8d04'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 2525;

        // Email setup
        $mail->setFrom('no-reply@mealmate.com', 'MealMate Contact Form');
        $mail->addReplyTo($email, $name);  // so you can reply directly
        $mail->addAddress('test@mealmate.com'); 

        $mail->isHTML(true);
        $mail->Subject = "New Contact Form Message from $name";
        $mail->Body    = nl2br("
            <strong>Name:</strong> $name<br>
            <strong>Email:</strong> $email<br><br>
            <strong>Message:</strong><br>$message
        ");

        $mail->send();
        echo "✅ Message sent successfully! Check your Mailtrap inbox.";
    } catch (Exception $e) {
        echo "❌ Mail error: {$mail->ErrorInfo}";
    }
}
?>
