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
            echo json_encode(['statut' => false, 'message' => 'Missing required fields', "code" => 400]);
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
        var_dump($response);exit();
        if (!$response || $response === null) {
            echo json_encode(['statut' => false, 'message' => 'Failed to connect to authentication server', 'code' => 500]); 
            return;
        }
 
        $response = json_decode($response,true); 
        if($response['statut']  === false){
            echo json_encode($response);
            return;
        } 
        
        //if everything gone right
        
        $userData = $response['data'];  
        $_SESSION["id"] = $userData["id"];
        $_SESSION["name"] = $userData["name"];
        $_SESSION["email"] = $userData["email"];
        $_SESSION["accreditation"] = $userData["accreditation"];
        $_SESSION["avatar"] = $userData["avatar"]; 
 
        echo json_encode($response);
        return; 
        
    }

    /**
     * Confirmation de l'utilisateur
     */
    public function confirm() { 
        $confirmationCode = $_POST['confirmation_code'] ?? null;
        $email = $_SESSION['email'] ?? null;
 
        if (!$confirmationCode || !$email) {   
            echo json_encode(['statut' => false, 'message' => 'Missing confirmation code or email', 'code' => 400]);
            return;
        }

        // request curl to http://accrodev/api/confirm in post for confirm

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["AUTH_HOST"] .  "/api/confirmUser" ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "email=$email&confirmation_code=$confirmationCode",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl); 

        if (!$response || $response === null) {
            echo json_encode(['statut' => false, 'message' => 'Failed to connect to authentication server', 'code' => 500]); 
            return;
        }
        
        $response = json_decode($response,true);
        if($response['statut']  === false){
            echo json_encode($response);
            return;
        }

        //if everything gone right

        $userData = $response['data'];
        $_SESSION["id"] = $userData["id"];
        $_SESSION["name"] = $userData["name"];
        $_SESSION["email"] = $userData["email"];
        $_SESSION["accreditation"] = $userData["accreditation"];
        $_SESSION["avatar"] = $userData["avatar"];

        echo json_encode($response);
        return; 
    }

    /**
     * Connexion de l'utilisateur
     */
    public function login() {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            echo json_encode(['statut' => false, 'message' => 'Missing email or password', 'code' => 400]);
            return;
        }

        // request curl to http://accrodev/api/login in post for login

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["AUTH_HOST"] .  "/api/other/login" ,
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

        if (!$response || $response === null) {
            echo json_encode(['statut' => false, 'message' => 'Failed to connect to authentication server', 'code' => 500]); 
            return;
        }

        $response = json_decode($response,true);
        if($response['statut']  === false){
            echo json_encode($response);
            return;
        }

        //if everything gone right

        $userData = $response['data'];
        $_SESSION["id"] = $userData["id"];
        $_SESSION["name"] = $userData["name"];
        $_SESSION["email"] = $userData["email"];
        $_SESSION["accreditation"] = $userData["accreditation"];
        $_SESSION["avatar"] = $userData["avatar"]; 

        // Ajouter l'email et le mot de passe dans les cookies
        setcookie('email', $email, time() + (86400 * 30), "/"); // 30 jours
        setcookie('password', $password, time() + (86400 * 30), "/"); // 30 jours

        echo json_encode([
            'statut' => true, 
            "code" => 200,
            'message' => 'User logged in successfully', 
            "id" => $userData["id"],
            "name" => $userData["name"],
            "email" => $userData["email"],
            "accreditation" => $userData["accreditation"], 
            "date" => date("d-m-Y",(int) $userData["date"]) ,  
            "avatar" => $userData["avatar"] && $userData["avatar"] !== '' ? $userData["avatar"] : '/Views/img/avatar/avatar.webp'
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
            echo json_encode(['statut' => false, 'message' => 'Missing email', 'code' => 400]);
            return;
        }

        // request curl to http://accrodev/api/forgotPassword in post for forgotPassword

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["AUTH_HOST"] .  "/api/forgotPassword" ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "email=$email",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response || $response === null) {
            echo json_encode(['statut' => false, 'message' => 'Failed to connect to authentication server', 'code' => 500]);
            return;
        }

        echo $response; 
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
        
        // request curl to http://accrodev/api/resetPassword in post for resetPassword

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["AUTH_HOST"] .  "/api/resetPassword" ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "email=$email&confirmation_code=$confirmationCode&new_password=$newPassword",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response || $response === null) {
            echo json_encode(['statut' => false, 'message' => 'Failed to connect to authentication server', 'code' => 500]);
            return;
        }

        $response = json_decode($response,true);

        if ($response['statut'] === true) {
            # create his session

            $userData = $response['data'];

            $_SESSION["id"] = $userData["id"];
            $_SESSION["name"] = $userData["name"];
            $_SESSION["email"] = $userData["email"];
            $_SESSION["accreditation"] = $userData["accreditation"];
            $_SESSION["avatar"] = $userData["avatar"];

            // Ajouter l'email et le mot de passe dans les cookies
            setcookie('email', $email, time() + (86400 * 30), "/"); // 30 jours
            setcookie('password', $newPassword, time() + (86400 * 30), "/"); // 30 jours

            echo json_encode([
                'statut' => true, 
                'message' => 'Password reset successfully',
                "id" => $userData["id"],
                "name" => $userData["name"],
                "email" => $userData["email"],
                "accreditation" => $userData["accreditation"], 
                "date" => date("d-m-Y", strtotime($userData["date"])) ,  
                "avatar" => $userData["avatar"] && $userData["avatar"] !== '' ? $userData["avatar"] : '/Views/img/avatar/avatar.webp'
            ]);
            return;
        }

        echo json_encode($response); 
    } 

    public function premiumSubscribe() 
    { 
        $phone = $_GET["phone"] ?? null;
        $additionalInfo = $_GET['additionalInfo'] ?? null;

        if (!$phone || $phone === null) {
            echo json_encode([ 'statut' => false, 'message' => 'Le numéro est requis', 'code' => 400 ]);
            return;
        }

        $bdd = GetPDO::getpdo();
        	

        $reqest = $bdd->prepare('INSERT INTO subscribe (User_id,Phone,AdditionalInfo,Date) VALUES (?,?,?,?)');
        $reqest->execute([isset($_SESSION["id"]) ? $_SESSION["id"] : null, $phone, $additionalInfo, time()]);
        
        $res = $reqest->fetch();

        echo json_encode([ 'statut' => true, 'message' => 'Donnée engregistré', 'code' => 200 ]);
        return;
    }
}
?>
