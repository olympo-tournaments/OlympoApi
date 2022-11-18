<?php 
	class Tournament {
		public function post($param, $jwt) {
			#param = undefined!!
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['name']) || !isset($data['description']) || !isset($data['sport']) || !isset($data['invitation'])) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				$name = $data['name'];
				$description = $data['description'];
				$sport = $data['sport'];
				$invitation = $data['invitation'];

				$verifyInvite = MySql::conectar()->prepare("SELECT * from `tournaments` WHERE invitation=?");
				$verifyInvite->execute(array($invitation));

				if($verifyInvite->rowCount() == 1){
			        $err = $err->getError("ERR_INVITE_EXISTS");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
		        }

				$sports = ["csgo", "valorant", "basquete", "futebol"];
				$sportInArray = in_array($sport, $sports);

				if(!$sportInArray) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				$jwt = (array) $jwt;

				$sql = MySql::conectar()->prepare("INSERT INTO `tournaments` (name, description, sport, invitation, owner_id, active) VALUES (?,?,?,?,?,?)");
				$sql->execute(array($name, $description, $sport, $invitation, $jwt['id'], true));

				$idTournament = MySql::getLastId();

				$response = ["data"=>$this->tournamentReturn($idTournament, $name, $description, $sport, $invitation, null, null, $jwt['id'],true)];

				http_response_code(201);
				echo json_encode($response);

			} else {

		        $err = $err->getError("ERR_INVALID_DATA");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
			}			
		}
		public function get() {
			$sql = MySql::conectar()->prepare("SELECT * FROM `tournaments` WHERE active=?");
        	$sql->execute(array(true));

			$err = new Errors();

        	$res = [];
        	$i=0;
	        if(($sql) AND ($sql->rowCount() != 0)) {
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);

	                $tournament = $this->tournamentReturn($data['id_tournament'], $data['name'], $data['description'], $data['sport'], $data['invitation'], $data['team_members'], $data['photo'], $data['owner_id'], $data['active']);
	                $res[$i]=$tournament;
	                $i++;
	            }

	            if(sizeof($res) !== 0) {
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
		        $err = $err->getError("ERR_TOURNAMENT_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }
		}
		public function find($param) {
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM `tournaments` WHERE id_tournament=? ");
        	$sql->execute(array($param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	            $data = $sql->fetch(PDO::FETCH_ASSOC);
	            $res = $this->tournamentReturn($data['id_tournament'], $data['name'], $data['description'], $data['sport'], $data['invitation'], $data['team_members'], $data['photo'], $data['owner_id'], $data['active']);

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
		public function put() {
			echo "Atualizar torneio";
		}
		public function delete() {
			echo "deletar torneio";
		}

		public function patch(){
			echo "Alterar foto do torneio";
		}
		public function getTeams() {
			echo "receber equipes do torneio";
		}
		public function getStats() {
			echo "receber estatisticas torneio";
		}
		public function setStats() {
			echo "determinar estatisticas torneio";
		}
		public function joinTournament() {
			echo "entrar no torneio";
		}

		//returns
		private function tournamentReturn($id, $name, $description, $sport, $invitation, $team_members, $photo, $ownerId, $active){
			$response = [
            	"type"=> "tournament",
            	"id"=> $id,
            	"attributes"=>[
            		"name"=>$name,
            		"description"=>$description,
            		"sport"=>$sport,
            		"photo"=>$photo,
            		"team_members"=>$team_members,
            		"owner"=>$ownerId,
            		"active"=>$active
            	],
            	"links"=>[
            		"self"=>"/tournament/".$id
            	]
            ];
            return $response;
		}
	}
?>