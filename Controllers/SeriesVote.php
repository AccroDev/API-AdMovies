<?php
namespace Controllers;

use Models\GetPDO;
use Dotenv\Dotenv;
use PDO;

class SeriesVote {
    public function __construct() {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
    }

    /**
     * Vote ou dévote pour une série
     * @param array $params - Paramètres de la route
     */
    public function voteOrDevote($params) { 
        $userId = $_SESSION['id'] ?? null;
        $seriesId = $params['id'] ?? null;

        if (!$userId || !$seriesId) {
            echo json_encode(['statut' => false, 'message' => 'Missing user ID or series ID']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $threeMonthsAgo = time() - (3 * 30 * 24 * 60 * 60); // Timestamp pour trois mois

        // Vérifier si l'utilisateur a déjà voté pour cette série dans les trois derniers mois
        $checkQuery = $bdd->prepare('SELECT id FROM votes WHERE user_id = ? AND movie_id = ? AND date >= ?');
        $checkQuery->execute([$userId, $seriesId, $threeMonthsAgo]);
        $vote = $checkQuery->fetch();

        if ($vote) {
            // Supprimer le vote
            $deleteQuery = $bdd->prepare('DELETE FROM votes WHERE id = ?');
            $result = $deleteQuery->execute([$vote['id']]);

            if ($result) {
                echo json_encode(['statut' => true, 'message' => 'Vote removed successfully']);
            } else {
                echo json_encode(['statut' => false, 'message' => 'Failed to remove vote']);
            }
        } else {
            // Ajouter le vote
            $insertQuery = $bdd->prepare('INSERT INTO votes (user_id, movie_id, date) VALUES (?, ?, ?)');
            $result = $insertQuery->execute([$userId, $seriesId, time()]);

            if ($result) {
                echo json_encode(['statut' => true, 'message' => 'Vote added successfully']);
            } else {
                echo json_encode(['statut' => false, 'message' => 'Failed to add vote']);
            }
        }
    }

    /**
     * Récupère les 20 films les plus recommandés durant les 3 derniers mois
     */
    public function getTopRecommended() {
        $userId = $_SESSION['id'] ?? null;

        $bdd = GetPDO::getpdo();
        $threeMonthsAgo = time() - (3 * 30 * 24 * 60 * 60); // Timestamp pour trois mois

        $query = $bdd->prepare('SELECT movie_id, COUNT(*) as vote_count FROM votes WHERE date >= ? GROUP BY movie_id ORDER BY vote_count DESC LIMIT 20');
        $query->execute([$threeMonthsAgo]);
        $topMovies = $query->fetchAll();

        $formattedResults = [];
        foreach ($topMovies as $movie) {
            $movieQuery = $bdd->prepare('SELECT id, titre, miniature, description, contenu, categori, date FROM movies WHERE id = ?');
            $movieQuery->execute([$movie['movie_id']]);
            $movieDetails = $movieQuery->fetch();

            if ($movieDetails) {
                $contenu = json_decode($movieDetails['contenu'], true);

                // Récupérer le nombre de votes durant les 3 derniers mois
                $likeCountQuery = $bdd->prepare('SELECT COUNT(*) as vote_count FROM votes WHERE movie_id = ? AND date >= ?');
                $likeCountQuery->execute([$movieDetails['id'], $threeMonthsAgo]);
                $likeCount = $likeCountQuery->fetchColumn();

                // Récupérer le nombre total de votes
                $totalLikeQuery = $bdd->prepare('SELECT COUNT(*) as total_vote_count FROM votes WHERE movie_id = ?');
                $totalLikeQuery->execute([$movieDetails['id']]);
                $totalLike = $totalLikeQuery->fetchColumn();

                // Vérifier si l'utilisateur actuel a voté pour cette série durant les 3 derniers mois
                $userLikeQuery = $bdd->prepare('SELECT COUNT(*) as user_vote_count FROM votes WHERE movie_id = ? AND user_id = ? AND date >= ?');
                $userLikeQuery->execute([$movieDetails['id'], $userId, $threeMonthsAgo]);
                $userLike = $userLikeQuery->fetchColumn() > 0;

                $formattedResults[] = [
                    'id' => $movieDetails['id'],
                    'titre' => $movieDetails['titre'],
                    'miniature' => $movieDetails['miniature'],
                    'description' => $movieDetails['description'],
                    'vote' => $contenu['vote_average'],
                    'like' => $userLike,
                    'likeCount' => (int) $likeCount,
                    'totLike' => (int) $totalLike,
                    "type" => $movieDetails['categori'],
                    "date" => date('d-m-Y', strtotime($movieDetails['date']))
                ];
            }
        }

        // Compléter les résultats avec des films ayant zéro vote si nécessaire
        if (count($formattedResults) < 20) {
            $remaining = 20 - count($formattedResults);
            $additionalQuery = $bdd->prepare('SELECT id, titre, miniature, description, contenu, categori, date FROM movies WHERE id NOT IN (SELECT movie_id FROM votes WHERE date >= :datelimit) LIMIT :remaining');
            $additionalQuery->bindValue(':datelimit', $threeMonthsAgo, PDO::PARAM_INT);
            $additionalQuery->bindValue(':remaining', $remaining, PDO::PARAM_INT);
            $additionalQuery->execute();
            $additionalMovies = $additionalQuery->fetchAll();
            foreach ($additionalMovies as $movie) {
                $contenu = json_decode($movie['contenu'], true);
                $formattedResults[] = [
                    'id' => $movie['id'],
                    'titre' => $movie['titre'],
                    'miniature' => $movie['miniature'],
                    'description' => $movie['description'],
                    'vote' => $contenu['vote_average'],
                    'like' => false,
                    'likeCount' => 0,
                    'totLike' => 0,
                    "type" => $movie['categori'],
                    "date" => date('d-m-Y', strtotime($movie['date']))
                ];
            }
        }

        echo json_encode($formattedResults);
    }
}
?>
