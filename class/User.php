<?php 
	class User {
		private $access_exp = 60 * 60* 24;
		private $refresh_exp = 60 * 60 * 24 * 3;
		public function authenticate(){
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['email']) || !isset($data['password'])) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					echo base64_encode($json);
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

					$res = ["data"=>$this->userTokenReturn($idUser, $data['name'], $data['email'], $data['username'], null, $token, $refresh_token)];

			        $json = json_encode($res);
					// echo base64_encode($json);
					 echo $json;
					http_response_code(200);
		                exit;


		        } else {
		        	$err = $err->getError("ERR_USER_INCORRECT");
			        	$res = ["errors"=> [$err]];

			        	http_response_code($err['status']);
						$json = json_encode($res);
						echo $json;
						// echo base64_encode($json);
        				exit;
	            }

			} else {

		        $err = $err->getError("ERR_INVALID_DATA");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
				$json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
				exit;
			}//ok
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
			        $json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
        			exit;	
				}

		        $verifyEmail = MySql::conectar()->prepare("SELECT * FROM `users` where email = ? OR username = ?");
		        $verifyEmail->execute(array($data['email'], $data['username']));
		        $verifyEmail->fetchAll();

		        if($verifyEmail->rowCount() == 1){

			        $err = $err->getError("ERR_USER_EXISTS");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
					//  echo base64_encode($json);
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

						 $res = ["data"=>$this->userTokenReturn($idUser, $data['name'], $data['email'], $data['username'], null, $token, $refresh_token)];

						 $json = json_encode($res);
					 echo $json;
					 //  echo base64_encode($json);
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
				$json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
				exit;
			}//ok
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
	            	$res = ["data"=>$res];
	            	echo json_encode($res);
	            	http_response_code(200);
	            } else {
	            	$err = $err->getError("ERR_APPLICATION");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
					exit;
	            }
	        } else {
		        $err = $err->getError("ERR_USER_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
				$json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
				exit;
	        }//ok
		}

		public function find($param){
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT id, name, email, username, photo FROM users WHERE username=?");
        	$sql->execute(array($param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	            $data = $sql->fetch(PDO::FETCH_ASSOC);

	            $res = $this->userReturn($data['id'], $data['name'], $data['email'], $data['username'], $data['photo']);

            	$res = ["data"=>$res];
				$json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_USER_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
				$json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
				exit;
	        }//ok
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

		public function getUserTournaments($param) {
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM team_tournament_members AS members INNER JOIN `tournaments` ON members.id_tournament = tournaments.id_tournament INNER JOIN `tournament_sports` AS sport ON tournaments.sport = sport.id_sport INNER JOIN `team_tournament` AS team ON members.id_team = team.id_team WHERE id_user=?");
        	$sql->execute(array($param));
	        if(($sql) AND ($sql->rowCount() != 0)) {
	        	$res = [];

				$i = 0;
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);
	                // $tournament = [

	                // ];
	                $res[$i] = Returns::userTournamentReturn($data);
	                $i++;
	            }

				$response = ["data"=>$res];
				 echo json_encode($response);
					 // echo base64_encode($json);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_USER_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
				$json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
				exit;
	        }
		}//ok

		public function getUserFavorites($param, $jwt) {
			$err = new Errors();

			$jwt = (array)$jwt;

			$sql = MySql::conectar()->prepare("SELECT * FROM `tournaments_favorites` INNER JOIN `tournaments` ON tournaments.id_tournament = tournaments_favorites.id_tournament WHERE id_user=?");
        	$sql->execute(array($jwt['id']));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	        	$i = 0;
	        	$res = [];
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                $res[$i]=Returns::tournamentReturn($data);
	                $i++;
	            }

            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_TOURNAMENT_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }
		}//ok

		public function addUserFavorites($param, $jwt) {
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['id_tournament'])) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
        			exit;	
				}

				$id_tournament = $data['id_tournament'];

				$jwt = (array)$jwt;

				$verifyAlreadyFavorite = MySql::conectar()->prepare("SELECT * FROM `tournaments_favorites` WHERE id_user=? AND id_tournament=?");
				$verifyAlreadyFavorite->execute(array($jwt['id'], $id_tournament));

				if($verifyAlreadyFavorite->rowCount()>= 1){
					$err = $err->getError("ERR_TOURNAMENT_FAVORITED");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
        			exit;
				}

				$sql = MySql::conectar()->prepare("INSERT INTO `tournaments_favorites` (id_user, id_tournament) VALUES(?,?)");
				$sql->execute(array($jwt['id'], $id_tournament));

				$userFavorites = MySql::conectar()->prepare("SELECT * FROM `tournaments_favorites` INNER JOIN `tournaments` ON tournaments.id_tournament = tournaments_favorites.id_tournament WHERE id_user=? ");
				$userFavorites->execute(array($jwt['id']));

				if(($userFavorites) AND ($userFavorites->rowCount() != 0)) {
		        	$i = 0;
		        	$res = [];
		            while($data=$userFavorites->fetch(PDO::FETCH_ASSOC)){
		                $res[$i]=Returns::tournamentReturn($data);
		                $i++;
		            }

	            	$response = ["data"=>$res];
	            	echo json_encode($response);
	            	http_response_code(200);
		        } else {
			        $err = $err->getError("ERR_TOURNAMENT_NOT_FOUND");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
					exit;
		        }


			} else {

		        $err = $err->getError("ERR_INVALID_DATA");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
				$json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
				exit;
			}
		}//ok

		public function getAllUserMatches($param, $jwt) {
			$err = new Errors();

			$jwt = (array)$jwt;

			$sql = MySql::conectar()->prepare("SELECT * FROM `matches` INNER JOIN team_tournament_members AS members ON members.id_user=? INNER JOIN tournaments ON tournaments.id_tournament=matches.id_tournament WHERE id_team1=members.id_team OR id_team2=members.id_team");
        	$sql->execute(array($jwt['id']));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	        	$i = 0;
	        	$res = [];
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
					// $res[$i] = $data;
	                $res[$i]=Returns::Match($data);
	                $i++;
	            }

            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_TOURNAMENT_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }
		}

		public function getUserMatches($param, $jwt) {
			$err = new Errors();

			$jwt = (array)$jwt;

			// $sql = MySql::conectar()->prepare("SELECT * FROM `matches` INNER JOIN team_tournament_members AS members ON members.id_user=? WHERE id_team1=members.id_team OR id_team2=members.id_team");
			// $sql = MySql::conectar()->prepare("SELECT * FROM `matches` INNER JOIN tournaments AS `t` ON t.id_tournament = matches.id_tournament INNER JOIN team_tournament_members AS team1 ON team1.id_user=:id_user INNER JOIN team_tournament_members AS team2 ON team2.id_user=:id_user WHERE id_team1=team1.id_team OR id_team2=team2.id_team");
        	// $sql->bindParam(':id_user', $jwt['id']);
			// $sql->execute();
			$sql = MySql::conectar()->prepare("SELECT * FROM `matches` INNER JOIN team_tournament_members AS members ON members.id_user=? WHERE result IS NULL AND id_team1=members.id_team OR id_team2=members.id_team ");
			$sql->execute(array($jwt['id']));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	        	$i = 0;
	        	$res = [];
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                $res[$i]=Returns::Match($data);
	                $i++;
	            }

            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_TOURNAMENT_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }
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
				$json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
				exit;
			}

		}//ok

		public function validateJwt(){
            $err = new Errors();

			$headers = apache_request_headers();
	        if (isset($headers['Authorization'])) {
	            $token = str_replace("Bearer ", "", $headers['Authorization']);
	            if($token == "undefined") {
					$err = $err->getError("ERR_TOKEN_INVALID");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
		            exit;
	            }
	        } else {
		        $err = $err->getError("ERR_TOKEN_INVALID");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
				$json = json_encode($res);
					 echo $json;
					 // echo base64_encode($json);
	            exit;
	        }

        	$jwt = new JwtClass();

	        $validate = $jwt->validaToken($token);

	        if($validate) {
	        	$expired = $jwt->isExpiredToken($token);
	        	return $expired;
	        }
	        else return false;
		}//ok

		public function refresh() {
			//implementar a blacklist aqui, n ta funcionando
            $post = file_get_contents("php://input");
            $err = new Errors();
			
            if($post) {
				$headers = apache_request_headers();
				$data = json_decode($post, true);
                if(!isset($data['refresh_token'])) {
                    $err = $err->getError("ERR_TOKEN_INVALID");
                    $res = ["errors"=> [$err]];

                    http_response_code($err['status']);
			        $json = json_encode($res);
					echo $json;
                    exit;
                }
                $refresh_token = $data['refresh_token'];
				$token = $headers['Authorization'];

                $jwt = new JwtClass();
				
                $refresh_token_expired = $jwt->isExpiredToken($refresh_token);

				if(!$refresh_token_expired) {
					$err = $err->getError("ERR_INVALID_REFRESH");
                    $res = ["errors"=> [$err]];
					
                    http_response_code($err['status']);
			        $json = json_encode($res);
					echo $json;
                    exit;
				}

                $payload_refresh_token = $jwt->extractDataJWT($refresh_token, 1);
		        $signature_refresh_token = $jwt->extractDataJWT($refresh_token, 2);

                $sql = MySql::conectar()->prepare("SELECT * FROM `token` INNER JOIN `users` ON token.id_user = users.id WHERE refresh_token=?");
                $sql->execute(array($signature_refresh_token));

                if(($sql) AND ($sql->rowCount() != 0)) {

                	$data = $sql->fetch(PDO::FETCH_ASSOC);

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
					 $signature_access_token = $jwt->extractDataJWT($token, 2);

 		            $payload_refresh_token->exp = time()+$this->refresh_exp;
					 $new_refresh_token = $jwt->criaToken($payload_refresh_token);
		             $new_signature_refresh_token = $jwt->extractDataJWT($new_refresh_token, 2);

		             $query = MySql::conectar()->prepare("UPDATE token SET access_token = ?, refresh_token = ?, expires_date = ? WHERE id = ?");
					 $date = date('Y-m-d H:i:s', $payload_refresh_token->exp);
					 $query->execute(array($signature_access_token, $new_signature_refresh_token, $date, $data['id']));
 					 $res = ["data"=>$this->userTokenReturn($data['id_user'], $data['name'], $data['email'], $data['username'], $data['photo'], $token, $new_refresh_token)];


			        $json = json_encode($res);
					echo $json;

            	} else {
			        $err = $err->getError("ERR_USER_NOT_FOUND");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
					exit;
            	}

            } else {
                $err = $err->getError("ERR_INVALID_DATA");
                $res = ["errors"=> [$err]];

                http_response_code($err['status']);
				$json = json_encode($res);
				echo $json;
                exit;
            }
		}//ok

		//returns

		private function userReturn($id, $name, $email, $username, $photo) {
			$res = [
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
            return $res;
		}
		private function userTokenReturn($id, $name, $email, $username, $photo, $token, $refresh_token) {
			$res = [
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
            		"self"=>"/user/".$username
            	]
            ];
            return $res;
		}
	}
?>