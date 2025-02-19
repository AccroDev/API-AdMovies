<?php
namespace Controllers;

use Models\GetPDO; 
use PDO;

class MovieController {

    public static function getMovieWithVille($ville,$type,$onlyAvailable,$limit,$offset) 
    {
        
        $bdd = GetPDO::getpdo();  
        if ($onlyAvailable === 'true') {   
            // get shop.id from this ville
            $stmt = $bdd->prepare('SELECT id FROM shops WHERE ville = :ville');
            $stmt->execute([':ville' => $ville]);  
            $shops = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!$shops || empty($shops)) {
                echo json_encode([]); 
                return;
            }

            // get movies.id from this shop 
            $shopsFound = implode(',',array_map('intval', $shops)); 

            $stmt = $bdd->prepare('SELECT movie_id FROM shop_movies WHERE shop_id IN (' . $shopsFound . ')');
            $stmt->execute();
            $movies = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!$movies || empty($movies)) {
                echo json_encode([]); 
                return;
            }

            $setCateg = '';
            if ($type) {
                $setCateg = "AND categori = '$type'";
            }

            // get movies details
            $movieFound = implode(',',array_map('intval', $movies)) ;
            $query = "SELECT id, titre, miniature, description, contenu, categori, date FROM movies WHERE id IN (" . $movieFound . ") ". $setCateg ." ORDER BY id DESC LIMIT $limit OFFSET $offset";

