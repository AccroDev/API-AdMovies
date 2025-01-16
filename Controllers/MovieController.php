<?php
namespace Controllers;

use Models\GetPDO; 
use PDO;

class MovieController {
    /**
     * Récupère tous les films dans la base de données avec pagination
     */
    public static function getMovies() {
        
        $userId = $_SESSION['id'] ?? null;

        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $bdd = GetPDO::getpdo();
        $query = 'SELECT id, titre, miniature, description, contenu, categori, date FROM movies ORDER BY id DESC LIMIT :limit OFFSET :offset';
        $stmt = $bdd->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

            // Vérifier si l'utilisateur actuel a voté pour cette série durant les 3 derniers mois
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
     * Récupère les données d'un film spécifique
     * @param array $params - Paramètres de la route
     */
    public static function getMovieById($params) {
        $movieId = $params['id'] ?? null;

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

        echo json_encode($formattedResult);
    }
}
?>
