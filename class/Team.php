<?php 
	class Team {
		public function post($param, $jwt) {
			echo "Criar Equipe";
		}
		public function get() {
			echo "Receber equipes";
		}
		public function find($param) {
			echo "Procurar equipe";
		}
		public function put() {
			echo "Atualizar equipe";
		}
		public function delete() {
			echo "deletar equipe";
		}
		public function patch(){
			echo "Alterar foto da equipe";
		}
		public function getMembers(){
			echo "Receber membros da equipe";
		}
	}
?>