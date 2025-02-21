<?php

    namespace Controllers;

    use Dotenv\Dotenv;

    class Download {
        public function __construct() {
            
            $dotenv = Dotenv::createImmutable(dirname(__DIR__));
            $dotenv->load();
        }

        public function getDownloadMovie()
        {
            // get id of movies and search content in Views/movies/{id} and display it in json
            $id = $_GET['id'] ?? null;

            if (!isset($id)) { 
                echo json_encode(['status' => false, 'message' => 'id is required', "code" => 400]);
                return;
            }

            $path = dirname(__DIR__) . "/Views/movies/$id";
            
            if (!is_dir($path)) {
                echo json_encode(['status' => false, 'message' => 'folder not found', "code" => 404, 'data' => [] ]);
                return;
            }

            $files = scandir($path);
            $files = array_diff($files, array('.', '..'));

            $data = [];
            foreach ($files as $file) { 

                $filePath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_file($filePath)) {
                    $sizeInBytes = filesize($filePath); // Taille en octets
                    $sizeInMB = round($sizeInBytes / (1024 * 1024), 2); // Conversion en Mo (arrondi à 2 décimales)

                    $data[] = [
                        'name' => $file,
                        'size' => $sizeInMB . ' Mo'
                    ];
                } 
            }

            echo json_encode(['status' => true, 'message' => 'success', "code" => 200, 'data' => $data ]);

 
        }

        public function download($params)
        { 
           

            if (!isset($_SESSION["accreditation"]) || $_SESSION["accreditation"] < 2) {
                http_response_code(403);
                echo json_encode(['status' => false, 'message' => 'you are not allowed to download', "code" => 403]);
                return;
            }

            $id = $params['id'] ?? null;
            $file = $params['slug'] ?? null;

            if (!isset($id) || !isset($file)) { 
                http_response_code(400);
                echo json_encode(['status' => false, 'message' => 'id and file are required', "code" => 400]);
                return;
            }
            

            $path = dirname(__DIR__) . "/Views/movies/$id/$file";
            
            if (!is_file($path)) {
                http_response_code(404);
                echo json_encode(['status' => false, 'message' => 'file not found', "code" => 404, 'data' => [] ]);
                return;
            }
 

            // Envoyer les bons headers
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
            header('Content-Length: ' . filesize($path));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Expires: 0');


            // Désactiver la mise en mémoire tampon pour éviter les coupures
            ob_clean();
            flush();

            // Lire et envoyer le fichier par blocs pour éviter les coupures de téléchargement
            $handle = fopen($path, "rb");
            if ($handle) {
                while (!feof($handle)) {
                    echo fread($handle, 8192); // Lire et envoyer 8 Ko à la fois
                    flush(); // Vider le buffer pour envoyer immédiatement
                }
                fclose($handle);
            }

            exit;
        }
    }


?>