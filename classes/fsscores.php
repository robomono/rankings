<?php

class FSScores{

    private $db_connection = null;
    public $errors = array();
    public $messages = array();

    public function __construct(){
       
			 	include_once("fsbasics.php");	
				require_once("../config/db.php");
				session_start();
				
        
				
    }
		
		public function getTotalLeaguePicks($league_id){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {$this->errors[] = $this->db_connection->error;}
		
			if (!$this->db_connection->connect_errno) {
				
				$sql = "SELECT l.user_id,l.event,l.pick_id,l.status,l.active,l.wc,s.position,s.points
									FROM league_picks l 
									LEFT JOIN surfer_scores s
									ON l.pick_id = s.surfer_id AND l.event = s.event
									WHERE l.league_id=$league_id AND l.event>0 AND l.active<8
									ORDER BY l.user_id,l.event,l.active";
								
				$result = $this->db_connection->query($sql);
				while($row = mysqli_fetch_array($result)){
					$allpicks[$row['user_id']][$row['event']][$row['active']]['sid'] = $row['pick_id'];
					$allpicks[$row['user_id']][$row['event']][$row['active']]['pos'] = $row['position'];
					$allpicks[$row['user_id']][$row['event']][$row['active']]['sco'] = $row['points'];
					
					$pickstatus[$row['pick_id']][$row['event']]['status'] = $row['status'];
					$pickstatus[$row['pick_id']][$row['event']]['wc'] = $row['wc'];
					
				}
				
				$toreturn['allpicks'] = $allpicks;
				$toreturn['pickstatus'] = $pickstatus;
				
				return $toreturn;
				
			}//connection errors
			
		}
		
		public function showLeaderboardTable($user_id,$league,$leaguepicks,$pickstatus){
			
			$league_id = $league['id'];
			
			$fsbasics = new FSBasics();
			
			$users = $fsbasics->getLeagueUsers($league_id);
			
			$surfers = $fsbasics->getSurfers();
			
			//calculate total for each player, and scores per event
			foreach($leaguepicks as $uid=>$v1){
								
				foreach($v1 as $eid=>$v2){
					
					foreach($v2 as $pos=>$v3){
						$sid = $v3['sid'];$res = $v3['pos'];$sco = $v3['sco'];
						//echo "$uid - $eid - $pos - $sid - $sco</br>";
						$usertotal[$uid]+= $sco;
						$eventtotal[$uid][$eid]+=$sco;
					}
					
				}
				
			}
			
			arsort($usertotal);
			$table.= "<div class='row align-center'><div class='large-10 small-12 columns'>";
			$table.= "<table class='leagueleaderboard'>";
			$table.= "<thead>
							<caption>".$league['name']."</caption>
							<tr class='leaderheader'>
							<th></th>
							<th>Team</th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Quiksilver Pro Gold Coast'>1</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Drug Aware Margaret River Pro'>2</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Rip Curl Pro Bells Beach'>3</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Rio Pro'>4</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Fiji Pro'>5</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Corona J-Bay Open'>6</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Billabong Pro Tahiti'>7</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Hurley Pro at Trestles'>8</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Quiksilver Pro France'>9</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='MEO Rip Curl Pro Portugal'>10</span></th>
							<th><span data-tooltip aria-haspopup='true' class='has-tip' title='Billabong Pipe Masters'>11</span></th>
							<th>Total</th>
						</tr></thead>";
			
			$i=1;
			foreach($usertotal as $uid=>$totalscore){
				
				$table.= "<tr>
							<td class='boardranking'>$i</td>
								
							<td class='boarduser'>
								<div class='boarduserteam'>".$users[$uid]['team']."</div>
								<div class='boardusername'>".$users[$uid]['name']."</div>
							</td>";
				
				//show total score per event
				for($e=1;$e<=11;$e++){
					if(!empty($eventtotal[$uid][$e])){
						$table.= "<td class='boardeventscore'>
										<a href='#' class='shorteventscore' id='showu".$uid."e".$e."'>
											".number_format($eventtotal[$uid][$e], 0,'',',')."
										</a>
									</td>";			
									
					}else{
						$table.= "<td class='boardeventscore'>-</td>";
					}
				}
				
				//show grand total points
				$table.= "<td class='boardtotalscore'>".number_format($totalscore, 0,'',',')."</td></tr>";
				
				//create rows for each one of the scored events
				for($e=1;$e<=11;$e++){
					if(!empty($eventtotal[$uid][$e])){
						
						//sort the array by score
						$rowscore = array();
						foreach($leaguepicks[$uid][$e] as $key=>$value){
							$rowscore[]  = $value['sco'];
						}
						array_multisort($rowscore, SORT_DESC, $leaguepicks[$uid][$e]);
						
						foreach($leaguepicks[$uid][$e] as $k1=>$v1){
							
							$thissurfer = $surfers[$v1['sid']]['name'];
							$thisscore = $v1['sco'];
							$thisresult = $v1['pos'];
							
							$table.= "<tr class='pointbreakdown foru".$uid."e".$e."'>
											<td colspan='".($e+1)."'>$thissurfer</td>
											<td>$thisscore</td>
											<td>$thisresult</td>
										</tr>";
						}//show users league picks and scores
						
					}
				}
				
				$i++;
				
			}
			
			$table.= "</table></div></div>";
			
			return $table;
			
		}
		
		public function getLeagueLeaderboard($user_id,$league){
			
			$league_id = $league['id'];
			
			$allpicks = $this->getTotalLeaguePicks($league_id);
			
			$leaguepicks = $allpicks['allpicks'];
			$pickstatus = $allpicks['pickstatus'];
			
			$display = $this->showLeaderboardTable($user_id,$league,$leaguepicks,$pickstatus);
			
			return $display;
			
		}
		
		
		
}//Class Scores
?>