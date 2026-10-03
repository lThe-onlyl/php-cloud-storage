<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

class MailService
{
  private array $config;

  public function __construct()
  {
    $this->config = require __DIR__
      . '/../../config/mail.php';
  }

  public function sendPasswordReset(
    string $email,
    string $resetUrl
  ): void {
    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();

      $mail->Host = $this->config['host'];
      $mail->SMTPAuth = true;
      $mail->Username = $this->config['username'];
      $mail->Password = $this->config['password'];
      $mail->Port = $this->config['port'];

      if ($this->config['encryption'] === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      }

      $mail->CharSet = 'UTF-8';

      $mail->setFrom(
        $this->config['from_email'],
        $this->config['from_name']
      );

      $mail->addAddress($email);

      $mail->isHTML(true);

      $mail->Subject = 'Password reset';

      $mail->Body = sprintf(
        '<h2>Password reset</h2>
                <p>You requested a password reset.</p>
                <p>
                    <a href="%s">
                        Reset your password
                    </a>
                </p>
                <p>This link is valid for 1 hour.</p>',
        htmlspecialchars(
          $resetUrl,
          ENT_QUOTES,
          'UTF-8'
        )
      );

      $mail->AltBody = sprintf(
        "Password reset\n\n"
        . "Open this link to reset your password:\n%s\n\n"
        . "The link is valid for 1 hour.",
        $resetUrl
      );

      $mail->send();
    } catch (Exception $exception) {
      throw new RuntimeException(

        'Unable to send password reset email: '
        . $exception->getMessage(),
        0,
        $exception
      );
    }
  }
}