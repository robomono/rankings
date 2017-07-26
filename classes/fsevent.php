<?php

class FSEvent{

    private $db_connection = null;
    public $errors = array();
    public $messages = array();

    public function __construct(){
       
			 	include_once("fsbasics.php");	
				require_once("../config/db.php");
				session_start();		
				
				
    }
		
		public function getAllEventRounds($event_id){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }
		
			if (!$this->db_connection->connect_errno) {
				
				//---GET ROUND
				$sql = "SELECT round,heat,player,surfer_id,result
								FROM heats
								WHERE event_id=$event_id ORDER BY round,heat,result";
	      
				$result = $this->db_connection->query($sql);
				while($row = mysqli_fetch_array($result)){
					$event[$row['round']][$row['heat']][$row['player']]['sid'] = $row['surfer_id'];
					$event[$row['round']][$row['heat']][$row['player']]['sco'] = $row['result'];
				}
				//---END GET ROUND
				
				return $event;
				
			}//connection errors
			
		}//gets all rounds, heats and surfers from event ****ATTN JOINS WITH OLD_SURFERS DATABASE!!!!!!*********
		
		public function getCurrentRound($heats){
			
			foreach($heats as $round=>$v1){
				foreach($v1 as $heat=>$v2){
					foreach($v2 as $player=>$v3){
						$sid = $v3['sid'];$sco = $v3['sco'];
						if($sco==0){$currentround = $round;$currentheat=$heat;break 3;}else{$lastround = $round;}//this is the current round
					}
				}
			}
			
			if(empty($currentround) && $lastround==8){
				//the finals are over but the event hasnt ended
				$currentround=8;
			}
			
			$current['round'] = $currentround;
			$current['heat'] = $currentheat;
			
			return $current;
			
		}//gets all event heats and finds out what round we're on
		
		public function getPastRound($heats){
			
			foreach($heats as $round=>$v1){
				foreach($v1 as $heat=>$v2){
					foreach($v2 as $player=>$v3){
						$sid = $v3['sid'];$sco = $v3['sco'];
						if($sco==0){$currentround = $round;$currentheat=$heat;break 3;}else{$lastround = $round;}//this is the current round
					}
				}
			}
		
			
			return $lastround;
			
		}
		
		public function getAllLeagueLineups($event_id,$league_id){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }
		
			if (!$this->db_connection->connect_errno) {
				
				$sql = "SELECT p.user_id,p.pick_id,p.active,u.user_name,u.user_team
								FROM league_picks p 
								LEFT JOIN users AS u
								ON p.user_id = u.id
								WHERE p.event=$event_id AND p.league_id=$league_id ORDER BY p.user_id,p.active";
				$result = $this->db_connection->query($sql);
				
				while($row = mysqli_fetch_array($result)){
						
					$picksbyuser[$row['user_id']][$row['active']] = $row['pick_id'];
					
					$picksbysurfer[$row['pick_id']][$row['user_id']] = $row['active'];
					
					$users[$row['user_id']]['name'] = $row['user_name'];
					$users[$row['user_id']]['short'] = explode(" ",$row['user_name'])[0];
					$users[$row['user_id']]['team'] = $row['user_team'];
						
				}
				
			}//no connection errors
			
			$allpicks['users'] = $users;
			$allpicks['byuser'] = $picksbyuser;
			$allpicks['bysurfer'] = $picksbysurfer;
			
			return $allpicks;
			
		}//gets event picks for all users in this event
		
		private function getPointsPerRound($round){
			
			//FIRST CALCULATE SCORES
						if($round==2){
							//25th place - 500 points
							$points[2] = 500;
						}elseif($round==3){
							//13th place - 1750 points
							$points[2] = 1750;
						}elseif($round==5){
							//9th place - 4000 points
							$points[2] = 4000;
						}elseif($round==6){
							//QF - 5th place - 5200 points
							$points[2] = 5200;
						}elseif($round==7){
							//SF - 3rd place - 6500 points
							$points[2] = 6500;
						}elseif($round==8){
							//F - 1st place - 10000 points - 2nd place - 8000 points
							$points[2] = 8000;
							$points[1] = 10000;
						}
			
						return $points;
			
		}
		
		private function potentialPointsTable(){
			//ROUND 8 - FINALS
				$max[8][2] = 8000 + 10000;
				$max[8][1] = 10000;
				
				$min[8][2] = 8000 +10000;
				$min[8][1] = 8000;
			
				//ROUND 7 - SEMIS - 2 stay behind, 2 advance
				$max[7][4] = 6500 + 6500 + 8000 +10000;
				$max[7][3] = 6500 + 8000 +10000;
				$max[7][2] = 8000 + 10000;
				$max[7][1] = 10000;
				
				$min[7][4] = 6500 + 6500 + 8000 + 10000;
				$min[7][3] = 6500 + 6500 + 8000;
				$min[7][2] = 6500 + 6500;
				$min[7][1] = 6500;

				//ROUND 6 - QF
				$max[6][8] = 5200 + 5200 + 5200 + 5200 + 6500 + 6500 + 8000 + 10000;
				$max[6][7] = 5200 + 5200 + 5200 + 6500 + 6500 + 8000 + 10000;
				$max[6][6] = 5200 + 5200 + 6500 + 6500 + 8000 + 10000;
				$max[6][5] = 5200 + 6500 + 6500 + 8000 + 10000;
				$max[6][4] = 6500 + 6500 + 8000 + 10000;
				$max[6][3] = 6500 + 8000 + 10000;
				$max[6][2] = 8000 +10000;
				$max[6][1] = 10000;
				
				$min[6][8] = 5200 + 5200 + 5200 + 5200 + 6500 + 6500 + 8000 + 10000;
				$min[6][7] = 5200 + 5200 + 5200 + 5200 + 6500 + 6500 + 8000;
				$min[6][6] = 5200 + 5200 + 5200 + 5200 + 6500 + 6500;
				$min[6][5] = 5200 + 5200 + 5200 + 5200 + 6500;
				$min[6][4] = 5200 + 5200 + 5200 + 5200;
				$min[6][3] = 5200 + 5200 + 5200;
				$min[6][2] = 5200 + 5200;
				$min[6][1] = 5200;
			
			
				//RouND 5
				$max[5][8] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[5][7] = 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[5][6] = 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[5][5] = 4000 + 6500 + 6500 + 8000 + 10000;
				$max[5][4] = 6500 + 6500 + 8000 + 10000;
				$max[5][3] = 6500 + 8000 + 10000;
				$max[5][2] = 8000 + 10000;
				$max[5][1] = 10000;
				
				$min[5][1] = 4000;
				$min[5][2] = 4000 + 4000;
				$min[5][3] = 4000 + 4000 + 4000;
				$min[5][4] = 4000 + 4000 + 4000 + 4000;
				$min[5][5] = 4000 + 4000 + 4000 + 4000 + 6500;
				$min[5][6] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500;
				$min[5][7] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500 + 6500;
				$min[5][8] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500 + 6500 + 6500;
			
				//ROUND 4 - NON ELIMINATION
				$max[4][8] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[4][7] = 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[4][6] = 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[4][5] = 4000 + 6500 + 6500 + 8000 + 10000;
				$max[4][4] = 6500 + 6500 + 8000 + 10000;
				$max[4][3] = 6500 + 8000 + 10000;
				$max[4][2] = 8000 + 10000;
				$max[4][1] = 10000;
				
				$min[4][1] = 4000;
				$min[4][2] = 4000 + 4000;
				$min[4][3] = 4000 + 4000 + 4000;
				$min[4][4] = 4000 + 4000 + 4000 + 4000;
				$min[4][5] = 4000 + 4000 + 4000 + 4000 + 6500;
				$min[4][6] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500;
				$min[4][7] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500 + 6500;
				$min[4][8] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500 + 6500 + 6500;
			
				//ROUND 3
				$max[3][8] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[3][7] = 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[3][6] = 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[3][5] = 4000 + 6500 + 6500 + 8000 + 10000;
				$max[3][4] = 6500 + 6500 + 8000 + 10000;
				$max[3][3] = 6500 + 8000 + 10000;
				$max[3][2] = 8000 + 10000;
				$max[3][1] = 10000;
				
				$min[3][1] = 1750;
				$min[3][2] = 1750 + 1750;
				$min[3][3] = 1750 + 1750 + 1750;
				$min[3][4] = 1750 + 1750 + 1750 + 1750;
				$min[3][5] = 1750 + 1750 + 1750 + 1750 + 1750;
				$min[3][6] = 1750 + 1750 + 1750 + 1750 + 1750 + 1750;
				$min[3][7] = 1750 + 1750 + 1750 + 1750 + 1750 + 1750 + 1750;
				$min[3][8] = 1750 + 1750 + 1750 + 1750 + 1750 + 1750 + 1750 + 1750;
			
				//ROUND 2
				$max[2][8] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[2][7] = 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[2][6] = 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[2][5] = 4000 + 6500 + 6500 + 8000 + 10000;
				$max[2][4] = 6500 + 6500 + 8000 + 10000;
				$max[2][3] = 6500 + 8000 + 10000;
				$max[2][2] = 8000 + 10000;
				$max[2][1] = 10000;
				
				$min[2][1] = 500;
				$min[2][2] = 500 + 500;
				$min[2][3] = 500 + 500 + 500;
				$min[2][4] = 500 + 500 + 500 + 500;
				$min[2][5] = 500 + 500 + 500 + 500 + 500;
				$min[2][6] = 500 + 500 + 500 + 500 + 500 + 500;
				$min[2][7] = 500 + 500 + 500 + 500 + 500 + 500 + 500;
				$min[2][8] = 500 + 500 + 500 + 500 + 500 + 500 + 500 + 500;
			
			
			//ROUND 1
				$max[1][8] = 4000 + 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[1][7] = 4000 + 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[1][6] = 4000 + 4000 + 6500 + 6500 + 8000 + 10000;
				$max[1][5] = 4000 + 6500 + 6500 + 8000 + 10000;
				$max[1][4] = 6500 + 6500 + 8000 + 10000;
				$max[1][3] = 6500 + 8000 + 10000;
				$max[1][2] = 8000 + 10000;
				$max[1][1] = 10000;
				
				$min[1][1] = 500;
				$min[1][2] = 500 + 500;
				$min[1][3] = 500 + 500 + 500;
				$min[1][4] = 500 + 500 + 500 + 500;
				$min[1][5] = 500 + 500 + 500 + 500 + 500;
				$min[1][6] = 500 + 500 + 500 + 500 + 500 + 500;
				$min[1][7] = 500 + 500 + 500 + 500 + 500 + 500 + 500;
				$min[1][8] = 500 + 500 + 500 + 500 + 500 + 500 + 500 + 500;
				
				$table['max'] = $max;
				$table['min'] = $min;
				
				return $table;
				
		}
		
		private function getPotentialPoints($heats,$current,$picksinheat,$userscores,$usercount){
			
			$currentround = $current['round'];
			$nextround = $currentround+1;
			$nextnextround = $currentround+2;
			$currentheat = $current['heat'];
			
			$thisroundclashes = 0;
			$best_case_wins = 0;
			$best_case_loss = 0;
			$worst_case_wins = 0;
			$worst_case_loss = 0;
			
			$pointstable = $this->potentialPointsTable();
			
			$max = $pointstable['max'];
			$min = $pointstable['min'];
			
//			echo "</br>";
			//find out if user has colliding surfers in this round
			if(!empty($picksinheat[$currentround])){
				foreach($picksinheat[$currentround] as $heat=>$count){
				
					if(($currentround==1 || $currentround==4) && $count==3){
						$this_clashesof3+=1;
					}
				
					elseif(($currentround==1 || $currentround==4) && $count==2){
						$this_clashesof2+=1;
					}
				
					elseif($currentround!=1 && $currentround!=4 && $currentround!=8 && $count==2){
						$this_clashesof2+=1;
					}
				
				}
			}
			//returns $this_clashesof3 - $this_clashesof2
			
			
//			if(!empty($this_clashesof3)){echo "Clashes of 3 this round: $this_clashesof3 </br>";}
//			elseif(!empty($this_clashesof2)){echo "Clashes of 2 this round: $this_clashesof2 </br>";}
			
			//calculate spreads based on clashes
			if(!empty($this_clashesof2) || !empty($this_clashesof3)){
				
				if($currentround==1 || $currentround==4){
					
					if(!empty($this_clashesof3)){
						for($i=0;$i<$this_clashesof3;$i++){
							$usercount['act'] = $usercount['act']-3;
							$best_case_wins++;$best_case_loss = $best_case_loss+2;
							$worst_case_wins++;$worst_case_loss = $worst_case_loss+2;
						}
					}
					
					if(!empty($this_clashesof2)){
						for($i=0;$i<$this_clashesof2;$i++){
							$usercount['act'] = $usercount['act']-2;
							$best_case_wins++;$best_case_loss++;
							$worst_case_loss = $worst_case_loss+2;
						}
					}
					
				}
				elseif($currentround!=1 && $currentround!=4){
					
					if(!empty($this_clashesof2)){
						for($i=0;$i<$this_clashesof2;$i++){
							$usercount['act'] = $usercount['act']-2;
							$best_case_wins++;$best_case_loss++;
							$worst_case_wins++;$worst_case_loss++;
						}
					}
					
				}
					
			}
			
			//calculate scenarios
			if($currentround==1 || $currentround==4){
				$best_case_wins += $usercount['win'] + $usercount['act'] + $usercount['rel'];
			}elseif($currentround!=1 && $currentround!=4){
				$best_case_wins += $usercount['win'] + $usercount['act'];
			}
			
			if($currentround==1 || $currentround==4){
				$worst_case_wins += $usercount['win'];
				$worst_case_loss += $usercount['act'] + $usercount['rel'];
			}elseif($currentround!=1 && $currentround!=4){
				$worst_case_wins += $usercount['win'];
				$worst_case_loss += $usercount['act'];
			}			
			
			
			//calculate chances of surfers colliding in next heats
			//Round 5 - 8 Sufers: 4 Wins - 7 Surfers: 3 Wins 1 W/L - 6 Surfers: 2 Wins 2W/L - 5 Surfers: 1 Win 3W/L - 4 Surfers: 4W/L
			
			
			/*--------------------BEST CASE SCENARIO
			*****Won this heat: will also win next heat
			*****Still to surf: will win this heat and also next
			*****IF Non-Elimination AND already lost: will win relegation round and the next round
			*****IF Non-Elimination AND 2 surfers in heat: assumes one will win
			*/
			/*--------------------WORST CASE SCENARIO
			*****Won this heat: will lose next heat
			*****Still to surf: will lose this heat
			*****IF Non-Elimination AND already lost: Will lose relegation
			*****IF Non-Elimination AND 2 surfers in heat: assumes both will lose
			*/
			
			if($currentround==1 || $currentround==4){
				$best_points = $max[$nextnextround][$best_case_wins] + $userscores;
				$worst_points = $min[$nextround][$worst_case_loss] + $min[$nextnextround][$worst_case_wins] + $userscores;
			}elseif($currentround!=1 && $currentround!=4){
				$best_points = $max[$currentround][$best_case_wins] + $min[$currentround][$best_case_loss] + $userscores;
				$worst_points = $min[$currentround][$worst_case_loss] + $min[$nextround][$worst_case_wins] + $userscores;
			}
			
//			echo "Best Case Wins: $best_case_wins </br>";
//			echo "Best Case Loss: $best_case_loss </br>"; 
//			echo "Worst Case Wins: $worst_case_wins </br>";
//			echo "Worst Case Loss: $worst_case_loss </br>"; 
//			echo "Max Points: $best_points </br>"; 
//			echo "Min Points: $worst_points </br>";
			
		
			$toreturn['best'] = $best_points;
			$toreturn['worst'] = $worst_points;
			
			return $toreturn;
			
		}
		
		private function findActiveOwners($user_id,$sid,$picks,$users){
			
			$top.="<div class='row pickedbyusers align-center align-middle small-up-1 medium-up-4 large-up-5'>";
			
			foreach($picks[$sid] as $uid=>$pos){
				if($pos<8){
					if($uid==$user_id){
						$thisactive.= "<div class='pickedbyactive pickedbythisuser'>".$users[$uid]['short']."</div>";
					}else{
						$thisactive.= "<div class='pickedbyactive'>".$users[$uid]['short']."</div>";
					}
				}elseif($pos>=8){
					if($uid==$user_id){
						$thisbenched.= "<div class='pickedbybenched pickedbythisuser'>".$users[$uid]['short']."</div>";
					}else{
						$thisbenched.= "<div class='pickedbybenched'>".$users[$uid]['short']."</div>";
					}
				}
			}
			
			$bottom.="</div>";
			
			return $top.$thisactive.$thisbenched.$bottom;
			
		}
		
		private function getSurferImg($sid,$surfers){
			
			$heatimg = 
				
				"<div class='heatimg' style='
					background: url(img/".$surfers[$sid]['img'].");
					background-size: 63px 50px;
					background-color: rgba(255, 255, 255, 0.4);
					background-repeat: no-repeat;
					background-position: center;'>
				</div>";
					
				return $heatimg;
			
		}
		
		private function showHeatSurfer($user_id,$currentround,$v2,$surfers,$users,$picksbysurfer){
			
			$sco = $v2['sco'];$sid = $v2['sid'];
			
			if($currentround==1 || $currentround==4){
				$columns=4;
				if($sco==1){$surferresult = "heatwon";}
				if($sco==2){$surferresult = "heatrelegated";}
				if($sco==3){$surferresult = "heatrelegated";}
			}elseif($currentround!=1 && $currentround!=4){
				$columns=6;
				if($sco==1){$surferresult = "heatwon";}
				if($sco==2){$surferresult = "heatlost";}
			}
			
			$img = $this->getSurferImg($sid,$surfers);
			
			$heatsurfer.= "<div class='heatsurfer $surferresult large-$columns columns'>";
			$heatsurfer.= $img;
			$heatsurfer.= "<div class='row align-center align-middle heatsurferdetails'>";
			$heatsurfer.= "<div class='heatsurfername'>".$surfers[$sid]['name']."</div>";
			$heatsurfer.= "<div class='heatsurferaka'>".$surfers[$sid]['aka']."</div>";
			$heatsurfer.= "</div>";
			
			if(!empty($picksbysurfer[$sid])){$heatsurfer.= $this->findActiveOwners($user_id,$sid,$picksbysurfer,$users);}
			
			$heatsurfer.= "</div>";
			
			return $heatsurfer;
			
		}
		
		private function getLiveHeat($currentround,$currentheat){
			
			$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }
		
			if (!$this->db_connection->connect_errno) {
				
				//---GET ROUND
				$sql = "SELECT surfer_id,result FROM heat_live 
								WHERE round=$currentround AND heat=$currentheat ORDER BY result";
				
				$result = $this->db_connection->query($sql);
				
				while($row = mysqli_fetch_array($result)){$liveheat[$row['result']] = $row['surfer_id'];}
				
				return $liveheat;
					
			}//connection errors
			
		}
		
		private function getLeagueEventStandings($heats,$current,$user_id,$picksbyuser,$users,$surfers){
			
			$currentround = $current['round'];
			$currentheat = $current['heat'];
			
			$previousround = $currentround-1;
			$nextround = $currentround+1;
			$nextnextround = $currentround+2;
			
			
			foreach($heats as $round=>$v1){
				foreach($v1 as $heat=>$v3){
					foreach($v3 as $player=>$v4){
						
						$sid = $v4['sid'];
						$sco = $v4['sco'];
						$results[$sid][$round] = $sco;
												
						if($round!=1 && $round!=4 && $sco==2){
							$results[$sid]['sco'] = $this->getPointsPerRound($round)[$sco];
						}elseif($round==8 && $sco==1){
							$results[$sid]['sco'] = $this->getPointsPerRound($round)[$sco];
						}
						
					}				
				}
			}
			
			//find if surfers from users team are facing each other in future heats or future rounds
			foreach($heats as $round=>$v1){
				if($round>=$currentround){
					
					foreach($v1 as $heat=>$v2){
						
						if(($round==$currentround && $heat>$currentheat) || ($round>$currentround)){
							
							foreach($v2 as $k3=>$v3){
						
								$sid = $v3['sid'];$sco = $v3['sco'];
												
								foreach($picksbyuser as $uid=>$v1){
									foreach($v1 as $k1=>$hsid){
										if($k1<8 && $hsid==$sid){
											$picksinheat[$uid][$round][$heat]+=1;
										}
									}
								}
						
							}
							
						}
						
						
					}
					
				}
			}
			//returns $picksinheat[$uid][$round][$heat]
			
			foreach($picksbyuser as $uid=>$v1){
								
				foreach($v1 as $pos=>$pid){
										
					if(!empty($results[$pid]['sco'])){
						if($pos<8){
							$userscores[$uid] += $results[$pid]['sco'];
							$usercount[$uid]['out']+=1;
							$new_active_picks[$uid]['out'][$pid] = $results[$pid]['sco'];
						}
						elseif($pos>=8){
							$benchscores[$uid] += $results[$pid]['sco'] ."</br>";
							$benchcount[$uid]['out']+=1;
							$new_bench_picks[$uid]['out'][$pid] = $results[$pid]['sco'];
						}	
					}
										
					elseif(empty($results[$pid]) && $surfers[$pid]['status']==1){
						if($pos<8){
							$userscores[$uid] += 500;
							$usercount[$uid]['inj']+=1;
							$new_active_picks[$uid]['inj'][] = $pid;
						}
						elseif($pos>=8){	
							$benchscores[$uid] += 500;
							$benchcount[$uid]['inj']+=1;
							$new_bench_picks[$uid]['inj'][] = $pid;
						}
					}
					
					elseif(isset($results[$pid][$currentround]) && $results[$pid][$currentround]==0){
						if($pos<8){
							$usercount[$uid]['act']+=1;
							$new_active_picks[$uid]['act'][] = $pid;
						}
						elseif($pos>=8){
							$benchcount[$uid]['act']+=1;
							$new_bench_picks[$uid]['act'][] = $pid;
						}
					}
					
					elseif(isset($results[$pid][$currentround]) && $results[$pid][$currentround]==1){
						if($pos<8){
							$usercount[$uid]['win']+=1;
							$new_active_picks[$uid]['win'][] = $pid;
						}
						elseif($pos>=8){
							$benchcount[$uid]['win']+=1;
							$new_bench_picks[$uid]['win'][] = $pid;
						}
					}
					
					elseif(isset($results[$pid][$currentround]) && ($results[$pid][$currentround]==2 || $results[$pid][$currentround]==3)){
						if($pos<8){
							$usercount[$uid]['rel']+=1;
							$new_active_picks[$uid]['rel'][] = $pid;
						}
						elseif($pos>=8){
							$benchcount[$uid]['rel']+=1;
							$new_bench_picks[$uid]['rel'][] = $pid;
						}
					}
					
					elseif(!isset($results[$pid][$currentround]) && empty($results[$pid]['sco']) && $results[$pid][$previousround]==1){
						if($pos<8){
							$usercount[$uid]['win']+=1;
							$new_active_picks[$uid]['win'][] = $pid;
						}
						elseif($pos>=8){
							$benchcount[$uid]['win']+=1;
							$new_bench_picks[$uid]['win'][] = $pid;
						}
					}
					
					
					
				}
				
				///------------
				if(!empty($new_active_picks[$uid]['win'] )){sort($new_active_picks[$uid]['win']);}
				if(!empty($new_bench_picks[$uid]['win']  )){sort($new_bench_picks[$uid]['win']);}
				if(!empty($new_active_picks[$uid]['out'] )){arsort($new_active_picks[$uid]['out']);}
				if(!empty($new_bench_picks[$uid]['out']  )){arsort($new_bench_picks[$uid]['out']);}
				if(!empty($new_active_picks[$uid]['rel'] )){sort($new_active_picks[$uid]['rel']);}
				if(!empty($new_bench_picks[$uid]['rel']  )){sort($new_bench_picks[$uid]['rel']);}
				if(!empty($new_active_picks[$uid]['act'] )){sort($new_active_picks[$uid]['act']);}
				if(!empty($new_bench_picks[$uid]['act']  )){sort($new_bench_picks[$uid]['act']);}
				if(!empty($new_active_picks[$uid]['inj'] )){sort($new_active_picks[$uid]['inj']);}
				if(!empty($new_bench_picks[$uid]['inj']  )){sort($new_bench_picks[$uid]['inj']);}
				
				
				$potentialpts = $this->getPotentialPoints($heats,$current,$picksinheat[$uid],$userscores[$uid],$usercount[$uid]);
				
				$best_points[$uid] = $potentialpts['best'];
				$worst_points[$uid] = $potentialpts['worst'];
				
			}
			
			if(!empty($best_points)){arsort($best_points);}
			if(!empty($userscores)){arsort($userscores);} 
			
			$leaguetable.= "<div class='row align-center'><div class='large-10 small-12 columns'>";
			
			foreach($best_points as $uid=>$bestpoints){
				
				$leaguetable.= "<div class='row is-collapse-child leaguestandings'>";
				
				$leaguetable.= "<div class='large-2 small-2 columns standingsuser'>".$users[$uid]['short']."</div>";
				
				if(!empty($new_active_picks[$uid]['win'])){foreach($new_active_picks[$uid]['win'] as $k=>$sid){
						$leaguetable.= "<div class='columns standingswin'>
															<span data-tooltip aria-haspopup='true' class='has-tip' title='".$surfers[$sid]['name']."'>
																	" .$surfers[$sid]['aka'] ."
															</span>
														</div>";
				}}
				
				if(!empty($new_active_picks[$uid]['act'])){foreach($new_active_picks[$uid]['act'] as $k=>$sid){
					$leaguetable.= "<div class='columns standingsact'>
														<span data-tooltip aria-haspopup='true' class='has-tip' title='".$surfers[$sid]['name']."'>
														" .$surfers[$sid]['aka'] ."
														</span>
													</div>";
				}}
				
				if(!empty($new_active_picks[$uid]['rel'])){foreach($new_active_picks[$uid]['rel'] as $k=>$sid){
					$leaguetable.= "<div class='columns standingsrel'>
														<span data-tooltip aria-haspopup='true' class='has-tip' title='".$surfers[$sid]['name']."'>
														" .$surfers[$sid]['aka'] ."
														</span>
													</div>";
				}}
				
				if(!empty($new_active_picks[$uid]['out'])){foreach($new_active_picks[$uid]['out'] as $sid=>$points){
					if($points==500){$pointsclass="outrd2";}
					elseif($points==1750){$pointsclass="outrd3";}
					elseif($points==4000){$pointsclass="outrd5";}
					elseif($points==5200){$pointsclass="outrd6";}
					elseif($points==6500){$pointsclass="outrd7";}
					elseif($points==8000){$pointsclass="outrd8";}
					elseif($points==10000){$pointsclass="outrd8";}
					$leaguetable.= "<div class='columns standingsout $pointsclass'>";
					
					$leaguetable.= "<div class='outsurfer'>
														<span data-tooltip aria-haspopup='true' class='has-tip' title='".$surfers[$sid]['name']."'>
															".$surfers[$sid]['aka']."
														</span>
													</div>";
													
					$leaguetable.= "<div class='outpoints'>$points</div>"; 
					$leaguetable.= "</div>";
				}}
				
				if(!empty($new_active_picks[$uid]['inj'])){foreach($new_active_picks[$uid]['inj'] as $k=>$sid){
					$leaguetable.= "<div class='large-1 small-1 columns standingsinj'>";
					$leaguetable.= "<div class='injsurfer'>
														<span data-tooltip aria-haspopup='true' class='has-tip' title='".$surfers[$sid]['name']."'>
														".$surfers[$sid]['aka']."
														</span>
													</div>";
					
					$leaguetable.= "<div class='injpoints'>
													<span data-tooltip aria-haspopup='true' class='has-tip' title='500 points'>
														INJ
													</span>
													</div>";
					
					$leaguetable.= "</div>";
				}}
				
				$leaguetable.= "<div class='large-1 hide-for-small-only columns projectionpoints'>
					
					
								<div class='bestpoints'>
										<span data-tooltip aria-haspopup='true' class='has-tip' title='Best possible score'>
														".number_format($bestpoints, 0,'',',')."
										</span>
								</div>
								
								<div class='worstpoints'>
									<span data-tooltip aria-haspopup='true' class='has-tip' title='Worst possible score'>
									".number_format($worst_points[$uid], 0,'',',')."
									</span>
								</div>
							
							</div>";
				
				$leaguetable.= "<div class='large-1 hide-for-small-only columns standingspoints'>"
													.number_format($userscores[$uid], 0,'',',').
												"</div>";
				
				
				$leaguetable.= "</div>";
			}
			
			$leaguetable.= "</div></div>";
			
			return $leaguetable;
			
		}
		
		public function displayLiveHeat($user_id,$current,$surfers,$users,$picksbysurfer){
			
			$currentround = $current['round'];
			$currentheat = $current['heat'];
			
			$liveheat = $this->getLiveHeat($currentround,$currentheat);
			
			if(!empty($liveheat)){
				
				$display .= "<div class='row liveheat'><div class='large-12 columns heattitle'>LIVE - Heat $currentheat</div>";
			
				foreach($liveheat as $sco=>$sid){
					$v2['sid'] = $sid;$v2['sco'] = $sco;
					
					$display.= $this->showHeatSurfer($user_id,$currentround,$v2,$surfers,$users,$picksbysurfer);
				
				}
			
				$display .= "</div>";
			
				return $display;
				
			}
			
		}
		
		public function displayCurrentRound($user_id,$heats,$current,$leaguepicks,$surfers){
			
			$picksbyuser = $leaguepicks['byuser'];
			$picksbysurfer = $leaguepicks['bysurfer'];
			$users = $leaguepicks['users'];
			
			$currentround = $current['round'];
			$currentheat = $current['heat'];
			
			$liveheat = $this->displayLiveHeat($user_id,$current,$surfers,$users,$picksbysurfer);
			
			if($currentround==1 || $currentround==4){$breakpoint = 3;}
			elseif($currentround!=1 && $currentround!=4){$breakpoint = 2;}
			
			//UPCOMING HEATS
			$i=0;
			foreach($heats[$currentround] as $heat=>$v1){	
				foreach($v1 as $k2=>$v2){
					if($v2['sco']==0 && $heat!=$currentheat){
						if($i==0){$nextheats.= "<div class='row upcomingheat'><div class='large-12 columns heattitle'>Heat $heat</div>";}
						$nextheats.= $this->showHeatSurfer($user_id,$currentround,$v2,$surfers,$users,$picksbysurfer);
						$i++;
						if($i==$breakpoint){$nextheats.= "</div>";$i=0;}						
					}
					elseif($v2['sco']==0 && $heat==$currentheat){
						if($i==0){
							$nextheats.= "<div class='row thisisliveheat'><div class='large-12 columns heattitle'>Live - Heat $heat</div>";						
						}
						$i++;
						if($i==$breakpoint){$nextheats.= "</div>";$i=0;}
					}
				}
			}
			
			//LEAGUE PICKS TABLE
			$leagueresults = $this->getLeagueEventStandings($heats,$current,$user_id,$picksbyuser,$users,$surfers);
			
			//PAST (SCORED) HEATS
			$i=0;
			foreach($heats[$currentround] as $heat=>$v1){	
				foreach($v1 as $k2=>$v2){
					if($v2['sco']>0){
						if($i==0){$pastheats.= "<div class='row finishedheat'><div class='large-12 columns heattitle'>Heat $heat</div>";}
						$pastheats.= $this->showHeatSurfer($user_id,$currentround,$v2,$surfers,$users,$picksbysurfer);
						$i++;
						if($i==$breakpoint){$pastheats.= "</div>";$i=0;}						
					}
				}
			}
			
			return $liveheat.$leagueresults.$pastheats.$nextheats;
			
		}
		
		private function getPositionTitle($pos){
			
			//FIRST CALCULATE SCORES
			if($pos==25){
				$position = "25<sup>th</sup>";
			}elseif($pos==13){
				$position = "13<sup>th</sup>";
			}elseif($pos==9){
				$position = "9<sup>th</sup>";
			}elseif($pos==5){
				$position = "5<sup>th</sup>";
			}elseif($pos==3){
				$position = "3<sup>rd</sup>";
			}elseif($pos==2){
				$position = "2<sup>nd</sup>";
			}elseif($pos==1){
				$position = "Winner";
			}
			
			
			
			return $position;
			
		}
		
		public function displayPrevRoundScores($user_id,$event_id,$leaguepicks,$surfers){
			
			$fsbasics = new FSBasics();
			
			$results = $fsbasics->getEventResults($event_id);
			
			$picksbysurfer = $leaguepicks['bysurfer'];
			$users = $leaguepicks['users'];
			
			if(!empty($results)){
				
				foreach($results as $pos=>$v1){
				
					$postitle = $this->getPositionTitle($pos);
				
					$roundresults.= "<div class='row eventresultsrow' id='pos$pos'>
															<div class='small-12 columns resultsposition'>$postitle</div>";
				
					foreach($v1 as $k2=>$sid){	
					
						//get surfers image div
						$thisheatimg = $this->getSurferImg($sid,$surfers);
					
						//if its owned, find who had them active and who benched them
						if(!empty($picksbysurfer[$sid])){$thisowners = $this->findActiveOwners($user_id,$sid,$picksbysurfer,$users);}
					
						$roundresults.= "<div class='large-3 medium-4 small-6 columns resultssurfer'>";
						$roundresults.= $thisheatimg;
						$roundresults.= $surfers[$sid]['name'];
						$roundresults.= "<div class='row is-collapse-child pickedbyusers align-center small-up-1 medium-up-4 large-up-5'>" .$thisowners.$thisbenched ."</div>";
						$roundresults.= "</div>";
					}
					$roundresults.= "</div>";
				}
				
			}
			
			
			
			return $roundresults;
				
		}
		
}//Class Event
?>