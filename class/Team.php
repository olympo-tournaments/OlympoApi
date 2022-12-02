<?php 
	class Team {
		public function post($param, $jwt) {
			#param = undefined!!
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['name_team']) || !isset($data['id_tournament'])) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				$name_team = $data['name_team'];
				$id_tournament = $data['id_tournament'];

				$verifyExistsTeam = MySql::conectar()->prepare("SELECT * from `team_tournament` WHERE name_team=? AND id_tournament=?");
				$verifyExistsTeam->execute(array($name_team, $id_tournament));

				if($verifyExistsTeam->rowCount() == 1){
			        $err = $err->getError("ERR_TEAM_EXISTS");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
		        }
				$jwt = (array)$jwt;

				#
				$verifyUserTournament = MySql::conectar()->prepare("SELECT * FROM team_tournament_members WHERE id_user=? AND id_team=?");
                $verifyUserTournament->execute(array($jwt['id'], $id_tournament));

                if($verifyUserTournament->rowCount() >= 1){
                    $err = $err->getError("ERR_USER_TOURNAMENT");
                    $res = ["errors"=> [$err]];

                    http_response_code($err['status']);
                    echo json_encode($res);
                    exit;
                }

				$sql = MySql::conectar()->prepare("INSERT INTO `team_tournament` (name_team, owner, active, id_tournament) VALUES (?,?,?,?)");
				$sql->execute(array($name_team, $jwt['id'], true, $id_tournament));

				$idTeam = MySql::getLastId();

				$addUserTeam = MySql::conectar()->prepare("INSERT INTO team_tournament_members (id_team, id_user, id_tournament, tag, member_active) VALUES(?,?,?,?,?)");
                $addUserTeam->execute(array($idTeam, $jwt['id'], $id_tournament, "owner", true));

				$ownerInfo = MySql::conectar()->prepare("SELECT * FROM users WHERE id=?");
				$ownerInfo->execute(array($jwt['id']));
                $data = $ownerInfo->fetch(PDO::FETCH_ASSOC);

                $team_members = [
                    [
                        "id"=>$data['id'],
                        "name"=>$data['name'],
                        "email"=>$data['email'],
                        "username"=>$data['username'],
                        "photo"=>$data['photo']
                    ]
                ];

				$team = [
					"id_team"=>$idTeam,
					"name_team"=>$name_team,
            		"active"=>true,
            		"id_tournament"=>$id_tournament,
            		"team_members"=>$team_members,
            		"owner"=>$jwt['id']
				];

				$response = ["data"=>Returns::TeamReturn($team)];

				http_response_code(201);
				echo json_encode($response);

			} else {
		        $err = $err->getError("ERR_INVALID_DATA");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
			}		//ok
		}//ok
		public function get() {
			$sql = MySql::conectar()->prepare("SELECT * FROM `team_tournament` WHERE active=?");
        	$sql->execute(array(true));

			$err = new Errors();

        	$res = [];
        	$i=0;
	        if(($sql) AND ($sql->rowCount() != 0)) {
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);

	            	$res[$i] = Returns::TeamReturn($data);
	                $i++;
	            }
            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);

	        } else {
		        $err = $err->getError("ERR_TEAM_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }//ok
		}//ok
		public function find($param, $jwt) {
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM `team_tournament` WHERE id_team=? ");
        	$sql->execute(array($param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	            $data = $sql->fetch(PDO::FETCH_ASSOC);
	            $res = Returns::TeamReturn($data);

            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_TEAM_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }//ok
		}//ok
		public function put() {
			echo "Atualizar equipe";
		}
		public function delete() {
			echo "deletar equipe";
		}
		public function patch(){
			echo "Alterar foto da equipe";
		}
		public function getMembers($param){
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM `team_tournament_members` INNER JOIN `users` ON team_tournament_members.id_user = users.id WHERE id_team=? ");
        	$sql->execute(array($param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	        	$i = 0;
	        	$res = [];
	        	while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);

	            	$res[$i] = Returns::TeamMembersReturn($data);
	                $i++;
	            }
            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_TEAM_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }
		}//ok
		public function addMember($param, $jwt) {
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['id_team']) || !isset($data['id_user']) || !isset($data['tag'])) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					echo $json;
        			exit;	
				}

				$id_team = $data['id_team'];
				$id_user = $data['id_user'];
				$tag = $data['tag'];

				$jwt = (array)$jwt;

				#valida se o usuario existe
				$user = MySql::conectar()->prepare("SELECT * FROM `users` WHERE id=?");
				$user->execute(array($id_user));

				if($user->rowCount() == 0){
					$err = $err->getError("ERR_USER_NOT_FOUND");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
        			exit;
				}

				#valida se a equipe existe
				$team = MySql::conectar()->prepare("SELECT * FROM `team_tournament` WHERE id_team=?");
				$team->execute(array($id_team));

				if($team->rowCount() == 0){
					$err = $err->getError("ERR_TEAM_NOT_FOUND");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
        			exit;
				}

				$team = $team->fetch(PDO::FETCH_ASSOC);

				#validar se passou o limite dos membrros
				$tournament = MySql::conectar()->prepare("SELECT sport FROM `tournaments` WHERE id_tournament=?");
				$tournament->execute(array($team['id_tournament']));
				$tournament = $tournament->fetch(PDO::FETCH_ASSOC);

				$sport = $tournament['sport'];

				$sportMembers = MySql::conectar()->prepare("SELECT sport_members FROM tournament_sports WHERE id_sport=?");
				$sportMembers->execute(array($sport));
				$sportMembers = $sportMembers->fetch(PDO::FETCH_ASSOC);

				$verifyLimitMembers = MySql::conectar()->prepare("SELECT COUNT(*) FROM `team_tournament_members` WHERE id_team=?");
				$verifyLimitMembers->execute(array($id_team));
				$verifyLimitMembers = $verifyLimitMembers->fetch(PDO::FETCH_ASSOC);
				if($verifyLimitMembers['COUNT(*)'] >= $sportMembers['sport_members']){
					$err = $err->getError("ERR_TEAM_LIMIT");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
        			exit;
				}

				#valida se o usuario ja esta na equipe ou nao
				$verifyAlreadyUserTeam = MySql::conectar()->prepare("SELECT * FROM `team_tournament_members` WHERE id_user=? AND id_team=?");
				$verifyAlreadyUserTeam->execute(array($id_user, $id_team));

				if($verifyAlreadyUserTeam->rowCount() >= 1){
					$err = $err->getError("ERR_USER_TEAM_EXISTS");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        $json = json_encode($res);
					 echo $json;
        			exit;
				}

				$sql = MySql::conectar()->prepare("INSERT INTO `team_tournament_members` (id_team, id_user, id_tournament, tag, member_active) VALUES(?,?,?,?,?)");
				$sql->execute(array($id_team, $id_user, $team['id_tournament'], $tag, true));

				$members = MySql::conectar()->prepare("SELECT * FROM `team_tournament_members` INNER JOIN `users` ON team_tournament_members.id_user = users.id WHERE id_team=? ");
				$members->execute(array($id_team));

				if(($members) AND ($members->rowCount() != 0)) {
		        	$i = 0;
		        	$res = [];
		            while($data=$members->fetch(PDO::FETCH_ASSOC)){
		                $res[$i]=Returns::TeamMembersReturn($data);
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
				exit;
			}
		}//ok

	}
?>