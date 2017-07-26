<?php

class WSL{

    private $db_connection = null;
    public $errors = array();
    public $messages = array();

    public function __construct(){
       
			 	//include_once("fsbasics.php");	
				require_once("../config/db.php");
				session_start();
				
        
				
    }
		
		
	public function calculateRankings(){
		
		$user_id = 113; //eventually get userr id using session
		$last_event = 6; //eventually get event using last event in status 2 / 3 / 4
		$league_id = 1; //eventually get league id from users info
				
		$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	
		if (!$this->db_connection->set_charset("utf8")) {$this->errors[] = $this->db_connection->error;}
		
		if (!$this->db_connection->connect_errno) {
			
			$sql = "SELECT user_id, pick_id
					FROM league_picks WHERE event = $last_event AND league_id = $league_id AND wc=0";

			$result = $this->db_connection->query($sql);

			while($row = mysqli_fetch_array($result)){
				$picks[$row['pick_id']][] = $row['user_id'];
				$count[$row['pick_id']] += 1;
				
				if($row['user_id'] == $user_id){$userpicks[$row['pick_id']] = 1;}
				
			}
			
			$sql = "SELECT w.id, w.name, w.aka, s.event, s.position, s.points
							FROM surfers w
							LEFT JOIN surfer_scores AS s
							ON w.id = s.surfer_id
							WHERE s.event>0";
					
			
     		$result = $this->db_connection->query($sql);
	
			while($row = mysqli_fetch_array($result)){
				$scores[$row['id']]['name'] = $row['name'];
				$scores[$row['id']]['aka']  = $row['aka'];
				$scores[$row['id']][$row['event']]['pos'] = $row['position'];
				$scores[$row['id']][$row['event']]['pts'] = $row['points'];
				$scores[$row['id']]['total'] += $row['points'];
				
				if($userpicks[$row['id']]==1){
					$scores[$row['id']]['picked'] = 1;
				}
				
				if($row['id']<=1005){
					$scores[$row['id']]['avail'] = 2 - $count[$row['id']];
				}
				elseif($row['id']>1015){
					$scores[$row['id']]['avail'] = 4 - $count[$row['id']];
				}
				elseif($row['id']<=1015 && $row['id']>1005){
					$scores[$row['id']]['avail'] = 3 - $count[$row['id']];
				}
				
			}
			
			return $scores;
			
		}//connection errors
	
	}
	
	
	public function getWslRankings(){
		
		$scores = $this->calculateRankings();
		print_r($scores);
		echo "</br>";
		foreach($scores as $sid=>$v1){
			echo "$sid - " .$v1['name'] ." - " .$v1['total'] ."</br>";
		}
		
	}	
		
		
}//Class WSL
?>