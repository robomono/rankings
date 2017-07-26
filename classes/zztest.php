<?php
session_start();
require_once("../config/db.php");

function doit(){
	//$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

	// change character set to utf8 and check it
	if (!$this->db_connection->set_charset("utf8")) {
	    $this->errors[] = $this->db_connection->error;
	}

	if (!$this->db_connection->connect_errno) {	
	      $sql = "UPDATE users SET email='ted@gmail.com'
								WHERE id='101'";
	      $query_new_user_insert = $this->db_connection->query($sql);		
	}

	echo "done";
}

$doit = doit();

	
?>