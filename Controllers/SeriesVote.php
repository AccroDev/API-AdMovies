<?php
namespace Controllers;

use Models\GetPDO;
use Dotenv\Dotenv;

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
        session_start();
        $_SESSION['id'] = 1; // à supprimer
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
        $bdd = GetPDO::getpdo();
        $threeMonthsAgo = time() - (3 * 30 * 24 * 60 * 60); // Timestamp pour trois mois

        $query = $bdd->prepare('SELECT movie_id, COUNT(*) as vote_count FROM votes WHERE date >= ? GROUP BY movie_id ORDER BY vote_count DESC LIMIT 20');
        $query->execute([$threeMonthsAgo]);
        $topMovies = $query->fetchAll();

        $formattedResults = [];
        foreach ($topMovies as $movie) {
            $movieQuery = $bdd->prepare('SELECT id, titre, miniature, description, contenu FROM movies WHERE id = ?');
            $movieQuery->execute([$movie['movie_id']]);
            $movieDetails = $movieQuery->fetch();

            if ($movieDetails) {
                $contenu = json_decode($movieDetails['contenu'], true);
                $formattedResults[] = [
                    'id' => $movieDetails['id'],
                    'titre' => $movieDetails['titre'],
                    'miniature' => $movieDetails['miniature'],
                    'description' => $movieDetails['description'],
                    'votes' => $movie['vote_count'],
                    'vote' => $contenu['vote_average']
                ];
            }
        }

        echo json_encode($formattedResults);
    }
}
?>
