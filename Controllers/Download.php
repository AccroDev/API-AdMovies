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

            if (!isset($_SESSION["accreditation"]) || (isset($_SESSION["accreditation"]) && (int) $_SESSION["accreditation"] < 2)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'message' => 'you are not allowed to download', "code" => 403]);
                return;
            }

            $id = $params['id'] ?? null;
            $file = $params['slug'] ?? null;

            if (!isset($id) || !isset($file)) { 
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'message' => 'id and file are required', "code" => 400]);
                return;
            }
            

            $path = dirname(__DIR__) . "/Views/movies/$id/$file";
            
            if (!is_file($path)) {
                http_response_code(404);
                header('Content-Type: application/json');
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

       /* public function watch($params)
        {
            if (!isset($_SESSION["accreditation"]) || (isset($_SESSION["accreditation"]) && (int) $_SESSION["accreditation"] < 2)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'message' => 'you are not allowed to watch', "code" => 403]);
                return;
            }
        
            $id = $params['id'] ?? null;
            $file = $params['slug'] ?? null;
        
            if (!$id || !$file) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'message' => 'id and file are required', "code" => 400]);
                return;
            }
        
            $path = dirname(__DIR__) . "/Views/movies/$id/$file";
        
            if (!is_file($path)) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'message' => 'file not found', "code" => 404, 'data' => []]);
                return;
            }
        
            $size = filesize($path);
            $start = 0;
            $end = $size - 1;
        
            header('Content-Type: video/mp4');
            header('Accept-Ranges: bytes');
        
            // Gestion des plages pour le streaming
            if (isset($_SERVER['HTTP_RANGE'])) {
                $range = $_SERVER['HTTP_RANGE'];
                list(, $range) = explode('=', $range, 2);
                list($start, $end) = explode('-', $range, 2);
        
                $start = ($start !== '') ? (int)$start : 0;
                $end = ($end !== '') ? (int)$end : $size - 1;
        
                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes $start-$end/$size");
                header('Content-Length: ' . ($end - $start + 1));
            } else {
                header('Content-Length: ' . $size);
            }
        
            // Désactiver le buffer pour envoyer les données directement
            ob_clean();
            flush();
        
            $handle = fopen($path, 'rb');
            fseek($handle, $start);
        
            // Lire et envoyer le fichier par blocs pour économiser la mémoire
            while (!feof($handle) && ($pos = ftell($handle)) <= $end) {
                echo fread($handle, 1024 * 1024); // 8 Ko par bloc8192
                flush();
            }
        
            fclose($handle);
            exit;
        }*/

        private static function getType($fileName) : string
        {
            $extension = pathinfo($fileName,PATHINFO_EXTENSION);

            $mimeTypes = [
                'mp4' => "video/mp4",
                'webm' => "video/webm",
                'ogg' => "video/ogg",
                'avi' => "video/x-msvideo",
                'mov' => "video/quicktime",
                'mkv' => "video/x-matroska",
            ];

            if (array_key_exists($extension, $mimeTypes)) {
                return $mimeTypes[$extension];
            }
            return  'application/octet-stream';
        }

        public function watch($params)
        {
            
            if (!isset($_SESSION["accreditation"]) || (isset($_SESSION["accreditation"]) && (int) $_SESSION["accreditation"] < 2)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'message' => 'you are not allowed to watch', "code" => 403]);
                return;
            }

            $id = $params['id'] ?? null;
            $file = $params['slug'] ?? null;

            if (!isset($id) || !isset($file)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'message' => 'id and file are required', "code" => 400]);
                return;
            }

            $path = dirname(__DIR__) . "/Views/movies/$id/$file";

            if (!is_file($path)) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'message' => 'file not found', "code" => 404, 'data' => [] ]);
                return;
            }

            $type = self::getType($file);/*
            $ext = explode('.',$file);
            $ext = $ext[count($ext) - 1];
            if ($ext === 'avi') {
                $type = 'video/avi';
            } */


            // Envoyer les bons headers
            header('Content-Type: '. $type);
            header('Content-Length: ' . filesize($path));
            header('Accept-Ranges: bytes');

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