<?php
namespace Controllers;

use Models\GetPDO;
use PDO;

class Commentaires {

    public static function postComment() {
        
        $bdd = GetPDO::getpdo(); 
        $userId = $_SESSION['id'] ?? null;
        $nom = $_POST['nom'] ?? null;
        $commentaire = $_POST['message'] ?? null;
        $movieId = $_POST['movie_id'] ?? null;
        $niveau = $_POST['niveau'] ?? 0;

        if (!$commentaire || !$movieId) {
            echo json_encode(['statut' => false, 'message' => 'Commentaire ou ID du film manquant', 'code' => 400]);
            return;
        }

        if (!$userId && !$nom) {
            echo json_encode(['statut' => false, 'message' => 'Nom manquant pour les utilisateurs non connectés', 'code' => 400]);
            return;
        }

        $avatar = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = '/Views/img/avatar/';
            $uploadFile = $uploadDir . basename($_FILES['avatar']['name']);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'],dirname(__DIR__) .  $uploadFile)) {
                $avatar = $uploadFile;
            } 
        }

        $query = $bdd->prepare('INSERT INTO commentaires (User_id, Movie_id, Name, Avatar, Message, Data, Niveau) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$userId, $movieId, $nom, $avatar, $commentaire, time(), $niveau]);

        echo json_encode(['statut' => true, 'message' => 'Commentaire ajouté avec succès', 'code' => 200, 'id' => $bdd->lastInsertId()]);
    }

    public function getComments($params) {
        $movieId = $params['id'] ?? null;
        $AllUserData = [];

        if (!$movieId) {
            echo json_encode(['statut' => false, 'message' => 'ID du film manquant', 'code' => 400]);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT * FROM commentaires WHERE movie_id = ? AND Niveau = 0 ORDER BY id DESC');
        $query->execute([$movieId]);
        $comments = $query->fetchAll(PDO::FETCH_ASSOC);

        $days = [ 'Dim','Lun', 'Mar', 'Mer','Jeu', 'Ven', 'Sam'];
        

        $formattedComments = [];
        foreach ($comments as $comment) { 

            $commentId = $comment["id"];
            // select all comment when niveaux == this id

            $responseQuery = $bdd->prepare('SELECT * FROM commentaires WHERE movie_id = ? AND Niveau = ? ORDER BY id DESC');
            $responseQuery->execute([$movieId,$commentId]);
            $resp = $responseQuery->fetchAll(PDO::FETCH_ASSOC);
            $responseComments = [];

            foreach ($resp as $responseCommentaire) {

                $responseName = $responseCommentaire['Name'] ;
                $responseAvatar = $responseCommentaire['Avatar'];
    
                if (isset($responseCommentaire['User_id']) && $responseCommentaire['User_id'] !== "" ) {
                    if (isset($AllUserData[(int) $responseCommentaire['User_id']])) {
                       
                        $responseName = $AllUserData[(int) $responseCommentaire['User_id']]['name'] . ' ' . $AllUserData[(int) $responseCommentaire['User_id']]['postnom'] ;
                        $responseAvatar = $AllUserData[(int) $responseCommentaire['User_id']]['avatar'];
    
                    }else { 
                        $userDataResponse = $this->getUserData((int) $responseCommentaire['User_id']);
                        if ($userDataResponse !== false) {

                            $AllUserData[(int) $responseCommentaire['User_id']] =  $userDataResponse; 
                            $responseName = $userDataResponse['name'] . ' ' . $userDataResponse['postnom'] ;
                            $responseAvatar = $userDataResponse['avatar'];
                        }
                    }
                }
                $responseComments[] = [
                    "id" => $responseCommentaire["id"],
                    'name' => $responseName,
                    'avatar' => !isset($responseAvatar) || $responseAvatar === '' ? '/Views/img/avatar/avatar.webp' : $responseAvatar  ,
                    'date' => $days[(int) date("w",$responseCommentaire['Data'])]  . ', Le ' . date('d-m-Y', $responseCommentaire['Data']),
                    'message' => $responseCommentaire['Message'],
                    'reponse' => []  
                ];
            }

            $name = $comment['Name'] ;
            $avatar = $comment['Avatar'];

            if (isset($comment['User_id']) && $comment['User_id'] !== "" ) {
                if (isset($AllUserData[(int) $comment['User_id']])) {
                   
                    $name = $AllUserData[(int) $comment['User_id']]['name'] . ' ' . $AllUserData[(int) $comment['User_id']]['postnom'] ;
                    $avatar = $AllUserData[(int) $comment['User_id']]['avatar'];

                }else { 
                    $userData = $this->getUserData((int) $comment['User_id']);
                    if ($userData !== false) {
                        $AllUserData[(int) $comment['User_id']] =  $userData; 
                        $name = $userData['name'] . ' ' . $userData['postnom'] ;
                        $avatar = $userData['avatar'];
                    }
                }
            }
            $formattedComments[] = [
                "id" => $commentId,
                'name' => $name ,
                'avatar' => !isset($avatar) || $avatar === '' ? '/Views/img/avatar/avatar.webp' : $avatar ,
                'date' => $days[(int) date("w",$comment['Data'])]  . ', Le ' . date('d-m-Y', $comment['Data']),
                'message' => $comment['Message'],
                'reponse' => $responseComments  // Assuming no replies for now
            ];
 
        }

        echo json_encode($formattedComments);
    }

    public function getUserData(int $id)
    { 

        if (!$id) { 
            return false;
        }

        // request curl to http://accrodev/api/login in post for login

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["AUTH_HOST"] .  "/api/getUserData" ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "id=$id",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl); 

        if (!$response || $response === null) { 
            return false ;
        }

        $response = json_decode($response,true);
        if($response['statut']  === false){ 
            return false ;
        }

        //if everything gone right 
        return $response['data'];  
    }
}
?>
