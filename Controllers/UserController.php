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
        $checkQuery = $bdd->prepare('SELECT * FROM users WHERE email = ?');
        $checkQuery->execute([$email]);
        $user = $checkQuery->fetch();

        if ($user) {
            if ($user['accreditation'] > 0) {
                echo json_encode(['statut' => false, 'message' => 'Email already exists']);
                return;
            } else {
                // Mettre à jour les données de l'utilisateur non validé
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $confirmationCode = rand(100000, 999999);

                $updateQuery = $bdd->prepare('UPDATE users SET name = ?, password = ?, codeConfirm = ? WHERE email = ?');
                $result = $updateQuery->execute([$name, $hashedPassword, $confirmationCode, $email]);

                if ($result) {
                    // Envoyer l'email de confirmation
                    $mail = new Mail($email, "Votre code de confirmation est : $confirmationCode", "Confirmation de votre inscription");
                    if (!$mail->send()) {
                        echo json_encode(['statut' => false, 'message' => 'Failed to send confirmation email']);
                        return;
                    }

                    // Enregistrer le code de confirmation dans la session 
                    $_SESSION['confirmation_code'] = $confirmationCode;
                    $_SESSION['email'] = $email;
                    $_SESSION['password'] = $password;

                    echo json_encode(['statut' => true, 'message' => 'User registered successfully. Please check your email for the confirmation code.']);
                } else {
                    echo json_encode(['statut' => false, 'message' => 'Failed to update user']);
                }
                return;
            }
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
            $_SESSION['confirmation_code'] = $confirmationCode;
            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;

            echo json_encode(['statut' => true, 'message' => 'User registered successfully. Please check your email for the confirmation code.']);
        } else {
            echo json_encode(['statut' => false, 'message' => 'Failed to register user']);
        }
    }

    /**
     * Confirmation de l'utilisateur
     */
    public function confirm() { 
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

        //get user data
        $selectQuery = $bdd->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $selectQuery->execute([$email]);
        $selectResult = $selectQuery->fetch();

        if ($result) {

            // Ajouter les données personnelles dans la session 
            $_SESSION['id'] = $selectResult['id'];
            $_SESSION['name'] = $selectResult['name'];
            $_SESSION['email'] = $selectResult['email'];
            $_SESSION['accreditation'] = $selectResult['accreditation'];

            // Ajouter l'email et le mot de passe dans les cookies
            setcookie('email', $email, time() + (86400 * 30), "/"); // 30 jours
            isset($_SESSION['password']) ? setcookie('password', $_SESSION['password'], time() + (86400 * 30), "/") : ''; // 30 jours

            echo json_encode([
                'statut' => true, 
                'message' => 'User confirmed successfully',
                "id" => $selectResult["id"],
                "name" => $selectResult["name"],
                "email" => $selectResult["email"],
                "accreditation" => $selectResult["accreditation"], 
                "date" => date("d-m-Y", strtotime($selectResult["date"])),  
                "avatar" => $selectResult["avatar"] && $selectResult["avatar"] !== '' ? $selectResult["avatar"] : '/Views/img/avatar/avatar.jpg'
            ]);

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
        $_SESSION['id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['accreditation'] = $user['accreditation'];

        // Ajouter l'email et le mot de passe dans les cookies
        setcookie('email', $email, time() + (86400 * 30), "/"); // 30 jours
        setcookie('password', $password, time() + (86400 * 30), "/"); // 30 jours

        echo json_encode([
            'statut' => true, 
            'message' => 'User logged in successfully',
            "id" => $user["id"],
            "name" => $user["name"],
            "email" => $user["email"],
            "accreditation" => $user["accreditation"], 
            "date" => date("d-m-Y", strtotime($user["date"])),  
            "avatar" => $user["avatar"] && $user["avatar"] !== '' ? $user["avatar"] : '/Views/img/avatar/avatar.jpg'
        ]);
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout() { 
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
                $_SESSION['id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['accreditation'] = $user['accreditation'];
            }
        }
    }

    /**
     * Mot de passe oublié
     */
    public function forgotPassword() {
        $email = $_POST['email'] ?? null;

        if (!$email) {
            echo json_encode(['statut' => false, 'message' => 'Missing email']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT * FROM users WHERE email = ?');
        $query->execute([$email]);
        $user = $query->fetch();

        if (!$user) {
            echo json_encode(['statut' => false, 'message' => 'Email not found']);
            return;
        }

        $confirmationCode = rand(100000, 999999);
        $updateQuery = $bdd->prepare('UPDATE users SET codeConfirm = ? WHERE email = ?');
        $result = $updateQuery->execute([$confirmationCode, $email]);

        if ($result) {
            // Envoyer l'email de confirmation
            $mail = new Mail($email, "Votre code de réinitialisation est : $confirmationCode", "Réinitialisation de votre mot de passe");
            if (!$mail->send()) {
                echo json_encode(['statut' => false, 'message' => 'Failed to send confirmation email']);
                return;
            }

            echo json_encode(['statut' => true, 'message' => 'Confirmation code sent to your email']);
        } else {
            echo json_encode(['statut' => false, 'message' => 'Failed to update user']);
        }
    }

    /**
     * Réinitialisation du mot de passe
     */
    public function resetPassword() {
        $confirmationCode = $_POST['confirmation_code'] ?? null;
        $newPassword = $_POST['new_password'] ?? null;
        $email = $_POST['email'] ?? null;

        if (!$confirmationCode || !$newPassword || !$email) {
            echo json_encode(['statut' => false, 'message' => 'Missing required fields']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT * FROM users WHERE email = ? AND codeConfirm = ?');
        $query->execute([$email, $confirmationCode]);
        $user = $query->fetch();

        if (!$user) {
            echo json_encode(['statut' => false, 'message' => 'Invalid confirmation code or email']);
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateQuery = $bdd->prepare('UPDATE users SET password = ?, codeConfirm = NULL WHERE email = ?');
        $result = $updateQuery->execute([$hashedPassword, $email]);

        if ($result) {

            // Ajouter les données personnelles dans la session 
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['accreditation'] = $user['accreditation'];

            // Ajouter l'email et le mot de passe dans les cookies
            setcookie('email', $email, time() + (86400 * 30), "/"); // 30 jours
            setcookie('password', $newPassword, time() + (86400 * 30), "/"); // 30 jours


            echo json_encode([
                'statut' => true, 
                'message' => 'Password reset successfully',
                "id" => $user["id"],
                "name" => $user["name"],
                "email" => $user["email"],
                "accreditation" => $user["accreditation"], 
                "date" => date("d-m-Y", strtotime($user["date"])) ,  
                "avatar" => $user["avatar"] && $user["avatar"] !== '' ? $user["avatar"] : '/Views/img/avatar/avatar.jpg'
            ]);
        } else {
            echo json_encode(['statut' => false, 'message' => 'Failed to reset password']);
        }
    } 
}
?>
