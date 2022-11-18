<?php 
    class MySql{
        private static $pdo;

		public static function conectar(){
			if(self::$pdo == null){
				try{
				self::$pdo = new PDO('mysql:host='.HOST.';dbname='.DATABASE,USER,PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				self::$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
				}catch(Exception $e){
					echo '<h2>Erro ao se conectar com o banco de dados!</h2>';
				}
			}

			return self::$pdo;
		}
        public static function query($q){
            echo 'sim';
        }
        public static function getLastId(){
    		return MySql::conectar()->lastInsertId();
		}
    }

?>