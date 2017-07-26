<?php

class FSDraft{

    public function __construct(){

        session_start();
				include_once("fsbasics.php");
				require_once("../config/db.php");
				
    }
		
		private function getDraftOrder($league_id){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {$this->errors[] = $this->db_connection->error;}

			if (!$this->db_connection->connect_errno) {
			
				$sql = "SELECT user_id, draft_order
								FROM league_control
								WHERE league_id = $league_id;";
				
	      $result = $this->db_connection->query($sql);

				while($row = mysqli_fetch_array($result)){
					$pickorder[$row['draft_order']] = $row['user_id'];
				}
				
				return $pickorder;
				
			}//connection errors
			
		}//get draft order
		
		public function startDraft(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
      	
				$data   = $this->db_connection->real_escape_string($_POST['startDraft']);
				
				$data_exp = explode(".", $data);
				
				$user_id = $_SESSION['user_id'];
				$req = $data_exp[0];
				$league_id = $data_exp[1];
				
				if($req=="start"){
					
					//get pick order
					$pickorder = $this->getDraftOrder($league_id);
					
					//-------create final order array
					ksort($pickorder);
					$order1 = $pickorder;
					$order2 = $pickorder;
					$order2 = array_reverse($order2);
					
					$result = array_merge($order1, $order2);//merge normal and reversed orders
					$final_order = array_merge($result, $result, $result, $result, $result);//do it 5 times to get 10 picks each
					//-------end create final order array
					
					//-----INSERT ARRAY INTO DRAFT PICKS
					$query = "INSERT INTO draft_picks (league_id,turn,user_id) VALUES (?,?,?)";
					$stmt = $this->db_connection->prepare($query);
					$stmt ->bind_param("iii", $leagueid,$turn,$userid);

					$this->db_connection->query("START TRANSACTION");
				
					foreach ($final_order as $key => $item) {
					
							$leagueid 	= $league_id;
							$turn				= $key+1;//+1 so that first pick turn isnt 0
							$userid 		= $item;
							$stmt->execute();
					
					}
				
					$stmt->close();
					$this->db_connection->query("COMMIT");
					
	      	$sql = "UPDATE leagues SET status=2 WHERE league_id=$league_id";
					$this->db_connection->query($sql);
					//-----END INSERT ARRAY INTO DRAFT PICKS
					
					return "success";
					
				}
				
			}
			
		}
		
		private function getDraftPicks($league_id){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
			
			if (!$this->db_connection->set_charset("utf8")) {$this->errors[] = $this->db_connection->error;}

			if (!$this->db_connection->connect_errno) {
			
			$sql = "SELECT pick_id,user_id,turn FROM draft_picks WHERE league_id=$league_id ORDER BY turn;";
			
			$result = $this->db_connection->query($sql);
			
				while($row = mysqli_fetch_array($result)){
					
					$picksbyuser[$row['user_id']][] = $row['pick_id'];
					$usersbypick[$row['pick_id']][] = $row['user_id'];
					$usersbyturn[$row['turn']]['uid'] = $row['user_id'];
					$usersbyturn[$row['turn']]['pid'] = $row['pick_id'];
					
				}
				
				$draftpicks['picksbyuser'] = $picksbyuser;
				$draftpicks['usersbypick'] = $usersbypick;
				$draftpicks['usersbyturn'] = $usersbyturn;
			
				return $draftpicks;
			
			}//connection errors
			
		}
		
		private function getAvailables($user_id,$users,$league_id,$draftpicks){
			
			$fsbasics = new FSBasics();
			
			$leaguesize = count($users);
			
			//CALCULATE NUMBER OF AVAILABLE SURFERS
			
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
			//END CALCULATE AVAILABLE SURFERS

			$surfers = $fsbasics->getSurfers();
			
			foreach($surfers as $sid=>$v){
				if($sid<=1005){
					$surfers[$sid]['available'] = $top5;
				}elseif($sid>1015){
					$surfers[$sid]['available'] = $lowerhalf;
				}elseif($sid<=1015 && $sid>1005){
					$surfers[$sid]['available'] = $next10;
				}
			}
			
			//assign number of available picks per surfer according to ranking
//			foreach($surfers as $sid=>$v){
//				if($sid>1015){
//					$surfers[$sid]['available'] = $availables_max;
//				}else{
//					$surfers[$sid]['available'] = $availables_min;
//				}
//			}
			//top 15 have one less available than bottom 19
			
			$picksbyuser = $draftpicks['picksbyuser'];
			$usersbypick = $draftpicks['usersbypick'];
			
			foreach($picksbyuser as $uid=>$v){
				foreach($v as $k2=>$pid){
					if($uid == $user_id){
						$team[] = $pid;
						$surfers[$pid]['available'] = 0;
					}
				}
			}
			
			foreach($usersbypick as $pid=>$v){
				foreach($v as $k2=>$uid){
					if($surfers[$pid]['available'] > 0){
						$surfers[$pid]['available']--;
					}
				}
			}
			
			$toreturn['surfers'] = $surfers;
			$toreturn['team'] = $team;
			
			return $toreturn;
			
		}
		
		private function getTurnOrder($draftpicks){
			
			$usersbyturn = $draftpicks['usersbyturn'];
			
			//get next turn;
			
			foreach($usersbyturn as $t=>$v){
				$uid = $v['uid'];
				$pid = $v['pid'];
				
				if($pid==0){
					$nextturn['turn'] = $t;
					$nextturn['uid'] = $uid;
					break;
				}
				
			}
			
			//get entire turn list
			foreach($usersbyturn as $t=>$v){
				$uid = $v['uid'];
				$pid = $v['pid'];
				
				if($pid==0){
					$pickorder[$t]['uid'] = $uid;
				}elseif($pid>0){
					$pickorder[$t]['uid'] = $uid;
					$pickorder[$t]['pid'] = $pid;
				}
				
			}
			
			$order['nexturn'] = $nextturn;
			$order['pickorder'] = $pickorder;
			
			return $order;

		}//get next user to pick and turn number
		
		private function arrangeUsersTeam($surfers,$team){
			
			$user_name = $_SESSION['user_name'];
			$user_team = $_SESSION['user_team'];
			
			$usersteam .="<div class='row is-collapse-child align-middle align-center'>";
			$usersteam .="<div class='large-10 medium-expand small-10 columns draftteamtitle'>";
			if(empty($user_team)){$usersteam.=$user_name;}else{$usersteam.=$user_team;}
			$usersteam .="</div></div>";
			
			$usersteam .="<div class='draftteamteamrow align-center row is-collapse-child'>";
			
			foreach($team as $k=>$v){
				
				if($v>0){
					
					$usersteam .="<div class='large-1 medium-2 columns draftpick'>";
					
					$usersteam .="<div class='draftimgcontainer hide-for-small-only'>
													<div class='draftteamimg' style='
													background: url(img/".$surfers[$v]['img'].");
													background-size: 57px 47px;
													background-repeat: no-repeat;
													background-position: center;'> </div>
											</div>";
					
					$usersteam .="<div class='draftpickname'>".$surfers[$v]['aka'] ."</div>";
					
					$usersteam .="</div>";
					
					
				}
				elseif($v==0){
				
					$usersteam .="<div class='large-1 medium-2 columns draftempty'>";
					$usersteam .="<div class='draftimgcontainer hide-for-small-only'>
													<div class='draftteamimg' style='
													background-color: white;
													background-size: 57px 47px;
													background-repeat: no-repeat;
													background-position: center;'> </div>
											</div>";					
					$usersteam .="</div>";
					
				}
				
			}
			
			$usersteam .="</div>";
			
			return $usersteam;
			
		}

		private function arrangeAvailables($user_id,$league_id,$surfers,$order){
			
			$nextuser = $order['nexturn']['uid'];//get the next turn
			$nextturn = $order['nexturn']['turn'];//get the next turn
			$totalpicks =  sizeof($order['pickorder']);
			
			foreach($surfers as $sid=>$v){
				
				if($surfers[$sid]['available'] == 0 && $sid>0){
					//surfer is not available
					$unavailable .="<div class='draftsurferspicked draftsurfer row is-collapse-child align-middle'>";
				
					$unavailable .="<div class='draftsurfersname large-5 medium-5 small-8 columns'>".$surfers[$sid]['name'] ."</div>";
					
					$unavailable .="<div class='draftsurfercount large-2 medium-2 small-2 columns'>".$surfers[$sid]['available']."</div>";
					
					$unavailable .="</div>";
					
				}
				elseif($surfers[$sid]['available'] > 0 && $sid>0){
					//surfer is available
					
					$available .="<div class='draftsurfersavail draftsurfer row is-collapse-child align-middle'>";
					
					$available .="<div class='draftsurfersname large-4 medium-5 small-5 columns'>".$surfers[$sid]['name'] ."</div>";
					
					$available .="<div class='draftsurfercount large-2 medium-2 small-2 columns'>
												<span class='remainingtag'>Available</span></br>".$surfers[$sid]['available']."</div>";
					
					if($user_id == $nextuser){
						$available .="<div class='draftsurferbtnarea large-3 medium-3 small-3 columns'>
														<button class='button thinkpick' id='pick.$sid.$league_id.$nextturn.$totalpicks'>
															Pick ".$surfers[$sid]['aka']."
														</button>
														
													</div>";
					}
//<button class='button makepick' 
//																		id='pick.$sid.$league_id.$nextturn.$totalpicks'>
//																						Confirm ".$surfers[$sid]['aka']."
//														</button>					
					
					$available .="</div>";
				}
				
			}
			
			$draftsurfers['available'] = $available;
			$draftsurfers['unavailable'] = $unavailable;
			
			return $draftsurfers;
			
		}
		
		private function arrangeDraftQueue($user_id,$users,$order,$surfers){
			
			$nextturn = $order['nexturn'];
			$pickorder = $order['pickorder'];
			
			foreach($pickorder as $t=>$v){
				
				$pid = $v['pid'];
				$uid = $v['uid'];
				
				//if user has no name then user name is shorthand email
				if(empty($users[$uid]['name'])){$ename = explode("@", $users[$uid]['email']);$users[$uid]['name'] = $ename[0];}
				
				//arrange the display
				if($pid>0){
					
					$orderlist.= "<div class='draftorderrow orderhaspicked row is-collapse-child align-middle'>
													<div class='draftorderturn small-2 columns'>$t</div>
													<div class='columns'>
													<div class='draftorderuser'>" .$users[$uid]['name']."</div>
													<div class='draftorderpicked'>" .$surfers[$pid]['name']. "</div>
												</div></div>";
				
				}
				elseif($t == $nextturn['turn'] && $uid == $nextturn['uid']){
					
					$orderlist.= "<div class='draftorderrow orderisnext row is-collapse-child align-middle'>";
					if($uid==$user_id){
						$orderlist.= "<div class='draftorderusersturn columns'>It's your turn!</div>";
					}else{
						$orderlist.= "<div class='columns'>
														<div class='draftorderpicked'>Picking now: </div>
														<div class='draftorderuser'>".$users[$uid]['name']."</div>
													</div>";
					}
					$orderlist.= "</div>";
				}
				elseif($pid==0 && $t!=$nextturn['turn']){
					$orderlist.= "<div class='draftorderrow orderhasntpicked row is-collapse-child align-middle'>
													<div class='draftorderturn small-2 columns'>$t</div>
													<div class='draftorderidleuser columns'>" .strtolower($users[$uid]['name'])."</div>
												</div>";
				}
				
			}
			
			return $orderlist;
			
		}
		
		private function consolidateDraftDisplay($user_id,$usersteam,$order,$draftsurfers,$disp_nextorder){
			
			$nextuser = $order['nexturn']['uid'];//get the next turn
			
			$display.= "<div class='draftcontainer'>";
			
			//DISPLAY USERS TEAM
			$display.= "<div class='row align-center align-middle draftteamcontainer'>
										<div class='large-10 medium-12 small-12 columns'>
											$usersteam
									</div></div>";
			//DISPLAY USERS TEAM
			
			//IF ITS USERS TEAM, DISPLAY PICK NOTIFICATION
			if($nextuser == $user_id){
				$display.= "<div class='row align-center align-middle draftturnnotification'>
											<div class='large-10 medium-12 small-12 columns'>
											It's your turn to pick
										</div></div>";
			}
			//DISPLAY PICK NOTIFICATION
			
			//DISPLAY MAIN DRAFT SECTION
			$display.= "<div class='row align-spaced'>";
			//pick order
			$display.= "<div class='large-3 medium-2 small-12 small-order-2 columns draftordercontainer'>
										$disp_nextorder
									</div>";
			//pick order
			//draft surfers
			$display.= "<div class='large-8 medium-9 small-12 small-order-1 columns draftsurferscontainer' id='alldraftsurfers'>
											<input class='search' placeholder='Find a surfer' /> <button class='button sort' data-sort='name'>Find</button>
										".$draftsurfers['available']."
										".$draftsurfers['unavailable']."
										
									</div>";
			
			//echo $draftsurfers;
			$display.= "</div>";
			//DISPLAY MAIN DRAFT SECTION
			
			
			$display.= "</div>";
			
			return $display;
			
		}
		
		public function showDraft(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
      	
				$data   = $this->db_connection->real_escape_string($_POST['showDraft']);
				
				$data_exp = explode("#lid", $data);
				
				$league_id = $data_exp[1];
				$user_id = $_SESSION['user_id'];
				
				if(!empty($league_id) && !empty($user_id)){
					
					$fsbasics = new FSBasics();
					
					$league = $fsbasics->getUsersLeague($user_id);
					
					if($league['status'] == 2){
							
							$users = $fsbasics ->getLeagueUsers($league);
							
							$draftpicks = $this->getDraftPicks($league_id);
							
							$availables = $this->getAvailables($user_id,$users,$league_id,$draftpicks);
							
							$order = $this->getTurnOrder($draftpicks);
							
							$surfers = $availables['surfers'];
							$team = $availables['team'];
							
							//ARRANGE INFO TO DISPLAY
							$usersteam = $this->arrangeUsersTeam($surfers,$team);	
							$draftsurfers = $this->arrangeAvailables($user_id,$league_id,$surfers,$order);
							$orderchart = $this->arrangeDraftQueue($user_id,$users,$order,$surfers);
							
							//consolidate display
							$display = $this->consolidateDraftDisplay($user_id,$usersteam,$order,$draftsurfers,$orderchart);
							
							return $display;
							
							
					}
					elseif($league['status'] != 2){
						return "closewindow";
					}
					
				}
				
				
			}
			
		}
		
		public function makePick(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
				
				$data = $this->db_connection->real_escape_string($_POST['makePick']);
				
				$data_exp = explode(".", $data);
				
				$req 			 = $data_exp[0];				
				$pick_id   = $data_exp[1];	
				$league_id = $data_exp[2];
				$turn 		 = $data_exp[3];
				$lastturn  = $data_exp[4];
				$user_id   = $_SESSION['user_id'];
				
        $sql = "INSERT INTO league_picks (user_id,league_id,event,pick_id,active) 
								VALUES ($user_id,$league_id,0,$pick_id,1);";
				$this->db_connection->query($sql);
				
      	$sql = "UPDATE draft_picks SET pick_id=$pick_id WHERE league_id=$league_id AND user_id=$user_id AND turn=$turn";
				$this->db_connection->query($sql);
				
				if($turn==$lastturn){
					//this is the last pick of the draft
	      	$sql = "UPDATE leagues SET status=3 WHERE league_id=$league_id";
					$this->db_connection->query($sql);
					return "closewindow";
				}
				elseif($turn!=$lastturn){
					return "success";
				}
				
				
				
			}//connection errors
			
		}
		
}//class League
