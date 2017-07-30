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
		
		$user_id = 105; //eventually get userr id using session
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
							WHERE s.event>0 AND w.wildcard=0";
					
			
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
	
	public function getHeatResults(){
		
		//Get heat results
		
		$this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	
		if (!$this->db_connection->set_charset("utf8")) {$this->errors[] = $this->db_connection->error;}
		
		if (!$this->db_connection->connect_errno) {
		
			$sql = "SELECT event_id,round,heat,result,surfer_id FROM heats";
					
     		$result = $this->db_connection->query($sql);
	
			while($row = mysqli_fetch_array($result)){
				//figure out the result and write as text

				if($row['round']==1 || $row['round']==4){
					if($row['result']==1){
						$heats[$row['surfer_id']][$row['event_id']][$row['round']] = "won";
						$heats[$row['surfer_id']][$row['event_id']][$row['round']+1] = "won";
					}else{
						$heats[$row['surfer_id']][$row['event_id']][$row['round']] = "relegated";
					}
					
				}else{
					if($row['result']==1){
						$heats[$row['surfer_id']][$row['event_id']][$row['round']] = "won";
					}else{
						$heats[$row['surfer_id']][$row['event_id']][$row['round']] = "lost";
					}
					
				}
				
			}
		
		}
		
		
		return $heats;
		
	}
	
	public function getWslRankings(){
		
		$position[2] = 25;
		$position[3] = 13;
		$position[5] = 9;
		$position[6] = 5;
		$position[7] = 3;
		$position[8] = 2;
		
		$scores = $this->calculateRankings();
		$heats = $this->getHeatResults();
		
		$last_event = 6; //eventually get event using last event in status 2 / 3 / 4
		$events[1]['name'] = "Quiksilver Pro Gold Coast";
		$events[2]['name'] = "Drug Aware Margaret River Pro";
		$events[3]['name'] = "Rip Curl Pro Bells Beach";
		$events[4]['name'] = "Oi Rio Pro";
		$events[5]['name'] = "Outerknown Fiji Pro";
		$events[6]['name'] = "Corona Open J-Bay";
		$events[7]['name'] = "Billabong Pro Tahiti";
		$events[8]['name'] = "Hurley Pro at Trestles";
		$events[9]['name'] = "Quiksilver Pro France";
		$events[10]['name'] = "MEO Rip Curl Pro Portugal";
		$events[11]['name'] = "Billabong Pipe Masters";
		
		//create ranking based on scores
		foreach($scores as $sid=>$v1){
			$totals[$sid] = $v1['total'];
		}
		arsort($totals);
		
		$prevtot = 0;$ranking=0;$oddcount = 0;
		foreach($totals as $sid=>$total){
			
			if($oddcount % 2==0){$isodd="oddline";}else{$isodd="evenline";}
			$oddcount++;
			
			//calculate ranking
			if($prevtot==$total){}else{$ranking++;$prevtot=$total;}

			//calculate availability
			if($scores[$sid]['picked']==1){
				$team = '<i style="font-size:14pt;margin-top:3px;color:#00A633;" class="material-icons">check_circle</i>';
			}elseif($scores[$sid]['avail']>0){
				$team = '<i style="font-size:14pt;margin-top:3px;color:#F29F05;" class="material-icons">timelapse</i>';
			}else{
				$team = '<i style="font-size:14pt;margin-top:3px;color:#D92525;" class="material-icons">do_not_disturb_on</i>';
			}
			
			$name = $scores[$sid]['name'];
			
			for($i=1;$i<12;$i++){
				
				if($i<=$last_event){
					
					if(!empty($scores[$sid][$i]['pos']) && $scores[$sid][$i]['pos']<1000){
						$pos[$i] = $scores[$sid][$i]['pos'];
						$sco[$i] = $scores[$sid][$i]['pts'];
						$stat[$i] = $scores[$sid][$i]['pos'];
					}elseif($scores[$sid][$i]['pos']==1000){
						$pos[$i] = "O";
						$stat[$i] = "injured";
					}else{
						$pos[$i] = "--";
						$stat[$i] = "out";
					}
					
				}elseif($i>$last_event){
					
					$pos[$i] = "--";
					$sco[$i] = 0;
					$stat[$i] = "unsurfed";
					
				}
				
			}
			
		$display.='
							<div class="row align-center align-middle wslsurfer '.$isodd.' hide-for-small-only">
								<div class="large-1 medium-1 columns" style="padding:0px;">
									<div class="wslsurferrank">'.$ranking.'</div>
									<div class="wslsurferteam">'.$team.'</div>
								</div>
								
								<div class="large-3 medium-4 columns wslsurfername">'.$name.'</div>
								
								<div class="large-6 medium-5 columns wslsurferresults">
										<div class="row">';
								
										for($e=1;$e<=11;$e++){
											
											
								$display.= "<div class='performanceresult event".$stat[$e]."'>
											<span data-tooltip aria-haspopup='true' class='has-tip' title='".$events[$e]['name']."'>
												".$pos[$e]."
											</span>
											</div>";
											
										}
										
								
											
										$display.= '
										</div>
								</div>
								
								<div class="large-1 medium-1 columns wslsurferpoints">'.number_format($total).'</div>
								
								<div class="large-1 medium-1 columns wslsurferexpand closedchevron"> <i class="material-icons">chevron_left</i> </div>
								
							</div>
							';
						
			$display.= '<div class="small-12 columns expandedsurfer"><div class="row align-center align-middle">
						<div class="small-12 columns '.$isodd.'" style="padding-bottom:15px;">
						<div class="row align-middle" style="margin-top:10px">';
			
			
			$display.= '<div class="small-12 show-for-small-only columns performancelabel">EVENT PERFORMANCE</div>';
			
			for($e=1;$e<=$last_event;$e++){
				
				$display .= '<div class="large-3 medium-4 large-offset-1 medium-offset-1 columns performanceeventname hide-for-small-only">'.$events[$e]['name'].'</div>';
				$display .= '<div class="large-4 small-1 columns performanceeventnumber show-for-small-only">'.$e.'</div>';
				$display.= '<div class="large-7 medium-6 small-11 columns"><div class="row align-middle">';
				
				for($r=1;$r<=8;$r++){
					
					if($heats[$sid][$e][$r] == "won"){
						if($r<8){
							$display .= '<div class="performanceround performancewon">&nbsp;</div>';
						}elseif($r==8){
							$display .= '<div class="performanceround performancewon">WON</div>';
						}
					}elseif($heats[$sid][$e][$r] == "relegated"){
						$display .= '<div class="performanceround performancerelegated">&nbsp;</div>';
					}elseif($heats[$sid][$e][$r] == "lost"){
						if($r<6){
							$display .= '<div class="performanceround event'.$position[$r].'">RD'.$r.'</div>';
						}elseif($r==6){
							$display .= '<div class="performanceround event5">QF</div>';
						}elseif($r==7){
							$display .= '<div class="performanceround event3">SF</div>';
						}elseif($r==8){
							$display .= '<div class="performanceround event2">F</div>';
						}
						
					}else{
						$display .= '<div class="performanceround performanceout">&nbsp;</div>';
					}
					
				}
				
				$display .= '</div></div>';
				
			}
			
			$display.= '</div></div></div></div>';
			//END LARGE SCREEN BUILD
			
			//START SMALL SCREEN BUILD
			$display.= '<div class="leagueleaderboard show-for-small-only '.$isodd.'">';
			$display.= '<div class="row align-center align-middle sm-standings-row closedchevron" id="sm-lbu'.$sid.'">';
			
			$display.= '<div class="small-1 columns sm-leaderboard-rank">'.$ranking.'</div>';
			
			
			if($scores[$sid]['picked']==1){
				$display.= '<div class="small-1 columns sm-leaderboard-status">
								<i class="material-icons" style="font-size:12pt;margin-top:3px;color:#00A633;margin-top:6px;">check_circle</i>
							</div>';
			}elseif($scores[$sid]['avail']>0){
				$display.= '<div class="small-1 columns sm-leaderboard-status">
								<i class="material-icons" style="font-size:12pt;margin-top:3px;color:#F29F05;margin-top:6px;">timelapse</i>
							</div>';
			}else{
				$display.= '<div class="small-1 columns sm-leaderboard-status">
								<i class="material-icons" style="font-size:12pt;margin-top:3px;color:#D92525;margin-top:6px;">do_not_disturb_on</i>
							</div>';
			}
			
			$display.= '<div class="small-5 columns sm-leaderboard-username" id="sm-lbu'.$sid.'n">'.$name.'</div>';
			
			$display.= '<div class="small-4 columns sm-leaderboard-total" id="sm-lbu'.$sid.'n">'.number_format($total).'</div>';
			
			$display.= '<div class="small-1 columns sm-leaderboard-chevron" id="sm-lbu'.$sid.'n">
							<i class="material-icons" style="margin-top:6px;">keyboard_arrow_left</i>
						</div>';
				
			$display.= '<div class="small-12 columns noselect expandedsurfer '.$isodd.'" id="show-sm-lbu'.$sid.'"><div class="row align-middle" style="padding-bottom:15px;">';
			
			$display.= '<div class="small-12 show-for-small-only columns performancelabel" style="margin-top:10px">EVENT RESULTS</div>';
			
			for($e=1;$e<=11;$e++){	
				$display.= "<div class='performanceresult event".$stat[$e]."'>
									<span data-tooltip aria-haspopup='true' class='has-tip' title='".$events[$e]['name']."'>
										".$pos[$e]."
									</span>
								</div>";
			}
			
			$display.= '<div class="small-12 columns">
						<div class="row align-middle" style="margin-top:10px">
						<div class="small-12 show-for-small-only columns performancelabel">EVENT PERFORMANCE</div>';
						
			for($e=1;$e<=$last_event;$e++){
				$display.= '<div class="large-4 small-1 columns performanceeventnumber show-for-small-only">'.$e.'</div>';
				$display.= '<div class="small-11 columns"><div class="row align-middle">';
				
				for($r=1;$r<=8;$r++){
					
					if($heats[$sid][$e][$r] == "won"){
						if($r<8){
							$display .= '<div class="performanceround performancewon">&nbsp;</div>';
						}elseif($r==8){
							$display .= '<div class="performanceround performancewon">WON</div>';
						}
					}elseif($heats[$sid][$e][$r] == "relegated"){
						$display .= '<div class="performanceround performancerelegated">&nbsp;</div>';
					}elseif($heats[$sid][$e][$r] == "lost"){
						if($r<6){
							$display .= '<div class="performanceround event'.$position[$r].'">RD'.$r.'</div>';
						}elseif($r==6){
							$display .= '<div class="performanceround event5">QF</div>';
						}elseif($r==7){
							$display .= '<div class="performanceround event3">SF</div>';
						}elseif($r==8){
							$display .= '<div class="performanceround event2">F</div>';
						}
						
					}else{
						$display .= '<div class="performanceround performanceout">&nbsp;</div>';
					}
					
				}
				
				$display.= '</div></div>';
			}
						
			$display.= '</div></div>';
			
			$display.= '</div></div>';
			
			$display.= '</div>';
			
			$display.= '</div></div>';
			
		}
		
		
		echo $display;
		
	}	
		
		
}//Class WSL
?>