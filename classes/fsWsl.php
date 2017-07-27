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
			
		if(!empty($scores[$sid][1]['pos'])){$pos1=$scores[$sid][1]['pos'];$sco1=$scores[$sid][1]['pts'];}else{$pos1="--";$sco1="";}
		if(!empty($scores[$sid][2]['pos'])){$pos2=$scores[$sid][2]['pos'];$sco2=$scores[$sid][1]['pts'];}else{$pos2="--";$sco2="";}
		if(!empty($scores[$sid][3]['pos'])){$pos3=$scores[$sid][3]['pos'];$sco3=$scores[$sid][1]['pts'];}else{$pos3="--";$sco3="";}
		if(!empty($scores[$sid][4]['pos'])){$pos4=$scores[$sid][4]['pos'];$sco4=$scores[$sid][1]['pts'];}else{$pos4="--";$sco4="";}
		if(!empty($scores[$sid][5]['pos'])){$pos5=$scores[$sid][5]['pos'];$sco5=$scores[$sid][1]['pts'];}else{$pos5="--";$sco5="";}
		if(!empty($scores[$sid][6]['pos'])){$pos6=$scores[$sid][6]['pos'];$sco6=$scores[$sid][1]['pts'];}else{$pos6="--";$sco6="";}
		if(!empty($scores[$sid][7]['pos'])){$pos7=$scores[$sid][7]['pos'];$sco7=$scores[$sid][1]['pts'];}else{$pos7="--";$sco7="";}
		if(!empty($scores[$sid][8]['pos'])){$pos8=$scores[$sid][8]['pos'];$sco8=$scores[$sid][1]['pts'];}else{$pos8="--";$sco8="";}
		if(!empty($scores[$sid][9]['pos'])){$pos9=$scores[$sid][9]['pos'];$sco9=$scores[$sid][1]['pts'];}else{$pos9="--";$sco9="";}
		if(!empty($scores[$sid][10]['pos'])){$pos10=$scores[$sid][10]['pos'];$sco10=$scores[$sid][1]['pts'];}else{$pos10="--";$sco10="";}
		if(!empty($scores[$sid][11]['pos'])){$pos11=$scores[$sid][11]['pos'];$sco11=$scores[$sid][1]['pts'];}else{$pos11="--";$sco11="";}
			
		$display.='
							<div class="row align-center align-middle wslsurfer hide-for-small-only">
								<div class="large-1 medium-1 columns" style="padding:0px;">
									<div class="wslsurferrank">'.$ranking.'</div>
									<div class="wslsurferteam">'.$team.'</div>
								</div>
							
							';
						
							echo $display;
		}
		
		
	}	
		
		
}//Class WSL
?>