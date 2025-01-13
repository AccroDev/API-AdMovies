<?php
namespace Controllers;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Dotenv\Dotenv;

class Mail {
    public $mailClass;

    public function __construct(string $adress, string $contenu, string $sujet = '', string $piece = NULL) {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        $mail = new PHPMailer();
        // Dire à PHPMailer d'utiliser SMTP
        $mail->isSMTP();
        $mail->Encoding = 'base64';
        // Définir le debugage SMTP
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        // Définir le serveur de mail
        $mail->Host = $_ENV['MAIL_HOST'];
        // Définir le port SMTP
        $mail->Port = $_ENV['MAIL_PORT'];
        // Définir l'encryptage
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        // Obliger l'authentification à chaque fois
        $mail->SMTPAuth = true;
        // Adresse mail à utiliser pour l'authentification SMTP
        $mail->Username = $_ENV['MAIL_USERNAME'];
        // Mot de passe pour l'authentification SMTP
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        // De quelle adresse mail vient l'email
        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        // Où doivent être renvoyées les réponses
        $mail->addReplyTo($_ENV['MAIL_REPLY_TO_ADDRESS'], $_ENV['MAIL_REPLY_TO_NAME']);
        // Ajouter une pièce jointe
        if (isset($piece) && $piece !== NULL) {
            $mail->addAttachment($piece);
        }
        // Définir le destinataire
        $mail->addAddress($adress);
        // Définir l'objet
        $mail->Subject = "=?utf-8?B?" . base64_encode($sujet) . "?=";
        // Contenu à envoyer
        $mail->CharSet = 'utf-8';
        $mail->msgHTML($contenu, __DIR__);

        $this->mailClass = $mail;
    }

    public function send() {
        return $this->mailClass->send();
    }
}
?>