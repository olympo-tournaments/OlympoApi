<?php 
    // class Errors {
    //     public static $lastId = 0;
    //     $id;

    //     public function __construct(){
    //         $this->id = ++Errors::lastId;
    //     }

    //     public function createError($_title, $_detail, $_status) {
    //         $res = [
    //             "id"=>$this->id,
    //             "title"=>$_title,
    //             "detail"=>$_detail,
    //             "status"=>$_status,
    //         ];
    //     }
    // }

    // $errAccessApi = new Errors().createError("ERR_ACCESS_API", "Bem vindo a API, utilize /api", 404);

    class Errors {
        private $errors = [];

        public function __construct(){
            $file = 'errors.json';
            $handle = fopen($file, 'r');
            $read = fread($handle, filesize($file));
            fclose($handle);
            $this->errors = json_decode($read);
        }

        public function getError($title) {
            if($this->errors) {
                $found_key = array_search($title, array_column($this->errors, 'title'));
                return (array)$this->errors[$found_key];                
            } else {
                $sql = MySql::conectar()->prepare("SELECT * FROM `errors` WHERE title=?");
                $sql->execute(array($title));

                if(($sql) AND $sql->rowCount() >= 1) {
                    $data = $sql->fetch(PDO::FETCH_ASSOC);
                    return (array)$data;
                } else {
                    return [
                    "id"=>1,
                    "title"=> "ERR_APPLICATION",
                    "detail"=> "Ocorreu um erro ao realizar esta operação",
                    "status"=> 404
                ];
                }
                
            }

        }

    }

?>