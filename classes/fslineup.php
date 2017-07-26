<?php

class FSLineup{

    public function __construct(){

        session_start();
				require_once("../config/db.php");
				
    }
		
		public function duplicateUsersLineup($user_id,$league_id,$event_id,$picks){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
			
				if($event_id==1){
				
					//first event - get picks from draft
					$sql = "SELECT pick_id,turn FROM draft_picks 
									WHERE user_id='$user_id' AND league_id='$league_id' ORDER BY turn;";
				
					$result = $this->db_connection->query($sql);
					
					$i=0;
					while($row = mysqli_fetch_array($result)){
						$newpicks[$row['pick_id']]['status'] = 0;
						$newpicks[$row['pick_id']]['priority'] = $i;
						$newpicks[$row['pick_id']]['wc'] = 0;
						$i++;
					}
					
					
				
				}//first event - get from draft
				
				elseif($event_id>1){
				
					//filter out wc picks from last event and create new sequential order
					$i=0;
					foreach($picks as $pid=>$v1){
						if($v1['wc']==0){
							$newpicks[$pid]['status'] = $v1['status'];
							$newpicks[$pid]['priority'] = $i;
							$newpicks[$pid]['wc'] = $v1['wc'];
							$i++;
						}
					}
				
				}//recurring event
				
				//do insertion
				if(!empty($newpicks)){
				
					//INSERT LIBEUP
					$query = "INSERT INTO league_picks (user_id,league_id,event,pick_id,active) VALUES ($user_id,$league_id,$event_id,?,?)";
					$stmt = $this->db_connection->prepare($query);
					$stmt ->bind_param("ii",$pick_id,$active);
					
					$this->db_connection->query("START TRANSACTION");
					
					foreach ($newpicks as $key => $item) {

						$pick_id 	= $key;
						$active 	= $item['priority'];
						$stmt->execute();

					}
					
					$stmt->close();
					$this->db_connection->query("COMMIT");
					
					return $newpicks;
					
				}
				
			}//connection errors
			
		}
		
		public function getUsersLineup($user_id,$league,$events){
			
			//extracts userid and leagueid from input, wether its array or int
			if(!empty($league['id'])){$league_id = $league['id'];}else{$league_id = $league;}
			
			if(!empty($events['current'])){$event_id = $events['current'];}
			elseif(!empty($events['next'])){$event_id = $events['next'];}
			else($event_id = $events);

			$prevevent = $event_id-1;
			
			if($user_id == $_SESSION['user_id']){
			
	      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

	      if (!$this->db_connection->set_charset("utf8")) {
	          $this->errors[] = $this->db_connection->error;
	      }

	      if (!$this->db_connection->connect_errno) {
      	
					$sql = "SELECT event,pick_id,status,active,wc FROM league_picks 
									WHERE user_id='$user_id' AND league_id='$league_id' AND (event='$event_id' OR event='$prevevent')
									ORDER BY event,active;";
				
					$result = $this->db_connection->query($sql);
				
					while($row = mysqli_fetch_array($result)){
						$picks[$row['event']][$row['pick_id']]['status'] = $row['status'];
						$picks[$row['event']][$row['pick_id']]['priority'] = $row['active'];
						$picks[$row['event']][$row['pick_id']]['wc'] = $row['wc'];
					}
					
					
					if(empty($picks[$event_id])){
						$picks[$event_id] = $this->duplicateUsersLineup($user_id,$league_id,$event_id,$picks[$prevevent]);
					}
				
					return $picks[$event_id];
					
				}
				
			}//check that user is logged in
			
		}//get users lineup
			
		public function getAvailableWildcards($events,$requests){
			
			$event_id = $events['current'];
			if(!empty($events['current'])){$event_id = $events['current'];}else{$event_id = $events;}
			
			if(!empty($_SESSION['user_id']) && !empty($event_id)){
				
				$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

	      if (!$this->db_connection->set_charset("utf8")) {
	          $this->errors[] = $this->db_connection->error;
	      }

	      if (!$this->db_connection->connect_errno) {
				
					$sql = "SELECT id,status,wildcard FROM surfers WHERE wildcard>0 AND for_event=$event_id;";
					$result = $this->db_connection->query($sql);
					while($row = mysqli_fetch_array($result)){
						
						if(empty($requests[$row['id']])){
							$wildcards[$row['id']]['status'] = $row['status'];
							$wildcards[$row['id']]['type'] = $row['wildcard'];
						}
						
					}
					
					return $wildcards;
				
				}//check for errors
				
			}//check that user is logged in
			
		}//get event wildcards
		
		public function getUsersWildcardRequests($user,$league,$event){
			
			if(!empty($user['id'])){$user_id = $user['id'];}else{$user_id = $user;}
			if(!empty($league['id'])){$league_id = $league['id'];}else{$league_id = $league;}
			if(!empty($event['current'])){$event_id = $event['current'];}else{$event_id = $event;}
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
					
				$sql = "SELECT wc_id,type,priority,approved,turn FROM wildcard_requests 
								WHERE user_id=$user_id AND league_id=$league_id AND event_id=$event_id ORDER BY priority;";
				$result = $this->db_connection->query($sql);
				
				$lastpriority = 0;
				while($row = mysqli_fetch_array($result)){
					$requests[$row['wc_id']]['type'] = $row['type'];
					$requests[$row['wc_id']]['priority'] = $row['priority'];
					$requests[$row['wc_id']]['approved'] = $row['approved'];
					$requests[$row['wc_id']]['turn'] = $row['turn'];
				}
				$lastpriority++;//adds 1 to highest recorded priority
				
				return $requests;
			}
			
			
		}
		
		public function getAllWildcardRequests($league,$event){
			
			if(!empty($league['id'])){$league_id = $league['id'];}else{$league_id = $league;}
			if(!empty($event['current'])){$event_id = $event['current'];}else{$event_id = $event;}
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
					
				$sql = "SELECT w.user_id,w.wc_id,w.type,w.priority,w.approved,w.turn,s.name,u.user_name,u.user_team
								FROM wildcard_requests w
								LEFT JOIN surfers AS s
								ON w.wc_id = s.id
								LEFT JOIN users AS u
								ON w.user_id = u.id
								WHERE league_id=$league_id AND event_id=$event_id ORDER BY turn;";
				$result = $this->db_connection->query($sql);
				
				while($row = mysqli_fetch_array($result)){
					$requests[$row['turn']]['uid'] = $row['user_id'];
					$requests[$row['turn']]['user_name'] = $row['user_name'];
					$requests[$row['turn']]['user_team'] = $row['user_team'];
					$requests[$row['turn']]['wid'] = $row['wc_id'];
					$requests[$row['turn']]['wc_name'] = $row['name'];
					$requests[$row['turn']]['priority'] = $row['priority'];
					$requests[$row['turn']]['approved'] = $row['approved'];
				
				}
				
				return $requests;
			}
			
		}
		
		public function calculateAvailableWildcards($lineup,$wildcards,$requests){
			
			foreach($wildcards as $wid=>$v1){		
				
				if(empty($requests[$wid])){
					
					if($v1['type']==1){//if its an injury wc
						if(!empty($lineup[$v1['status']])){//if surfer replacing is in users lineup
							if(empty($lineup[$wid])){$available[1][] =  $wid;}//avaialble for immediate pick
						}elseif(empty($lineup[$v1['status']])){//surfer being replaced isn't in users lineup
							if(empty($wildcard_requests[$wid])){//wild card isnt on requested list
								if(empty($lineup[$wid])){$available[3][] =  $wid;}//avaialble request
							}
						}
					}//injury wc
				
				
					elseif($v1['type']==2){//if its a guest wc
						if(empty($wildcard_requests[$wid])){//wild card isnt on requested list
							if(empty($lineup[$wid])){$available[2][] =  $wid;}//avaialble request
						}
					}//guest wc
					
				}
				
			}//parse through wildcards
			
			return $available;
			
		}
		
		public function addInstantWc(){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }
		
			if (!$this->db_connection->connect_errno) {
				
				$data   = $this->db_connection->real_escape_string($_POST['addInstantWc']);
				
				$data_exp = explode(".", $data);
				
				$req = $data_exp[0];
				$event_id = $data_exp[1];
				$league_id = $data_exp[2];
				$repl_id = $data_exp[3];
				$wc_id = $data_exp[4];
				
				$user_id = $_SESSION['user_id'];
								
				if($req=="addinstant" && !empty($repl_id) && !empty($wc_id)){
					
					$highest_pos = 100;//serves as control for the top injured position
					
					$picks = $this->getUsersLineup($user_id,$league_id,$event_id);
					
					//go through picks to find highest positioned surfer (injured are >100)
					foreach($picks as $k1=>$v1){
						if($v1['priority']>=$highest_pos){//updates injured position to insert above other injured surfers;
							$highest_pos = $v1['priority']+1;
						}
					}
					
					$injured_pos = $picks[$repl_id]['priority'];//the position at which the wc will be inserted
					
					if($injured_pos>7){
					
						//make array with all non-wc non-injured surfers
						foreach($picks as $k1=>$v1){
							if(($v1['priority']<=7) && ($v1['wc']==0)){
								//this pick is active and not a wildcard
								$picksbypriority[$v1['priority']] = $k1;
							}
						}
					
						krsort($picksbypriority);//sorts array by highest key first				
						$last_pid = reset($picksbypriority);//gets the first value in the array - last seated surfer
						$last_position = key($picksbypriority);//gets the first key in the array - last available position
					
						//move the injured surfer to the highest availabe position over 100
						//move the last surfer to the bench position were the injured surfer was
						//insert the wildcard at the position the last surfer was at
		      	$sql = "UPDATE league_picks SET status=1, active=$highest_pos WHERE user_id=$user_id AND league_id=$league_id AND event=$event_id AND pick_id=$repl_id";
						$this->db_connection->query($sql);
					
		      	$sql = "UPDATE league_picks SET active=$injured_pos WHERE user_id=$user_id AND league_id=$league_id AND event=$event_id AND pick_id=$last_pid";
						$this->db_connection->query($sql);
					
	          $sql = "INSERT INTO league_picks (user_id,league_id,event,pick_id,active,wc) 
																			VALUES ($user_id,$league_id,$event_id,$wc_id,$last_position,1);";
						$this->db_connection->query($sql);
							
//							$picks[$wc_id]['status'] = 0;
//							$picks[$wc_id]['wc'] = 1;
//							$picks[$wc_id]['priority'] = $last_position;
//							$picks[$repl_id]['priority'] = $highest_pos;
//							$picks[$last_pid]['priority'] = $injured_pos;
							
							return "success";
							
					} //rearrange lineup to avoid wc landing on bench
					
					elseif($injured_pos<=7){
					
						$priority = $picks[$repl_id]['priority'];
	          $sql = "INSERT INTO league_picks (user_id,league_id,event,pick_id,active,wc) 
																			VALUES ($user_id,$league_id,$event_id,$wc_id,$priority,1);";
						$this->db_connection->query($sql);
					
		      	$sql = "UPDATE league_picks SET status=1, active=$highest_pos WHERE user_id=$user_id AND league_id=$league_id AND event=$event_id AND pick_id=$repl_id";
						$this->db_connection->query($sql);
							
							$picks[$wc_id]['status'] = 0;
							$picks[$wc_id]['wc'] = 1;
							$picks[$wc_id]['priority'] = $priority;
							$picks[$repl_id]['priority'] = $highest_pos;
							
							return "success";
							
					}//add wc into active lineup, no rearrange necessary
					
					
				}
				
				
			}//check for connection errors
			
		}
		
		public function removeInstantWc(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
      	
				$data   = $this->db_connection->real_escape_string($_POST['removeInstantWc']);
				
				$data_exp = explode(".", $data);
				
				$user_id = $_SESSION['user_id'];
				$req = $data_exp[0];
				$event_id = $data_exp[1];
				$league_id = $data_exp[2];
				$wcposition = $data_exp[3];
				$wcsurfer = $data_exp[4];
				$injuredsurfer = $data_exp[5];
								 
				if($req=="remove"){
					
					//remove wc from lineup
					$sql = "DELETE FROM league_picks 
									WHERE user_id=$user_id AND event=$event_id AND pick_id=$wcsurfer ;";
					$this->db_connection->query($sql);
				
					//move injured surfer back into position
					$sql = "UPDATE league_picks SET active=$wcposition 
									WHERE user_id=$user_id AND event=$event_id AND pick_id=$injuredsurfer";
					$this->db_connection->query($sql);
					
				}
				
			}//check for connection errors
			
		}
		
		public function requestWc(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
      	
				$data   = $this->db_connection->real_escape_string($_POST['requestWc']);
				
				$data_exp = explode(".", $data);
				
				$user_id = $_SESSION['user_id'];
				$req = $data_exp[0];
				$event_id = $data_exp[1];
				$league_id = $data_exp[2];
				$wc_id = $data_exp[3];
				
				$requests = $this->getUsersWildcardRequests($user_id,$league_id,$event_id);
				
				if($req=="requestguest"){$wc_type = 2;}
				elseif($req="requestrepl"){$wc_type = 1;}
				//calculate the highest priority to add new request after that
				
				
					$lastpriority = 0;
					foreach($requests as $k=>$v){
						$thispriority = $v['priority'];
						if($thispriority>$lastpriority){
							$lastpriority = $thispriority;
						}
					}
					$lastpriority++;
				
				//INSERT REQUEST
        $sql = "INSERT INTO wildcard_requests (user_id,league_id,event_id,wc_id,type,priority) 
																	VALUES ($user_id,$league_id,$event_id,$wc_id,$wc_type,$lastpriority);";
				$this->db_connection->query($sql);
				
			}
			
		}
		
		public function removeRequest(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
      	
				$data   = $this->db_connection->real_escape_string($_POST['removeRequest']);
				
				$data_exp = explode(".", $data);
				
				$user_id = $_SESSION['user_id'];
				$req = $data_exp[0];
				$event_id = $data_exp[1];
				$league_id = $data_exp[2];
				$req_pos = $data_exp[3];
				$req_id = $data_exp[4];
								
				//get current user wildcard requests
				$requests = $this->getUsersWildcardRequests($user_id,$league_id,$event_id);
				
				if($req=="removerequest"){
					
					if($req_pos==sizeof($requests)){
					
						//this is the last or only request, can delete without reshuffling
						$sql = "DELETE FROM wildcard_requests 
										WHERE user_id=$user_id AND league_id=$league_id AND event_id=$event_id AND wc_id=$req_id AND approved=0;";
						$this->db_connection->query($sql);
					
					}
				
					elseif($req_pos<sizeof($requests)){
						//needs to reshuffle and delete
						$change = 0;//works as flag, when its on, priority goes down by 1 (bc its after the deleted wc)
					
						foreach($requests as $wid=>$v){
						
							$priority = $v['priority'];
						
							if($wid==$req_id && $change==0){$change = 1;}//foung user to change, change flag to change the next
						
							elseif($wid!=$req_id && $change==1){$tochange[$wid] = $priority-1;}//change flag is on, p-1
						
						}//goes through requests to create update array
					
					
						//delete the wildcard
						$sql = "DELETE FROM wildcard_requests 
										WHERE user_id=$user_id AND league_id=$league_id AND event_id=$event_id AND wc_id=$req_id AND approved=0;";
						$this->db_connection->query($sql);

					
						//---------DO UPDATE
				
						$query = "UPDATE wildcard_requests SET priority=? 
											WHERE user_id=$user_id AND league_id=$league_id AND event_id=$event_id AND wc_id=? AND approved=0;";
						$stmt = $this->db_connection->prepare($query);
						$stmt ->bind_param("ii",$p,$wid);
		
						$this->db_connection->query("START TRANSACTION");
		
						foreach ($tochange as $key => $item) {				
							$p 		= $item;
							$wid	 		= $key;
							$stmt->execute();
						}
			
						$stmt->close();
						$this->db_connection->query("COMMIT");
				
						//---------DO UPDATE
					
				
					}//must change order
					
				}
				
			}
			
		}
		
		public function changeRequestOrder(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
      	
				$data1  = $this->db_connection->real_escape_string($_POST['switchRequest'][0]);
				$data2  = $this->db_connection->real_escape_string($_POST['switchRequest'][1]);
				
				$data1_exp = explode(".", $data1);
				$data2_exp = explode(".", $data2);
				
				$user_id = $_SESSION['user_id'];
				$req = $data1_exp[0];
				$event_id = $data1_exp[1];
				$league_id = $data1_exp[2];
				$move1_pos = $data1_exp[3];
				$move1_id = $data1_exp[4];
				$move2_pos = $data2_exp[3];
				$move2_id = $data2_exp[4];
				
				//switchreq
				if($req=="switchreq"){
				
					//move 1
	      	$sql = "UPDATE wildcard_requests SET priority=$move2_pos 
									WHERE user_id=$user_id AND league_id=$league_id AND event_id=$event_id AND wc_id=$move1_id";
					$this->db_connection->query($sql);
		
					//move 2
	      	$sql = "UPDATE wildcard_requests SET priority=$move1_pos 
									WHERE user_id=$user_id AND league_id=$league_id AND event_id=$event_id AND wc_id=$move2_id";
					$this->db_connection->query($sql);
					
				}
				
			}
			
		}
		
		public function changeLineupOrder(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
      	
				$data1  = $this->db_connection->real_escape_string($_POST['switchLineup'][0]);
				$data2  = $this->db_connection->real_escape_string($_POST['switchLineup'][1]);
				
				$data1_exp = explode(".", $data1);
				$data2_exp = explode(".", $data2);
				
				$user_id = $_SESSION['user_id'];
				$req = $data1_exp[0];
				$event_id = $data1_exp[1];
				$move1_pos = $data1_exp[2];
				$move1_id = $data1_exp[3];
				$move2_pos = $data2_exp[2];
				$move2_id = $data2_exp[3];
				
				if($req=="lineup"){
					
					$wildcards = $this->getAvailableWildcards($event_id,0);
					
					$proceed = "y";
					
					if(isset($move1_id) && isset($move1_pos) && isset($move2_id) && isset($move2_pos) && ($move1_id!=$move2_id)){
						
						if(!empty($wildcards[$move1_id]) || !empty($wildcards[$move2_id])){
							if($move1_pos>7 || $move2_pos>7){
								$proceed = "n";//one of the moves involves a wildcard being benched. That can't be done.
								$this->errors[] = "You can't bench a wildcard.";//error output
							}
						}
					
						if($proceed=="y"){
						
							//move active to bench
			      	$sql = "UPDATE league_picks SET active=$move2_pos 
											WHERE user_id=$user_id AND event=$event_id AND pick_id=$move1_id";
							$this->db_connection->query($sql);
				
							//move bench to active
			      	$sql = "UPDATE league_picks SET active=$move1_pos
											WHERE user_id=$user_id AND event=$event_id AND pick_id=$move2_id";
							$this->db_connection->query($sql);
						
						}//can proceed with switch
					
					}//check that all moves are set
					
				}
				
			}
			
		}
		
		public function viewRequestReport($league,$event){
			
			if(!empty($league['id'])){$league_id = $league['id'];}else{$league_id = $league;}
			if(!empty($event['current'])){$event_id = $event['current'];}else{$event_id = $event;}
		
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
				
				$user_id = $_SESSION['user_id'];
					
					$requests = $this->getAllWildcardRequests($league_id,$event_id);
			
					$to_return .= "<div id='myNav' class='overlay'>";
					$to_return .= "<a href='javascript:void(0)' class='closebtn' onclick='closeNav()'>&times;</a>";
					$to_return .= "<div class='overlay-content'>";
					
					foreach($requests as $t=>$v1){
						
						if($v1['approved']==1){
								
							if($lastuid==$v1['uid'] && $lastapproved=="n"){
								
								$to_return .= "<div class='reportrow secondchance approved row is-collapse-child align-middle'>
																<div class='small-1 columns'>".($t+1)."</div>
																<div class='small-4 columns'>" .$v1['user_name'] ."</div>
																<div class='small-4 columns'>".$v1['wc_name'] ."</div>
																<div class='small-2 columns'> Approved </div>
															</div>";
								
								$lastuid = $v1['uid'];$lastapproved = "y";
							}
							else{
								
								$to_return .= "<div class='reportrow approved row is-collapse-child align-middle'>
																<div class='small-1 columns'>".($t+1)."</div>
																<div class='small-4 columns'>" .$v1['user_name'] ."</div>
																<div class='small-4 columns'>".$v1['wc_name'] ."</div>
																<div class='small-2 columns'> Approved </div>
															</div>";
								
								$lastuid = $v1['uid'];$lastapproved = "y";
							}
							
						}
				
						elseif($v1['approved']==2){
							
							if($lastuid==$v1['uid'] && $lastapproved=="n"){
								$to_return .= "<div class='reportrow secondchance denied row is-collapse-child align-middle'>
																 <div class='small-1 columns'>".($t+1)."</div>
																 <div class='small-4 columns'>" .$v1['user_name'] ."</div>
		 														<div class='small-4 columns'>".$v1['wc_name'] ."</div>
																 <div class='small-2 columns'> Denied </div>
															 </div>";
							
								$lastuid = $v1['uid'];$lastapproved = "n";
							}
							else{
								$to_return .= "<div class='reportrow denied row is-collapse-child align-middle'>
																 <div class='small-1 columns'>".($t+1)."</div>
																 <div class='small-4 columns'>" .$v1['user_name'] ."</div>
		 														<div class='small-4 columns'>".$v1['wc_name'] ."</div>
																 <div class='small-2 columns'> Denied </div>
															 </div>";
							
								$lastuid = $v1['uid'];$lastapproved = "n";
							}
						}
				
					}
					
					$to_return .= "</div></div>";
				
					return $to_return;
				
			}
			
		}
		
		
}//class Lineup
