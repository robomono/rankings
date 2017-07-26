<?php

class FSBasics{

    public function __construct(){

        session_start();
				require_once("../config/db.php");
				
    }
		
		
		
		
		public function getUsersLeague(){
			
			$user_id = $_SESSION['user_id'];
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {$this->errors[] = $this->db_connection->error;}
		
			if (!$this->db_connection->connect_errno) {

				$sql = "SELECT l.league_id, l.league_name, l.admin, l.status, l.random
								FROM league_control c
								LEFT JOIN leagues AS l
								ON c.league_id = l.league_id
								WHERE c.user_id = $user_id";
						
				
	      $result = $this->db_connection->query($sql);
		
				while($row = mysqli_fetch_array($result)){
					
					$league['id'] = $row['league_id'];
					$league['name'] = $row['league_name'];
					$league['admin'] = $row['admin'];
					$league['status'] = $row['status'];
					$league['random'] = $row['random'];
				}
				
				return $league;		
			
			}//connection errors
			
		}//get league status
		
		public function checkForRunningEvents(){
						
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {$this->errors[] = $this->db_connection->error;}
		
			if (!$this->db_connection->connect_errno) {
				
				$sql = "SELECT id FROM events WHERE status=3";
				
        $running_event = $this->db_connection->query($sql);

				return $running_event->num_rows;		
			
			}//connection errors
			
		}//checks for running events
		
		public function getAllEvents(){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {$this->errors[] = $this->db_connection->error;}
		
			if (!$this->db_connection->connect_errno) {
				
				$sql = "SELECT id,name,status FROM events ORDER BY id";
				
				$result = $this->db_connection->query($sql);
				
				while($row = mysqli_fetch_array($result)){
					
					$byid[$row['id']]['name'] = $row['name'];
					$byid[$row['id']]['status'] = $row['status'];
					
					$bystatus[$row['status']][] = $row['id'];

					if($laststatus==4 && $row['status']!=4){
						$lastevent = $lasteventid;
						$nextevent = $row['id'];
					}
					
					if($row['status']!=0 && $row['status']!=4){
						$currentevent = $row['id'];
					}
					
					$laststatus = $row['status'];$lasteventid = $row['id'];
					
				}	
			
				$events['byid'] = $byid;
				$events['bystatus'] = $bystatus;
				$events['last'] = $lastevent;
				$events['next'] = $nextevent;
				$events['current'] = $currentevent;
				
				return $events;
			
			}//connection errors
			
		}//checks for running events
			
		public function getSurfersWithRankings(){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }
		
