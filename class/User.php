<?php 
	class User {
		private $access_exp = 800;
		private $refresh_exp = 800;
		public function authenticate(){
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['email']) || !isset($data['password'])) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				$password = md5($data['password']);

		        $sql = MySql::conectar()->prepare("SELECT * FROM `users` where email = ? AND password = ?");
		        $sql->execute(array($data['email'], $password));

		        if($sql->rowCount() == 1){

                    $data = $sql->fetch(PDO::FETCH_ASSOC);

		        	$jwt = new JwtClass();

		            $idUser = $data['id'];

		            $time_exp = time()+$this->access_exp;
					$payload = [
					    'iss' => 'localhost',
					    'exp' => $time_exp,
					    'type'=>"access_token",
					    'name'=>$data['name'],
					    'id'=>$idUser
					 ];
					 $token = $jwt->criaToken($payload);

 		            $time_exp_refresh = time()+$this->refresh_exp;
					$payload_refresh = [
					    'iss' => 'localhost',
					    'exp' => $time_exp_refresh,
					    'type'=>"refresh_token",
					    'name'=>$data['name'],
					    'id'=>$idUser
					 ];
					 $refresh_token = $jwt->criaToken($payload_refresh);

					 $signature_refresh_token = $jwt->extractDataJWT($refresh_token, 2);
					 $signature_access_token = $jwt->extractDataJWT($token, 2);
					 // $query = MySql::conectar()->prepare("UPDATE token SET refresh_token = ?, expires_date = ? WHERE id_user = ?");
					 // $date = date('Y-m-d H:i:s', $time_exp_refresh);
					 // $query->execute(array($signature_refresh_token, $date, $idUser));

					 $query = MySql::conectar()->prepare("INSERT INTO `token` (id_user, access_token, refresh_token, expires_date) VALUES (?, ?, ?, ?)");
					 $date = date('Y-m-d H:i:s', $time_exp_refresh);
					 $query->execute(array($idUser, $signature_access_token, $signature_refresh_token, $date));

					$response = ["data"=>$this->userTokenReturn($idUser, $data['name'], $data['email'], $data['username'], null, $token, $refresh_token)];

					echo json_encode($response);
		                http_response_code(201);
		                exit;


		        } else {
		        	$err = $err->getError("ERR_USER_INCORRECT");
			        	$res = ["errors"=> [$err]];

			        	http_response_code($err['status']);
			        	echo json_encode($res);
        				exit;
	            }

			} else {

		        $err = $err->getError("ERR_INVALID_DATA");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
			}
		}

		public function post(){
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['username'])) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

		        $verifyEmail = MySql::conectar()->prepare("SELECT * FROM `users` where email = ? OR username = ?");
		        $verifyEmail->execute(array($data['email'], $data['username']));
		        $verifyEmail->fetchAll();

		        if($verifyEmail->rowCount() == 1){

			        $err = $err->getError("ERR_USER_EXISTS");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
		        } else {
		        	try {
			        	$password = md5($data['password']);
			            $sql = MySql::conectar()->prepare("INSERT INTO `users` (name, email, password, username) VALUES (?, ?, ?, ?)");
			            $a = $sql->execute(array($data['name'],$data['email'],$password, $data['username']));

			            $jwt = new JwtClass();

			            $idUser = MySql::getLastId();

			            $time_exp = time()+$this->access_exp;
						$payload = [
						    'iss' => 'localhost',
						    'exp' => $time_exp,
						    'type'=>"access_token",
						    'name'=>$data['name'],
						    'id'=>$idUser
						 ];
						 $token = $jwt->criaToken($payload);

	 		            $time_exp_refresh = time()+$this->refresh_exp;
						$payload_refresh = [
						    'iss' => 'localhost',
						    'exp' => $time_exp_refresh,
						    'type'=>"refresh_token",
						    'name'=>$data['name'],
						    'id'=>$idUser
						 ];
						 $refresh_token = $jwt->criaToken($payload_refresh);

						 $signature_refresh_token = $jwt->extractDataJWT($refresh_token, 2);
						 $signature_access_token = $jwt->extractDataJWT($token, 2);
						 // $query = MySql::conectar()->prepare("UPDATE token SET refresh_token = ?, expires_date = ? WHERE id_user = ?");
						 $query = MySql::conectar()->prepare("INSERT INTO `token` (id_user, access_token, refresh_token, expires_date) VALUES (?, ?, ?, ?)");
						 $date = date('Y-m-d H:i:s', $time_exp_refresh);
						 $query->execute(array($idUser, $signature_access_token, $signature_refresh_token, $date));

						 $response = ["data"=>$this->userTokenReturn($idUser, $data['name'], $data['email'], $data['username'], null, $token, $refresh_token)];

		                echo json_encode($response);
		                http_response_code(201);
		                exit;

		        	} catch(Exception $e) {
		        		print_r($e);
				        // $err = $err->getError("ERR_APPLICATION");
			        	// $res = ["errors"=> [$err]];

			        	// http_response_code($err['status']);
			        	// echo json_encode($res);
        				// exit;
		        	}
	            }

			} else {

		        $err = $err->getError("ERR_INVALID_DATA");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
			}
		}

		public function get(){
			$sql = MySql::conectar()->prepare("SELECT id, name, email, username, photo FROM users");
        	$sql->execute();
			$err = new Errors();
			
        	$res = [];
        	$i=0;
	        if(($sql) AND ($sql->rowCount() != 0)) {
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);
	                $user = $this->userReturn($data['id'], $data['name'], $data['email'], $data['username'], $data['photo']);
	                $res[$i]=$user;
	                $i++;
	            }

	            if(sizeof($res) !== 0) {
	            	$response = ["data"=>$res];
	            	echo json_encode($response);
	            	http_response_code(200);
	            } else {
	            	$err = $err->getError("ERR_APPLICATION");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
					exit;
	            }
	        } else {
		        $err = $err->getError("ERR_USER_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }
		}

		public function find($param){
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT id, name, email, username, photo FROM users WHERE username=?");
        	$sql->execute(array($param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	            $data = $sql->fetch(PDO::FETCH_ASSOC);

	            $res = $this->userReturn($data['id'], $data['name'], $data['email'], $data['username'], $data['photo']);

            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_USER_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }
		}

		public function put(){
			echo "Atualizar usuario";
			
		}

		public function delete(){
			echo "Deletar usuario";
			
		}

		public function patch(){
			echo "Alterar foto do perfil";
		}


		public function getStats(){
			echo "Receber estatisticas do usuário";
		}

		//middlewares
		public function validateRole($role, $payload){
            $err = new Errors();

			$payload = (array)$payload;

			$sql = MySql::conectar()->prepare("SELECT role FROM `users` WHERE id=?");
			$sql->execute(array($payload['id']));

			if(($sql) AND ($sql->rowCount() != 0)) {

            	$data = $sql->fetch(PDO::FETCH_ASSOC);

            	if($data['role'] >= $role) {
            		return $data;
            	} else {
            		return false;
            	}

			} else {
				$err = $err->getError("ERR_USER_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
			}

		}

		public function validateJwt(){
            $err = new Errors();

			$headers = apache_request_headers();
	        if (isset($headers['Authorization'])) {
	            $token = str_replace("Bearer ", "", $headers['Authorization']);
	            if($token == "undefined") {
					$err = $err->getError("ERR_TOKEN_INVALID");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
		            exit;
	            }
	        } else {
		        $err = $err->getError("ERR_TOKEN_INVALID");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
	            exit;
	        }

        	$jwt = new JwtClass();

	        $validate = $jwt->validaToken($token);

	        if($validate) {
	        	$expired = $jwt->isExpiredToken($token);
	        	return $expired;
	        }
	        else return false;
		}

		public function refresh() {
            $post = file_get_contents("php://input");
            $err = new Errors();

            if($post) {
                $data = json_decode($post, true);
                if(!isset($data['refresh_token'])) {
                    $err = $err->getError("ERR_TOKEN_INVALID");
                    $res = ["errors"=> [$err]];

                    http_response_code($err['status']);
                    echo json_encode($res);
                    exit;
                }
                $refresh_token = $data['refresh_token'];

                $jwt = new JwtClass();
                $token_expired = $jwt->isExpiredToken($refresh_token);

                if($token_expired) {
                    $err = $err->getError("ERR_TOKEN_INVALID");
                    $res = ["errors"=> [$err]];

                    http_response_code($err['status']);
                    echo json_encode($res);
                    exit;
                }

                $payload_refresh_token = $jwt->extractDataJWT($refresh_token, 1);
		        $signature_refresh_token = $jwt->extractDataJWT($refresh_token, 2);

                $sql = MySql::conectar()->prepare("SELECT * FROM `token` INNER JOIN `users` ON token.id_user = users.id WHERE refresh_token=?");
                $sql->execute(array($signature_refresh_token));

                if(($sql) AND ($sql->rowCount() != 0)) {

                	$data = $sql->fetch(PDO::FETCH_ASSOC);
                	// print_r($data);

		            $idUser = $data['id'];

		            $time_exp = time()+$this->access_exp;
					$payload = [
					    'iss' => 'localhost',
					    'exp' => $time_exp,
					    'type'=>"access_token",
					    'name'=>$data['name'],
					    'id'=>$idUser
					 ];
					 $token = $jwt->criaToken($payload);

 		            $payload_refresh_token->exp = time()+$this->refresh_exp;
					 $new_refresh_token = $jwt->criaToken($payload_refresh_token);
		             $new_signature_refresh_token = $jwt->extractDataJWT($new_refresh_token, 2);

		             $query = MySql::conectar()->prepare("UPDATE token SET refresh_token = ?, expires_date = ? WHERE id = ?");
					 $date = date('Y-m-d H:i:s', $payload_refresh_token->exp);
					 $query->execute(array($signature_refresh_token, $date, $data['id']));

 					 $response = ["data"=>$this->userTokenReturn($data['id_user'], $data['name'], $data['email'], $data['username'], $data['photo'], $token, $new_refresh_token)];

	                echo json_encode($response);
	                http_response_code(200);

            	} else {
			        $err = $err->getError("ERR_USER_NOT_FOUND");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
					exit;
            	}

            } else {
                $err = $err->getError("ERR_INVALID_DATA");
                $res = ["errors"=> [$err]];

                // http_response_code($err['status']);
                echo json_encode($res);
                exit;
            }
		}

		//returns

		private function userReturn($id, $name, $email, $username, $photo) {
			$response = [
            	"type"=> "user",
            	"id"=> $id,
            	"attributes"=>[
            		"name"=>$name,
            		"email"=>$email,
            		"username"=>$username,
            		"photo"=>$photo
            	],
            	"links"=>[
            		"self"=>"/user/".$username
            	]
            ];
            return $response;
		}
		private function userTokenReturn($id, $name, $email, $username, $photo, $token, $refresh_token) {
			$response = [
            	"type"=> "user",
            	"id"=> $id,
            	"attributes"=>[
            		"name"=>$name,
            		"email"=>$email,
            		"username"=>$username,
            		"photo"=>$photo
            	],
            	"token"=>[
            		"access_token"=>$token,
            		"refresh_token"=>$refresh_token
            	],
            	"links"=>[
            		"self"=>'//user//'.$username
            	]
            ];
            return $response;
		}
	}
?>