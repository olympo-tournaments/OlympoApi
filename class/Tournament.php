<?php 
	class Tournament {
		public function post($param, $jwt) {
			#param = undefined!!
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['name']) || !isset($data['description']) || !isset($data['sport']) || !isset($data['invitation']) || !isset($data['type']) || !isset($data['privacy'])) {
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
				$type = $data['type'];
				$privacy = $data['privacy'];

				try {

					$verifyInvite = MySql::conectar()->prepare("SELECT * from `tournaments` WHERE invitation=?");
					$verifyInvite->execute(array($invitation));

					if($verifyInvite->rowCount() >= 1){
				        $err = $err->getError("ERR_INVITE_EXISTS");
				        $res = ["errors"=> [$err]];

				        http_response_code($err['status']);
				        echo json_encode($res);
	        			exit;	
			        }

					$sportId = MySql::conectar()->prepare("SELECT id_sport FROM tournament_sports WHERE sport_name=?");
					$sportId->execute(array($sport));

					if($sportId->rowCount() <= 0) {
				        $err = $err->getError("ERR_INVALID_DATA");
				        $res = ["errors"=> [$err]];

				        http_response_code($err['status']);
				        echo json_encode($res);
	        			exit;	
					}

					$sportId = $sportId->fetch(PDO::FETCH_ASSOC);
					$sport = $sportId['id_sport'];	
					$jwt = (array) $jwt;

					$types = ["presencial", "online"];
					if (!in_array($type, $types)) {
				        $err = $err->getError("ERR_INVALID_DATA");
				        $res = ["errors"=> [$err]];

				        http_response_code($err['status']);
				        echo json_encode($res);
	        			exit;	
					}

					$privacyTypes = ["open", "only-invites"];
					if (!in_array($privacy, $privacyTypes)) {
				        $err = $err->getError("ERR_INVALID_DATA");
				        $res = ["errors"=> [$err]];

				        http_response_code($err['status']);
				        echo json_encode($res);
	        			exit;	
					}


					$sql = MySql::conectar()->prepare("INSERT INTO `tournaments` (name, description, sport, invitation, owner_id, active, type, privacy) VALUES (?,?,?,?,?,?,?,?)");
					$sql->execute(array($name, $description, $sport, $invitation, $jwt['id'], true, $type, $privacy));

					$idTournament = MySql::getLastId();

					$ownerInfo = MySql::conectar()->prepare("SELECT * FROM users WHERE id=?");
					$ownerInfo->execute(array($jwt['id']));

					if(($ownerInfo) AND $ownerInfo->rowCount() >= 1) {

						$tournament = [
							"id_tournament"=>$idTournament,
			           		"name"=>$name,
		            		"description"=>$description,
		            		"sport"=>$sport,
		            		"photo"=>null,
		            		"team_members"=>[],
		            		"owner_id"=>$jwt['id'],
		            		"active"=>true,
		            		"invitation"=>$invitation,
		            		"type"=>$type,
							"privacy"=>$privacy
						];

						$response = ["data"=>Returns::tournamentReturn($tournament)];

						http_response_code(201);
						echo json_encode($response);
				} else {
					$err = $err->getError("ERR_APPLICATION");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
					exit;
				}
				} catch(Exception $e) {
					print_r($e);
				}

			} else {

		        $err = $err->getError("ERR_INVALID_DATA");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
			}			
		}//ok
		public function get() {
			$sql = MySql::conectar()->prepare("SELECT * FROM `tournaments` WHERE active=?");
        	$sql->execute(array(true));

			$err = new Errors();

        	$res = [];
        	$i=0;
	        if(($sql) AND ($sql->rowCount() != 0)) {
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);

	            	$res[$i] = Returns::tournamentReturn($data);
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
	        }//ok
		}
		public function find($param) {
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM `tournaments` WHERE id_tournament=? ");
        	$sql->execute(array($param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	            $data = $sql->fetch(PDO::FETCH_ASSOC);
	            $res = Returns::tournamentReturn($data);

            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_TOURNAMENT_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }//ok
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
		public function getTeams($param) {
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM `team_tournament` WHERE id_tournament=?");
        	$sql->execute(array($param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	        	$i = 0;
	        	$res = [];
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                $res[$i]=Returns::TeamReturn($data);
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
		public function getStats() {
			echo "receber estatisticas torneio";
		}
		public function setStats() {
			echo "determinar estatisticas torneio";
		}
		public function joinTournament() {
			echo "entrar no torneio";
		}
		public function inviteTeamTournament() {
			echo 'convidar';
		}
		public function leaveTeamTournament() {
			echo 'convidar';
		}
		public function getOnlineTournaments(){
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM `tournaments` WHERE type=? ");
        	$sql->execute(array("online"));

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
	        }//ok
		}//ok

		#fazer o que for perto
		public function getPresencialTournaments(){
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM `tournaments` WHERE type=? ");
        	$sql->execute(array("presencial"));

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

		public function getCategories() {
			$sql = MySql::conectar()->prepare("SELECT * FROM `tournament_sports` ORDER BY score");
        	$sql->execute();

			$err = new Errors();

        	$res = [];
        	$i=0;
	        if(($sql) AND ($sql->rowCount() != 0)) {
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);
	            	$res[$i] = Returns::CategoriesReturn($data);
	            	$i++;
	            }
	            $response = ["data"=>$res];

	            http_response_code(200);
	            echo json_encode($response);

	        } else {
		        $err = $err->getError("ERR_TOURNAMENT_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }
		}//ok

		public function findCategory($param) {
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM `tournament_sports` WHERE sport_name=? OR id_sport=? ");
        	$sql->execute(array($param, $param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	            $data = $sql->fetch(PDO::FETCH_ASSOC);
	            $res = Returns::CategoriesReturn($data);

            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_CATEGORY_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }//ok
		}

		public function findTournamentByCategory($param) {
			$err = new Errors();

			$sql = MySql::conectar()->prepare("SELECT * FROM `tournaments` WHERE sport=? ");
        	$sql->execute(array($param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
				$res = [];
				$i = 0;
				while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);
	            	$res[$i] = Returns::tournamentReturn($data);
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
	        }//ok
		}

		public function startTournament($param, $jwt) {
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['id_tournament']) || !isset($data['start'])) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				$id_tournament = $data['id_tournament'];
				$start = $data['start'];

				$verifyPermissions = MySql::conectar()->prepare("SELECT * FROM `tournaments` WHERE id_tournament=?");
				$verifyPermissions->execute(array($id_tournament));

				if($verifyPermissions->rowCount() == 0) {
			        $err = $err->getError("ERR_TOURNAMENT_NOT_FOUND");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				$jwt = (array)$jwt;

				$verifyPermissions= $verifyPermissions->fetch(PDO::FETCH_ASSOC);
				if($verifyPermissions['owner_id'] != $jwt['id']) {
			        $err = $err->getError("ERR_UNAUTHORIZED");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				$match = MySql::conectar()->prepare("SELECT * FROM `matches` WHERE id_tournament=?");
				$match->execute(array($id_tournament));

				if($match->rowCount() >= 1) {
			        $err = $err->getError("ERR_TOURNAMENT_STARTED");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				$teams = MySql::conectar()->prepare("SELECT * FROM `team_tournament` WHERE id_tournament = ?");
				$teams->execute(array($id_tournament));

				if($teams->rowCount() == 0) {
			        $err = $err->getError("ERR_TEAM_NOT_FOUND");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}
				$teams = $teams->fetchAll(PDO::FETCH_ASSOC);

				$countTeams = count($teams);
				if(($countTeams && ($countTeams - 1)) == 0) {
			        $err = $err->getError("ERR_TOURNAMENT_TEAM_TOTAL");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				shuffle($teams);
				$matches = array_chunk($teams, 2);

				$time = [];
				$days = 60 * 60 * 24 * $start;
				$minimumTime = time() + $days;
				$mininumDate = date("Y-m-d H:i:s", $minimumTime);
				$string = "INSERT INTO `matches` (id_team1, id_team2, id_tournament, time, type) VALUES ";

				$values = [];

				foreach ($matches as $key => $value) {
					// if(count($matches) != $key + 1) {
					// 	$string = $string."(".$value[0]['id_team'].",".$value[1]['id_team'].",".$id_tournament.",), ";
					// } else {
					// 	$string = $string."(".$value[0]['id_team'].",".$value[1]['id_team'].",".$id_tournament.",)";
					// }

					if(count($matches) != $key + 1) {
						$string = $string."(?,?,?,?,?), ";
					} else {
						$string = $string."(?,?,?,?,?)";
					}

					$time[$key] = $mininumDate;

					array_push($values, $value[0]['id_team']);
					array_push($values, $value[1]['id_team']);
					array_push($values, $id_tournament);
					array_push($values, $time[$key]);
					array_push($values, 1);
				}

				$firstMatches = MySql::conectar()->prepare($string);
				$firstMatches->execute($values);
				$query = "INSERT INTO `matches` (id_tournament, time, type, last_match) VALUES ";

				$i = 2;
				$values2 = [];
				while(count($matches) != 1) {
					$matches = array_chunk($matches, 2);
					$query = $query."(?, ?, ?, ?)";
					array_push($values2, $id_tournament);
					array_push($values2, $mininumDate);
					array_push($values2, $i);
					if(count($matches) == 1) {
						array_push($values2, true);
					} else {
						array_push($values2, false);
					}
					$i++;
				}

				$otherMatches = MySql::conectar()->prepare($query);
				$otherMatches->execute($values2);

				// $updateTournament = MySql::conectar()->prepare("UPDATE `tournaments` SET started=? WHERE id_tournament=?");
				// $updateTournament->execute(array(true, $id_tournament));

				// $allMatches = MySql::conectar()->prepare("SELECT * FROM `matches` LEFT JOIN `team_tournament` AS team ON matches.id_team1=team.id_team INNER JOIN `team_tournament` AS team2 ON matches.id_team1=team2.id_team WHERE matches.id_tournament=?");
				$allMatches = MySql::conectar()->prepare("SELECT * FROM `matches` LEFT JOIN `team_tournament` AS team ON matches.id_team1=team.id_team WHERE matches.id_tournament=? ORDER BY id_match");
				$allMatches->execute(array($id_tournament));
			// INNER JOIN `team_tournament` ON matches.id_team2 = team_tournament.id_team 
				$res = [];
	        	$i=0;
	            while($data=$allMatches->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);

	            	// $res[$i] = $data;
	            	$res[$i] = Returns::Match($data);
	                $i++;
	            }

            	$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);


			} else {
		        $err = $err->getError("ERR_INVALID_DATA");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
			}		
		}//falta determinar as datas!!
		public function endTournament(){
			//finalizar as partidas e determinar os vencedores
		}
		public function getTournamentMatches($param){
			$err = new Errors();

			$sql = MySql::conectar()->prepare("
			SELECT matches.*, team1.name_team AS name_team1, team2.name_team AS name_team2, tournaments.* FROM `matches` 
			INNER JOIN team_tournament AS team1 ON team1.id_team=matches.id_team1 
			INNER JOIN team_tournament AS team2 ON team2.id_team=matches.id_team2 
			INNER JOIN tournaments ON tournaments.id_tournament=matches.id_tournament
			WHERE matches.id_tournament=? ");
			// $sql = MySql::conectar()->prepare("SELECT * FROM `matches` INNER JOIN tournaments ON `tournaments.id_tournament`=`matches.id_tournament` WHERE id_tournament=:id_tournament ");
        	$sql->execute(array($param));

        	if(($sql) AND ($sql->rowCount() != 0)) {
        		$res = [];
        		$i = 0;
	            while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);
					// $res[$i] = $data;
	            	$res[$i] = Returns::Match($data);
	            	$i++;
	            }
	            $response = ["data"=>$res];

	            http_response_code(200);
	            echo json_encode($response);

	        } else {
		        $err = $err->getError("ERR_MATCH_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }
		}//ok

		public function finishMatch($param, $jwt) {
			$post = file_get_contents("php://input");
			$err = new Errors();

			if($post) {
				$data = json_decode($post, true);

				if(!isset($data['team_1']) || !isset($data['team_2']) || !isset($data['id_match'])) {
			        $err = $err->getError("ERR_INVALID_DATA");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				$team_1 = $data['team_1'];
				$team_2 = $data['team_2'];
				$id_match = $data['id_match'];

				if($team_1 == $team_2) {
			        $err = $err->getError("ERR_VALUES_EQUAL");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;
				}

				$verifyUpdate = MySql::conectar()->prepare("SELECT * FROM matches WHERE id_match=?");
				$verifyUpdate->execute(array($id_match));

				// AND result IS NOT NULL
				$verifyUpdate=$verifyUpdate->fetch(PDO::FETCH_ASSOC);

				if($verifyUpdate['result'] != null) {
			        $err = $err->getError("ERR_MATCH_UPDATED");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;	
				}

				if($verifyUpdate['id_team1'] == null || $verifyUpdate['id_team2'] == null) {
			        $err = $err->getError("ERR_UNDEFINED_MEMBERS_MATCH");
			        $res = ["errors"=> [$err]];

			        http_response_code($err['status']);
			        echo json_encode($res);
        			exit;
				}

				$result = $team_1."x".$team_2;

				$updateMatch = MySql::conectar()->prepare("UPDATE `matches` SET result=?, finish=? WHERE id_match=?");
				$date = date("Y-m-d H:i:s"); 
				$updateMatch->execute(array($result, $date, $id_match));

				$match = MySql::conectar()->prepare("SELECT * FROM `matches` WHERE id_match = ?");
				$match->execute(array($id_match));

				$match = $match->fetch(PDO::FETCH_ASSOC);

				// $res = ["data"=>Returns::Match($match)];

				$winner = $team_1 > $team_2 ? $match['id_team1'] : $match['id_team2'];
				$second = $team_1 > $team_2 ? $match['id_team2'] : $match['id_team1'];
				$id_tournament = $match['id_tournament'];

				if($match['last_match'] == true) {
					//determina as stats
					$finishTournament = MySql::conectar()->prepare("INSERT INTO `stats_tournament` (winner, second_place, id_tournament) VALUES(?,?,?)");
					$finishTournament->execute(array($winner, $second, $id_tournament));

					#setar as equipes como nao ativas
					// $updateTournament = MySql::conectar()->prepare("UPDATE tournaments SET active=? WHERE id_tournament=?");
					// $updateTournament->execute(array(false, $id_tournament));

					// $updateTeamTournament = MySql::conectar()->prepare("UPDATE team_tournament SET active=? WHERE id_tournament=?");
					// $updateTeamTournament->execute(array(false, $id_tournament));

					// $updateMemberTournament = MySql::conectar()->prepare("UPDATE team_tournament_members SET member_active=? WHERE id_tournament=?");
					// $updateMemberTournament->execute(array(false, $id_tournament));

					$res = ["data"=>"Campeonato finalizado com vitoria do time de id ".$winner];

			        http_response_code(200);
			        echo json_encode($res);
					exit;
				}

				$nextMatch = MySql::conectar()->prepare("SELECT * FROM matches WHERE id_tournament=? AND id_team1 IS NULL OR id_team2 IS NULL LIMIT 1");
				$nextMatch->execute(array($id_tournament));
				$nextMatch = $nextMatch->fetch(PDO::FETCH_ASSOC);

				$update = $nextMatch['id_team1'] == null ? "id_team1" : "id_team2";
				$updateNextMatch = MySql::conectar()->prepare("UPDATE matches SET $update=? WHERE id_match=?");
				$updateNextMatch->execute(array($winner, $nextMatch['id_match']));

				$nextMatchValues = MySql::conectar()->prepare("SELECT * FROM `matches` WHERE id_match=?");
				$nextMatchValues->execute(array($nextMatch['id_match']));
				$nextMatchValues=$nextMatchValues->fetch(PDO::FETCH_ASSOC);

				#retorna os valores da PROXIMA partida!!

				$res = ["data"=>Returns::Match($nextMatchValues)];

				echo json_encode($res);
				http_response_code(200);

			} else {
		        $err = $err->getError("ERR_INVALID_DATA");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
			}	
		}
		public function findMatch($param) {
			$err = new Errors();

			$sql = MySql::conectar()->prepare("
			SELECT matches.*, team1.name_team AS name_team1, team2.name_team AS name_team2, tournaments.* FROM `matches` 
			INNER JOIN team_tournament AS team1 ON team1.id_team=matches.id_team1 
			INNER JOIN team_tournament AS team2 ON team2.id_team=matches.id_team2 
			INNER JOIN tournaments ON tournaments.id_tournament=matches.id_tournament
			WHERE id_match=? ");
        	$sql->execute(array($param));

	        if(($sql) AND ($sql->rowCount() != 0)) {
	            $data = $sql->fetch(PDO::FETCH_ASSOC);
				// $res = $data;
	            $res = Returns::Match($data);

				$response = ["data"=>$res];
            	echo json_encode($response);
            	http_response_code(200);
	        } else {
		        $err = $err->getError("ERR_TOURNAMENT_NOT_FOUND");
		        $res = ["errors"=> [$err]];

		        http_response_code($err['status']);
		        echo json_encode($res);
				exit;
	        }//ok
		}
		public function findMatchMembers($param) {
			$err = new Errors();

			$sql = MySql::conectar()->prepare("
			SELECT * FROM team_tournament_members AS member
			INNER JOIN matches ON matches.id_match=:id_match
			INNER JOIN users ON users.id=member.id_user
			INNER JOIN team_tournament AS team ON team.id_team=member.id_team
			WHERE member.id_team=matches.id_team1 OR member.id_team=matches.id_team2
			ORDER BY member.id_team
			");
			$sql->bindParam(":id_match", $param);
        	$sql->execute();

	        if(($sql) AND ($sql->rowCount() != 0)) {
				$res = [];
				$i = 0;
				while($data=$sql->fetch(PDO::FETCH_ASSOC)){
	                // extract($pesquisa);

					// $res[$i] = $data;
	            	$res[$i] = Returns::MatchMember($data);
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
	        }//ok
		}

	}
?>