			if (!$this->db_connection->connect_errno) {

				//get events
				$events = $this->getAllEvents();
				
				//find latest completed event
				$latestevent = 0;//control for latest event
				foreach($events as $eid=>$v1){
					if($v1['status']==4 && $eid>$latestevent){
						$latestevent = $eid;
					}					
				}
				
				$sql = "SELECT s.id,s.name,s.img,s.aka,s.status,s.wildcard,s.for_event,r.rank_after
								FROM surfers s
								LEFT JOIN surfer_scores AS r
								ON s.id = r.surfer_id
								WHERE r.event = $latestevent;";
								
				$result = $this->db_connection->query($sql);
				
				while($row = mysqli_fetch_array($result)){
						
					$thissurfer = $row['id'];
					$surfers[$thissurfer]['name'] = $row['name'];
					$surfers[$thissurfer]['img'] = $row['img'];
					$surfers[$thissurfer]['aka'] = $row['aka'];
					$surfers[$thissurfer]['status'] = $row['status'];
					$surfers[$thissurfer]['wildcard'] = $row['wildcard'];
					$surfers[$thissurfer]['forevent'] = $row['forevent'];
					$surfers[$thissurfer]['rank'] = $row['rank_after'];
						
				}
				
				return $surfers;
			
			}
			
		}//get surfers and latest ranking
		
		public function getSurfers(){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }
		
			if (!$this->db_connection->connect_errno) {

				$sql = "SELECT * FROM surfers;";
			
	      $result = $this->db_connection->query($sql);
		
				while($row = mysqli_fetch_array($result)){
					$thissurfer = $row['id'];
					$surfers[$thissurfer]['name'] = $row['name'];
					$surfers[$thissurfer]['img'] = $row['img'];
					$surfers[$thissurfer]['aka'] = $row['aka'];
					$surfers[$thissurfer]['status'] = $row['status'];
					$surfers[$thissurfer]['wildcard'] = $row['wildcard'];
					$surfers[$thissurfer]['forevent'] = $row['forevent'];
				}
				
				return $surfers;
			
			}
							
		}//get surfers
		
		public function getOpenEvent(){
			
		      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		      if (!$this->db_connection->set_charset("utf8")) {
		          $this->errors[] = $this->db_connection->error;
		      }

		      if (!$this->db_connection->connect_errno) {
      	
						$sql = "SELECT id,name,status FROM events WHERE (status!=0 AND status!=4);";
						$result = $this->db_connection->query($sql);
						while($row = mysqli_fetch_array($result)){
							$event['id'] = $row['id'];
							$event['name'] = $row['name'];
							$event['status'] = $row['status'];
						}
				
						return $event;
				
		      }
			
		}
		
		public function getEventResults($event_id){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }
			
			if (!$this->db_connection->connect_errno) {
				
				$sql = "SELECT surfer_id,position
								FROM surfer_scores
								WHERE event = $event_id
								ORDER BY position";
	      $result = $this->db_connection->query($sql);
				
				while($row = mysqli_fetch_array($result)){
					$sid = $row['surfer_id'];
					$pos = $row['position'];
					
					$results[$pos][] = $sid;
				}
			
			}//connection errors
			
			return $results;
			
		}
		
		public function getLeagueUsers($league){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {$this->errors[] = $this->db_connection->error;}
		
			if (!$this->db_connection->connect_errno) {
				
				if(empty($league['id'])){$league_id=$league;}elseif(!empty($league['id'])){$league_id = $league['id'];}
			
				$sql = "SELECT l.user_id, u.user_name,u.user_email,u.user_team,l.draft_order
									FROM league_control l
									LEFT JOIN users AS u
									ON l.user_id = u.id
									WHERE l.league_id=$league_id;";
								
				$result = $this->db_connection->query($sql);
				while($row = mysqli_fetch_array($result)){
					$members[$row['user_id']]['name'] = $row['user_name'];
					$members[$row['user_id']]['email'] = $row['user_email'];
					$members[$row['user_id']]['team'] = $row['user_team'];
					$members[$row['user_id']]['order'] = $row['draft_order'];
				}
			
				return $members;
				
			}//connection errors

		}//get all users, names, emails and teams in league
		
		public function getAvailableSurfers($members){
			
			$leaguesize = 0;
			foreach($members as $uid=>$v1){
				if(!empty($v1['name'])){
					$leaguesize++;
				}
			}
			
//			$availables_max = ceil((10*$leaguesize)/34);
//			$availables_min = $availables_max - 1;
				
//				if($leaguesize == 9){
					$top5 = 2;$next10 = 3; $lowerhalf = 4;
//				}

				
//			$top5 = 1;$next10 = 1;$lowerhalf = 2;
			
//			while(((($top5*5)+($next10*10)+($lowerhalf*19))/$leaguesize)<10){
//				if($top5==$next10){$next10++;$lowerhalf++;}
//				elseif($next10>$top5){$top5++;}
//			}
			
			$surfers = $this->getSurfers();
			
			foreach($surfers as $sid=>$v){
				if($sid<=1005){
					$surfers[$sid]['available'] = $top5;
				}elseif($sid>1015){
					$surfers[$sid]['available'] = $lowerhalf;
				}elseif($sid<=1015 && $sid>1005){
					$surfers[$sid]['available'] = $next10;
				}
			}

//			foreach($surfers as $sid=>$v){
//				if($sid>1015){
//					$surfers[$sid]['available'] = $availables_max;
//				}else{
//					$surfers[$sid]['available'] = $availables_min;
//				}
//			}
			
			return $surfers;
			
		}
		
		public function getSurferRankings($availsurfers){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }
		
			if (!$this->db_connection->connect_errno) {

				//get events
				$events = $this->getAllEvents()['byid'];
				
				//find latest completed event
				$latestevent = 0;//control for latest event
				foreach($events as $eid=>$v1){
					if($v1['status']==4 && $eid>$latestevent){
						$latestevent = $eid;
					}					
				}
				
				$sql = "SELECT surfer_id,rank_after
								FROM surfer_scores
								WHERE event = $latestevent;";
								
				$result = $this->db_connection->query($sql);
				
				while($row = mysqli_fetch_array($result)){
					$availsurfers[$row['surfer_id']]['rank'] = $row['rank_after'];
				}
				
				return $availsurfers;
				
			}
			
		}
		
}//class Basics
