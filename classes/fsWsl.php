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
		
		$user_id = 107; //eventually get userr id using session
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
	
	
	public function getWslRankings(){
		
		$scores = $this->calculateRankings();
		
		$last_event = 6; //eventually get event using last event in status 2 / 3 / 4
		
		//create ranking based on scores
		foreach($scores as $sid=>$v1){
			$totals[$sid] = $v1['total'];
		}
		arsort($totals);
		
		$prevtot = 0;$ranking=0;
		foreach($totals as $sid=>$total){
			
			//calculate ranking
			if($prevtot==$total){}else{$ranking++;$prevtot=$total;}

			//calculate availability
			if($scores[$sid]['picked']==1){
				$team = '<i style="font-size:14pt;margin-top:6px;" class="material-icons">check_circle</i>';
			}elseif($scores[$sid]['avail']>0){
				$team = '<i style="font-size:14pt;margin-top:6px;" class="material-icons">timelapse</i>';
			}else{
				$team = '<i style="font-size:14pt;margin-top:6px;" class="material-icons">do_not_disturb_on</i>';
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
							<div class="row align-center align-middle wslsurfer hide-for-small-only">
								<div class="large-1 medium-1 columns" style="padding:0px;">
									<div class="wslsurferrank">'.$ranking.'</div>
									<div class="wslsurferteam">'.$team.'</div>
								</div>
								
								<div class="large-3 medium-4 columns wslsurfername">'.$name.'</div>
								
								<div class="large-6 medium-5 columns wslsurferresults">
										<div class="row">
											<div class="performanceresult event'.$stat[1].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Quiksilver Pro Gold Coast">
													'.$pos[1].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[2].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Drug Aware Margaret River Pro">
													'.$pos[2].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[3].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Rip Curl Pro Bells Beach">
													'.$pos[3].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[4].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Oi Rio Pro">
													'.$pos[4].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[5].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Fiji Pro">
													'.$pos[5].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[6].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Corona Open J-Bay">
													'.$pos[6].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[7].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Billabong Pro Tahiti">
													'.$pos[7].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[8].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Hurley Pro at Trestles">
													'.$pos[8].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[9].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Quiksilver Pro France">
													'.$pos[9].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[10].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="MEO Rip Curl Pro Portugal">
													'.$pos[10].'
												</span>
											</div>
											<div class="performanceresult event'.$stat[11].'">
												<span data-tooltip aria-haspopup="true" class="has-tip" title="Billabong Pipe Masters">
													'.$pos[11].'
												</span>
											</div>
										</div>
								</div>
								
								<div class="large-1 medium-1 columns wslsurferpoints">'.number_format($total).'</div>
								
								<div class="large-1 medium-1 columns wslsurferexpand closedchevron"> <i class="material-icons">chevron_left</i> </div>
								
							</div>
							';
						
		
		}
		
		
		echo $display;
		
	}	
		
		
}//Class WSL
?>