            $stmt = $bdd->query($query); 
            $moviesDet = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
            // echo result like json
            $result =  self::formatedResult($moviesDet,$_SESSION['id'] ?? null);
            echo json_encode($result); 
        }else {

            // select all movie limit is limit and offset is offset order by id desc 
            $setCateg = '';
            if ($type && $type !== '') {
                $setCateg = " WHERE categori = '$type'";
            }
            $sql = "SELECT id, titre, miniature, description, contenu, categori, date FROM movies ". $setCateg ." ORDER BY id DESC LIMIT $limit OFFSET $offset";  
            $stmt = $bdd->prepare($sql); 
            $stmt->execute();
            $moviesDet = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // get all shop.id where from this ville params

            $vReq = $bdd->prepare("SELECT id FROM shops WHERE ville = :ville");
            $vReq->execute([':ville' => $ville]);
            $allVillId = $vReq->fetchAll(PDO::FETCH_ASSOC);

            $villeIdArray = implode(',',array_map(fn($row) => $row['id'], $allVillId));

            foreach ($moviesDet as $movie) {

                $movieId = $movie['id']; 
                $shops = []; 

                if ($allVillId && !empty($allVillId)) {  

                    $stmt = $bdd->prepare('SELECT shop_id FROM shop_movies WHERE movie_id = :movieId AND shop_id IN (' . $villeIdArray  . ')'); 
                    $stmt->execute([':movieId' => $movieId]);
                    $shops = $stmt->fetchAll(PDO::FETCH_COLUMN); 
                }
                
                $key = array_search($movie, $moviesDet); 
                $moviesDet[$key]['match'] = empty($shops) ? false : true; 
            }

            // echo result like json
            $result =  self::formatedResult($moviesDet,$_SESSION['id'] ?? null);
            echo json_encode($result); 
        }
    }

    public static function formatedResult ($movies,$userId)
    {
        $threeMonthsAgo = time() - (3 * 30 * 24 * 60 * 60); // Timestamp pour trois mois

        $formattedResults = [];
        $bdd = GetPDO::getpdo();
        foreach ($movies as $movie) {
            $contenu = json_decode($movie['contenu'], true);

            // Récupérer le nombre de votes durant les 3 derniers mois
            $likeCountQuery = $bdd->prepare('SELECT COUNT(*) as vote_count FROM votes WHERE movie_id = ? AND date >= ?');
            $likeCountQuery->execute([$movie['id'], $threeMonthsAgo]);
            $likeCount = $likeCountQuery->fetchColumn();

            // Récupérer le nombre total de votes
            $totalLikeQuery = $bdd->prepare('SELECT COUNT(*) as total_vote_count FROM votes WHERE movie_id = ?');
            $totalLikeQuery->execute([$movie['id']]);
            $totalLike = $totalLikeQuery->fetchColumn();

            // Vérifier si l'utilisateur actuel a voté pour cette série durant les 3 derniers mois
            $userLike = false;
            if ($userId) { 
                $userLikeQuery = $bdd->prepare('SELECT COUNT(*) as user_vote_count FROM votes WHERE movie_id = ? AND user_id = ? AND date >= ?');
                $userLikeQuery->execute([$movie['id'], $userId, $threeMonthsAgo]);
                $userLike = $userLikeQuery->fetchColumn() > 0;
            }

            $itemToAdd = [
                'id' => $movie['id'],
                'titre' => $movie['titre'],
                'miniature' => $movie['miniature'],
                'description' => $movie['description'],
                'vote' => $contenu['vote_average'],
                'like' => $userLike,
                'likeCount' => (int) $likeCount,
                'totLike' => (int) $totalLike,
                "type" => $movie['categori'],
                'date' => date('d-m-Y', strtotime($movie['date'])), 
            ];
            if (isset($movie['match'])) {
                $itemToAdd['match'] = $movie['match'];
            }
            $formattedResults[] = $itemToAdd;
        }

        return $formattedResults;
    }

    public static function getMovieWithShop($type,$onlyAvailable, $shop, $limit,$offset) 
    {
        
        $bdd = GetPDO::getpdo();
        if ($onlyAvailable === 'true') {
            
            // get movies from this shop

            $setCateg = '';
            if ($type) {
                $setCateg = "AND categori = '$type'";
            }

            $sql = "SELECT movie_id FROM shop_movies WHERE shop_id = $shop ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $stmt = $bdd->query($sql); 
            $movies = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($movies)) {
                echo json_encode([]);
                return;
            }
            
            $villeIdArray = implode(',',$movies);  

            $getDer = $bdd->prepare("SELECT id, titre, miniature, description, contenu, categori, date FROM movies WHERE id IN (" . $villeIdArray . ") ". $setCateg ." ORDER BY id DESC");
            $getDer->execute();
            $moviesDet = $getDer->fetchAll(PDO::FETCH_ASSOC);

            $result =  self::formatedResult($moviesDet,$_SESSION['id'] ?? null);
            echo json_encode($result);

        }else {
 
            $setCateg = '';
            if ($type && $type !== '') {
                $setCateg = " WHERE categori = '$type'";
            }
            $sql = "SELECT id, titre, miniature, description, contenu, categori, date FROM movies ". $setCateg ." ORDER BY id DESC LIMIT $limit OFFSET $offset";  
            $stmt = $bdd->prepare($sql); 
            $stmt->execute();
            $moviesDet = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // get all ville shop
  

            $villeIdArray = ''.$shop;  

            foreach ($moviesDet as $movie) {
                $movieId = $movie['id'];
                $stmt = $bdd->prepare('SELECT shop_id FROM shop_movies WHERE movie_id = :movieId AND shop_id IN (' . $villeIdArray . ')');
                $stmt->execute([':movieId' => $movieId]);
                $shops = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $key = array_search($movie, $moviesDet); 
                $moviesDet[$key]['match'] = empty($shops) ?false : true; 
            }

            // echo result like json
            $result =  self::formatedResult($moviesDet,$_SESSION['id'] ?? null);
            echo json_encode($result); 
        }
    }
    public static function getMovieWithoutShop($type,$limit,$offset) 
    {
        $bdd = GetPDO::getpdo();

        $setCateg = '';
        if ($type) {
            $setCateg = "WHERE categori = '$type'";
        }

        //get all movie with offset and limit order by id
        $sql = "SELECT id, titre, miniature, description, contenu, categori, date FROM movies ". $setCateg ." ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $stmt = $bdd->prepare($sql);
        $stmt->execute();
        $moviesDet = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result =  self::formatedResult($moviesDet,$_SESSION['id'] ?? null);
        echo json_encode($result);

    }
    /**
     * Récupère tous les films dans la base de données avec pagination et filtres
     */
    public static function getMovies() { 

        $page = $_GET['page'] ?? 1;
        $ville = $_GET['ville'] ?? null;
        $shop = $_GET['shop'] ?? null;
        $type = $_GET['type'] ?? null;
        $onlyAvailable = $_GET['available'] ?? false;

        $limit = 20;
        $offset = ($page - 1) * $limit;

        if ($ville && !$shop) {
            self::getMovieWithVille($ville,$type,$onlyAvailable,$limit,$offset);
            return;
        }else if($shop){
            self::getMovieWithShop($type,$onlyAvailable, $shop, $limit,$offset);
            return;
        }else {
            self::getMovieWithoutShop($type,$limit,$offset);
            return;
        }  

        echo json_encode([]);
    }

    /**
     * Récupère les données d'un film spécifique
     * @param array $params - Paramètres de la route
     */
    public static function getMovieById($params) {
        $movieId = $params['id'] ?? null;
        $userId = $_SESSION['id'] ?? null;

        if (!$movieId) {
            echo json_encode(['statut' => false, 'message' => 'Missing movie ID']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT * FROM movies WHERE id = ?');
        $query->execute([$movieId]);
        $movie = $query->fetch(PDO::FETCH_ASSOC);

        if (!$movie) {
            echo json_encode(['statut' => false, 'message' => 'Movie not found']);
            return;
        }

        $contenu = json_decode($movie['contenu'], true);

        $formattedResult = [
            'id' => $movie['id'],
            'titre' => $movie['titre'],
            'miniature' => $movie['miniature'],
            'description' => $movie['description'],
            'date' => date('d-m-Y', strtotime($movie['date'])),
            'vote' => $contenu['vote_average'] ?? 00,
            'genres' => $contenu['genres'] ?? [],
            'creators' => $contenu['created_by'] ?? [],
            'seasons' => $contenu['seasons'] ?? []
        ];

        // Enregistrer l'historique de chargement de la page
        if ($userId) {
            $insertQuery = $bdd->prepare('INSERT INTO loaded (user_id, movie_id, date) VALUES (?, ?, ?)');
            $insertQuery->execute([$userId, $movieId, time()]);
        }

        echo json_encode($formattedResult);

    }

    /**
     * Récupère l'historique des films regardés par l'utilisateur connecté
     */
    public static function getHistory() {
        $userId = $_SESSION['id'] ?? null;

        if (!$userId) {
            echo json_encode([]);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT DISTINCT movie_id FROM loaded WHERE user_id = ? ORDER BY date DESC');
        $query->execute([$userId]);
        $movieIds = $query->fetchAll(PDO::FETCH_COLUMN);

        if (empty($movieIds)) {
            echo json_encode([]);
            return;
        }

        $moviesQuery = $bdd->prepare('SELECT id, titre, miniature, description, contenu, categori, date FROM movies WHERE id IN (' . implode(',', array_map('intval', $movieIds)) . ')');
        $moviesQuery->execute();
        $movies = $moviesQuery->fetchAll(PDO::FETCH_ASSOC);

        $threeMonthsAgo = time() - (3 * 30 * 24 * 60 * 60); // Timestamp pour trois mois

        $formattedResults = [];
        foreach ($movies as $movie) {
            $contenu = json_decode($movie['contenu'], true);

            // Récupérer le nombre de votes durant les 3 derniers mois
            $likeCountQuery = $bdd->prepare('SELECT COUNT(*) as vote_count FROM votes WHERE movie_id = ? AND date >= ?');
            $likeCountQuery->execute([$movie['id'], $threeMonthsAgo]);
            $likeCount = $likeCountQuery->fetchColumn();

            // Récupérer le nombre total de votes
            $totalLikeQuery = $bdd->prepare('SELECT COUNT(*) as total_vote_count FROM votes WHERE movie_id = ?');
            $totalLikeQuery->execute([$movie['id']]);
            $totalLike = $totalLikeQuery->fetchColumn();

            // Vérifier si l'utilisateur actuel a voté pour ce film durant les 3 derniers mois
            $userLikeQuery = $bdd->prepare('SELECT COUNT(*) as user_vote_count FROM votes WHERE movie_id = ? AND user_id = ? AND date >= ?');
            $userLikeQuery->execute([$movie['id'], $userId, $threeMonthsAgo]);
            $userLike = $userLikeQuery->fetchColumn() > 0;

            $formattedResults[] = [
                'id' => $movie['id'],
                'titre' => $movie['titre'],
                'miniature' => $movie['miniature'],
                'description' => $movie['description'],
                'vote' => $contenu['vote_average'],
                'like' => $userLike,
                'likeCount' => (int) $likeCount,
                'totLike' => (int) $totalLike,
                "type" => $movie['categori'],
                'date' => date('d-m-Y', strtotime($movie['date']))
            ];
        }

        echo json_encode($formattedResults);
    }

    /**
     * Récupère les villes
     */
    public function getAllVilles()
    {

        $bdd = GetPDO::getpdo(); 
        $request = $bdd->query('SELECT * FROM villes'); 
        $villes = $request->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($villes);
        
    }
}
?>
