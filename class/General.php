<?php 
    class General {
        public function main(){
            $response = [
                "Bem vindo a API de Olympo Tournaments!",
                "Utilize a rota /api"
            ];
            echo json_encode($response);
            http_response_code(200);
        }
    }
?>