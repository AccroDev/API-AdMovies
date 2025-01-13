<?php
namespace Controllers;

use Models\GetPDO;
use Dotenv\Dotenv;

class UserController {
    public function __construct() {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
    }

    /**
     * Inscription de l'utilisateur
     */
    public function register() {
        $name = $_POST['name'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$name || !$email || !$password) {
            echo json_encode(['statut' => false, 'message' => 'Missing required fields']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $checkQuery = $bdd->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $checkQuery->execute([$email]);
        $exists = (int) $checkQuery->fetchColumn();

        if ($exists > 0) {
            echo json_encode(['statut' => false, 'message' => 'Email already exists']);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $confirmationCode = rand(100000, 999999);

        // Envoyer l'email de confirmation
        $mail = new Mail($email, "Votre code de confirmation est : $confirmationCode", "Confirmation de votre inscription");
        if (!$mail->send()) {
            echo json_encode(['statut' => false, 'message' => 'Failed to send confirmation email']);
            return;
        }

        $insertQuery = $bdd->prepare('INSERT INTO users (name, email, password, accreditation, codeConfirm) VALUES (?, ?, ?, ?, ?)');
        $result = $insertQuery->execute([
            $name,
            $email,
            $hashedPassword,
            0,
            $confirmationCode
        ]);

        if ($result) {
            // Enregistrer le code de confirmation dans la session
            session_start();
            $_SESSION['confirmation_code'] = $confirmationCode;
            $_SESSION['email'] = $email;

            echo json_encode(['statut' => true, 'message' => 'User registered successfully. Please check your email for the confirmation code.']);
        } else {
            echo json_encode(['statut' => false, 'message' => 'Failed to register user']);
        }
    }

    /**
     * Confirmation de l'utilisateur
     */
    public function confirm() {
        session_start();
        $confirmationCode = $_POST['confirmation_code'] ?? null;
        $email = $_SESSION['email'] ?? null;

        if (!$confirmationCode || !$email) {
            echo json_encode(['statut' => false, 'message' => 'Missing confirmation code or email']);
            return;
        }

        if ($confirmationCode != $_SESSION['confirmation_code']) {
            echo json_encode(['statut' => false, 'message' => 'Invalid confirmation code']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $updateQuery = $bdd->prepare('UPDATE users SET accreditation = 1 WHERE email = ?');
        $result = $updateQuery->execute([$email]);

        if ($result) {
            echo json_encode(['statut' => true, 'message' => 'User confirmed successfully']);
        } else {
            echo json_encode(['statut' => false, 'message' => 'Failed to confirm user']);
        }
    }

    /**
     * Connexion de l'utilisateur
     */
    public function login() {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            echo json_encode(['statut' => false, 'message' => 'Missing email or password']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT * FROM users WHERE email = ?');
        $query->execute([$email]);
        $user = $query->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            echo json_encode(['statut' => false, 'message' => 'Invalid email or password']);
            return;
        }

        // Ajouter les données personnelles dans la session
        session_start();
        $_SESSION['id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['accreditation'] = $user['accreditation'];

        // Ajouter l'email et le mot de passe dans les cookies
        setcookie('email', $email, time() + (86400 * 30), "/"); // 30 jours
        setcookie('password', $password, time() + (86400 * 30), "/"); // 30 jours

        echo json_encode(['statut' => true, 'message' => 'User logged in successfully']);
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout() {
        session_start();
        session_unset();
        session_destroy();

        // Supprimer les cookies
        setcookie('email', '', time() - 3600, "/");
        setcookie('password', '', time() - 3600, "/");

        echo json_encode(['statut' => true, 'message' => 'User logged out successfully']);
    }

    /**
     * Connexion automatique de l'utilisateur via les cookies
     */
    public function autoLogin() {
        if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
            $email = $_COOKIE['email'];
            $password = $_COOKIE['password'];

            $bdd = GetPDO::getpdo();
            $query = $bdd->prepare('SELECT * FROM users WHERE email = ?');
            $query->execute([$email]);
            $user = $query->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Ajouter les données personnelles dans la session
                session_start();
                $_SESSION['id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['accreditation'] = $user['accreditation'];
            }
        }
    }
}
?>
