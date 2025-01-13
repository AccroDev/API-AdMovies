<?php
    namespace Controllers;

use AltoRouter;

    class Router { 

        private $vieuwPath;  
        private $router;

        /**
         * router
         * cette class utilise AltoRouter comme routeur principal
         * @var AltoRouter
         */
        public function __construct(string $vieuwPath)
        {
            $this->vieuwPath = $vieuwPath;
            $this->router = new AltoRouter();  
        }

        public function get(string $url,string $view,?string $name = null):self
        {
            $this->router->map('GET',$url,$view,$name);
            return $this;
        }
        public function delete(string $url,string $view,?string $name = null):self
        {
            $this->router->map('DELETE',$url,$view,$name);
            return $this;
        }
        public function post(string $url,string $view,?string $name = null):self
        {
            $this->router->map('POST',$url,$view,$name);
            return $this;
        }

        public function run()
        {
            /* $match = [ 
                "path" => "/xxx" , 
                "target" => "Functions@erro",
                "name" => "accueil" ] 
            */

            $match = $this->router->match();

            if ($match === false || $match['target'] === null ) {
                //load user interface
                require(dirname(__DIR__) . DIRECTORY_SEPARATOR . "Views/index.html");
                return;
            } 

            //else it mean is api so load class

            $explose = explode('@',$match['target']);
            
            $class = $explose[0];
            $methode = $explose[1];
            
            $classAndNameSpace = $this->vieuwPath . '\\'.$class;

            $params = NULL;
            if (isset($match['params']) && !empty([$match['params']])) {
                    $params = $match['params'];
            }
             
                $instanceOf = new $classAndNameSpace(); 
                $instanceOf->$methode($params,$match['name']??""); 
            /* try {} catch (\Throwable $th) {
                echo 'class ou methode introuvable ' . $classAndNameSpace . $methode;
            } */
        } 
        public function generate(string $name)
        {
            return $this->router->generate($name) ;
        }
    }
?>