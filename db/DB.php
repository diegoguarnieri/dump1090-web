<?php

class DB {

	var $user = "root";
	var $pass = "root";
	var $driver = "mysql";
	var $host = "localhost";
	var $db = "pi";
	var $con;

	function DB() {
      $this->con = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
      
      if(mysqli_connect_errno()) {
         echo "Failed to connect to MySQL: " . mysqli_connect_errno();
      }
	}
	
	function execSQL($sql){
      $res = mysqli_query($this->conexao, $sql);
      return $res;
	}
   
   function getLastInsertId() {
      return mysqli_insert_id($this->conexao);
   }
   
   function getAffectedRows() {
      return mysqli_affected_rows($this->conexao);
   }
   
   function getFetch($res) {
      return mysqli_fetch_array($res);
   }
}
?>

