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

        // request curl to http://accrodev/api/signin in post for signin

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["AUTH_HOST"] .  "/api/signin" ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "name=$name&email=$email&password=$password",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);
   
        //if status == true  ==> if the user is registered
        if($response['statut'] == true){
            $_SESSION["id"]= $response['data']['id'];
            $_SESSION["name"]= $response['data']['name'];
            $_SESSION["email"]= $response['data']['email'];
            $_SESSION["accreditation"]= $response['data']['accreditation'];
            $_SESSION["avatar"]= $response['data']['avatar'];
            $_SESSION["pays"]= $response['data']['pays'];
            $_SESSION["devise"]= $response['data']['devise'];
            echo json_encode($response);
            return;
        }
        //if status == false  ==> if the user email already exists,400: Champs requis manquants, 500: Erreur interne du serveur,500: Erreur interne du serveur
        if($response['statut'] == false){
            echo json_encode($response);
            return;
        }
 
    }

    /**
     * Confirmation de l'utilisateur
     */
   
    public function confirm() { 
        $confirmationCode = $_POST['confirmation_code'] ?? null;
        $email = $_SESSION['email'] ?? null;

        if (!$confirmationCode || !$email) {
            echo json_encode(['statut' => false, 'message' => 'Code de confirmation ou email manquant']);
            return;
        }

        // Initialiser la requête cURL vers le service d'authentification
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["AUTH_HOST"] . "/api/confirmUser",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query(['confirmation_code' => $confirmationCode, 'email' => $email]),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === false) {
            echo json_encode(['statut' => false, 'message' => 'Échec de la connexion au service d\'authentification', 'code' => 500]);
            return;
        }

        $response = json_decode($response, true);

        if ($confirmationCode != $_SESSION['confirmation_code']) {
            echo json_encode(['statut' => false, 'message' => 'Code de confirmation invalide']);
            return;
        }

        // Supposons que $result est obtenu à partir de la réponse
        $result = $response['statut'] ?? false;

        if ($result) {
            $selectResult = $response['data'];

            // Stocker les données de l'utilisateur dans la session
            $_SESSION['id'] = $selectResult['id'];
            $_SESSION['name'] = $selectResult['name'];
            $_SESSION['email'] = $selectResult['email'];
            $_SESSION['accreditation'] = $selectResult['accreditation'];

            // Ajouter l'email et le mot de passe dans les cookies
            setcookie('email', $email, time() + (86400 * 30), "/"); // 30 jours
            isset($_SESSION['password']) ? setcookie('password', $_SESSION['password'], time() + (86400 * 30), "/") : ''; // 30 jours

            echo json_encode([
                'statut' => true, 
                'message' => 'Utilisateur confirmé avec succès',
                "id" => $selectResult["id"],
                "name" => $selectResult["name"],
                "email" => $selectResult["email"],
                "accreditation" => $selectResult["accreditation"], 
                "date" => date("d-m-Y", strtotime($selectResult["date"])),  
                "avatar" => $selectResult["avatar"] && $selectResult["avatar"] !== '' ? $selectResult["avatar"] : '/Views/img/avatar/avatar.jpg'
            ]);

        } else {
            echo json_encode(['statut' => false, 'message' => 'Échec de la confirmation de l\'utilisateur']);
        }
    }

    /**
     * Connexion de l'utilisateur
     */
    public function login() {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            echo json_encode(['statut' => false, 'message' => 'Missing required fields',"code"=> 400]);
            return;
        }

        // request curl to http://accrodev/api/login in post for signin

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["AUTH_HOST"] .  "/api/login" ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "email=$email&password=$password",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);
        
            
       
        
        if( $response['statut'] == true){
            $user = $response['data']; 
            // Ajouter les données personnelles dans la session 
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['accreditation'] = $user['accreditation'];

            // Ajouter l'email et le mot de passe dans les cookies
            setcookie('email', $email, time() + (86400 * 30), "/"); // 30 jours
            setcookie('password', $password, time() + (86400 * 30), "/"); // 30 jours
            echo json_encode($response);
         
        }
        
        if($response['code']==404){
            echo json_encode(['statut' => false, 'message' => 'User not found',"code"=>  404]);
        }
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
