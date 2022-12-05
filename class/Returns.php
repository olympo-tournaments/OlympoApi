<?php 
	class Returns {
		public static function tournamentReturn($tournament){
			// print_r($tournament);
			$response = [
            	"type"=> "tournament",
            	"id"=> $tournament['id_tournament'],
            	"attributes"=>[
            		"name"=>$tournament['name'],
            		"description"=>$tournament['description'],
            		"sport"=>$tournament['sport'],
            		"photo"=>$tournament['photo'],
            		"invitation"=>$tournament['invitation'],
            		// "team_members"=>$tournament['team_members'],
            		"owner"=>$tournament['owner_id'],
            		"active"=>$tournament['active'],
            		"type"=>$tournament['type'],
            		"twitch"=>$tournament['twitch'],
            	],
            	"links"=>[
            		"self"=>"/tournament/".$tournament['id_tournament']
            	]
            ];
            return $response;
		}

		public static function userTournamentReturn($tournament){
			// $response = $tournament;
			$response = [
            	"type"=> "tournament",
            	"id"=> $tournament['id_tournament'],
            	"attributes"=>[
            		"name"=>$tournament['name'],
            		"description"=>$tournament['description'],
            		"sport"=>$tournament['sport_name'],
            		"photo"=>$tournament['photo'],
            		"invitation"=>$tournament['invitation'],
            		// "team_members"=>$tournament['team_members'],
            		"owner"=>$tournament['owner_id'],
            		"active"=>$tournament['active'],
            		"team"=>[
            			"id"=>$tournament['id_team'],
            			"name_team"=>$tournament['name_team'],
	            		"tag"=>$tournament['tag']
            		]
            	],
            	"links"=>[
            		"self"=>"/tournament/".$tournament['id_tournament']
            	]
            ];
            return $response;
		}
		public static function CategoriesReturn($category){
			$response = [
				"type"=> "sport",
            	"id"=> $category['id_sport'],
            	"attributes"=>[
            		"name"=>$category['sport_name'],
            		"sport_members"=>$category['sport_members'],
            		"image"=>$category['image'],
            	],
            	"links"=>[
            		"self"=>"/tournament/category/".$category['id_sport']
            	]
			];
			return $response;
		}

		public static function TeamReturn($team) {
			$res = [
            	"type"=> "team",
            	"id"=> $team['id_team'],
            	"attributes"=>[
            		"name"=>$team['name_team'],
            		"active"=>$team['active'],
            		"id_tournament"=>$team['id_tournament'],
            		"owner"=>$team['owner']
            	],
            	"links"=>[
            		"self"=>"/team/".$team['id_team']
            	]
            ];
            return $res;
		}

		public function userReturn($user) {
			$res = [
            	"type"=> "user",
            	"id"=> $user['id'],
            	"attributes"=>[
            		"name"=>$user['name'],
            		"email"=>$user['email'],
            		"username"=>$user['username'],
            		"photo"=>$user['photo']
            	],
            	"links"=>[
            		"self"=>"/user/".$user['username']
            	]
            ];
            return $res;
		}

		public static function TeamMembersReturn($member) {
			$res = [
            	"type"=> "team-members",
            	"id"=> $member['id_user'],
            	"attributes"=>[
            		"name"=>$member['name'],
            		"email"=>$member['email'],
            		"username"=>$member['username'],
            		"photo"=>$member['photo'],
            		"tag"=>$member['tag']
            	],
            	"links"=>[
            		"self"=>"/user/".$member['id_user']
            	]
            ];
            return $res;
		}

		public static function Match($match) {
			$res = [
            	"type"=> "match",
            	"id"=> $match['id_match'],
            	"attributes"=>[
            		"team2"=>$match['id_team2'],
            		"id_tournament"=>isset($match['name']) ? [
						"name"=>$match['name'],
						"photo"=>$match['photo'],
						"sport"=>$match['sport'],
						"invitation"=>$match['invitation'],
						"id"=>$match['id_tournament'],
						"owner"=>$match['owner_id'],
						"active"=>$match['active'],
					] : $match['id_tournament'],
            		"time"=>$match['time'],            		
            		"result"=>$match['result'],   
            		"type"=>$match['type']     ,
            		// "team1"=>isset($match['name_team']) ? [
            		// 	"name"=>$match['name_team'],
	            	// 	"id"=>$match['id_team1'],
	            	// 	"active"=>$match['active'],
	            	// 	"id_tournament"=>$match['id_tournament'],
	            	// 	"owner"=>$match['owner']
            		// ] : $match['id_team1'],   
					"team1"=>isset($match['name_team1']) ? [
            			"name"=>$match['name_team1'],
	            		"id"=>$match['id_team1'],
            		] : $match['id_team1'],  
					"team2"=>isset($match['name_team2']) ? [
            			"name"=>$match['name_team2'],
	            		"id"=>$match['id_team2'],
            		] : $match['id_team1'],   
            		"last_match"=>$match['last_match'] == 1 ? true : false 		
            	],
            	"links"=>[
            		"self"=>"/match/".$match['id_match']
            	]
			];
			return $res;
		}
		public static function MatchMember($member) {
			$res = [
				"type"=> "member-members",
            	"id"=> $member['id_user'],
            	"attributes"=>[
            		"name"=>$member['name'],
            		"email"=>$member['email'],
            		"username"=>$member['username'],
            		"photo"=>$member['photo'],
            		"tag"=>$member['tag'],
					"team"=>[
						"id_team"=>$member['id_team_member'],
						"name_team"=>$member['name_team'],
					]
            	],
            	"links"=>[
            		"self"=>"/user/".$member['id_user']
            	]
			];
			// $res = $member;
			return $res;
		}
	}
?>