<?php

class Traffic{

    public function __construct(){

        session_start();
				require_once("../config/db.php");
				include_once("fsbasics.php");
				include_once("fsevent.php");
				include_once("fsscores.php");
				include_once("fslineup.php");
    }
		
		
		
		
		public function getLeagueAndEventStatus(){
			
			$fsbasics = new FSBasics();
			
			$league = $fsbasics->getUsersLeague();
			
			if($league['status']==3){

				$event = $fsbasics->checkForRunningEvents();
				
				if($event==0){return 1;}elseif($event==1){return 2;}
				
			}
			else{return 1;}
			
		}
		
		public function getLeaguePage(){
			
			$fsbasics = new FSBasics();
			
			$user_id = $_SESSION['user_id'];
			$user_name = $_SESSION['user_name'];
			$user_team = $_SESSION['user_team'];			
			
			$league = $fsbasics->getUsersLeague();
			
			if(empty($user_id) || empty($user_team)){
				
				return "setnameandteam";
				
			}elseif($league['status']==1){
					
				//league is open
				$members = $fsbasics->getLeagueUsers($league);
				$availsurfers = $fsbasics->getAvailableSurfers($members);
				$availsurfers = $fsbasics->getSurferRankings($availsurfers);
				
				$menu = "<div class='row expanded align-center navmenu'>
											<div class='large-3 small-6 columns navitem first selected' id='showMembers'>League Members</div>
											<div class='large-3 small-6 columns navitem second' id='showAvails'>Available Surfers</div>
										</div>";
				
				$leaguetitle = $this->showLeagueTitle($league);
				$display_openleague = $this->prepareOpenLeagueDisplay($members);
				$display_availsurfers = $this->prepareAvailSurfersDisplay($availsurfers);
				
				if($league['admin']==$user_id){
					$admin_display = "<div class='row column align-center' style='text-align:center;'>
															<button class='button startdraftbutton' id='start.".$league['id']."'>Start Draft</button>
														</div>";
				}else{
					$admin_display .= "<div class='row expanded column openleaguestatus'>Draft will be starting soon...</div>";
				}
				
				return $menu.$leaguetitle.$display_openleague.$display_availsurfers.$admin_display;

					
			}elseif($league['status']==2){
				
				//league is in draft
				$leaguetitle = $this->showLeagueTitle($league);
				
				$draft_button = "<div class='row align-center align-middle' style='text-align:center;height:80vh;'>
														<div class='large-6 columns'>
															Draft has started.</br>
															<button class='button enterdraft' id='lid".$league['id']."'>Go To Draft</button>
														</div>
													</div>";
				
				return $leaguetitle.$draft_button;
				
			}elseif($league['status']==3){
				
				$fsbasics = new FSBasics();
				$fslineup = new FSLineup();
				$fsscores = new FSScores();
				
				$events = $fsbasics->getAllEvents();
				$surfers = $fsbasics->getSurfers();//add get rankings
				$surfers = $fsbasics->getSurferRankings($surfers);
				
				if(!empty($events['current'])){
					
					if($events['byid'][$events['current']]['status']==1){
						
						//show movable lineup and wildcard requests
						
						$lineup = $fslineup->getUsersLineup($user_id,$league,$events);
						$requests = $fslineup->getUsersWildcardRequests($user_id,$league,$events);
						$wildcards = $fslineup->getAvailableWildcards($events,$requests);
						
						$leaderboard = $fsscores->getLeagueLeaderboard($user_id,$league);
						
						if(!empty($requests)){
							$requests_display = $this->prepareRequestsDisplay($league,$events,$requests,$wildcards,$surfers);
						}else{$requests_display = "";}
						
						$lineup_display = $this->prepareLineupDisplay($user_id,$lineup,$surfers,$wildcards,$events);
						
						if(!empty($wildcards)){
							$grouped_wc = $fslineup->calculateAvailableWildcards($lineup,$wildcards,$requests);
							$availables_display = $this->prepareWildcardDisplay($grouped_wc,$wildcards,$surfers,$events,$league);
						}else{$wildcards = "";}
						
						$display.= "<div class='row expanded align-center navmenu'>
													<div class='large-3 small-6 columns navitem first selected' id='showLineup'>Lineup</div>
													<div class='large-3 small-6 columns navitem second' id='showLeaderboard'>Leaderboard</div>
												</div>";
												
						$display.= "<div class='leaderboardtable isHidden'>$leaderboard</div>";
						
						
						$display.= "<div class='row align-center align-middle userlineup'>
												<div class='small-12 columns wcrequests'>".$requests_display."</div>
												<div class='small-12 columns lineupteam'>".$user_team."</div>
												<div class='small-12 columns thelineup'>".$lineup_display."</div>
												<div class='small-12 columns availablewc'>".$availables_display."</div>
												</div>";
						

						
						return $display;
						
					}
					
					if($events['byid'][$events['current']]['status']==2){
						
						//show movable lineup 
						$lineup = $fslineup->getUsersLineup($user_id,$league,$events);
						$requests = $fslineup->getUsersWildcardRequests($user_id,$league,$events);
						$wildcards = $fslineup->getAvailableWildcards($events,$requests);
						
						$report_display = $fslineup->viewRequestReport($league,$events);
						$requests_display = $this->prepareReportDisplay($user_id,$events,$league,$requests,$surfers);
						$lineup_display = $this->prepareLineupDisplay($user_id,$lineup,$surfers,$wildcards,$events);
						
						$display.= $report_display;
						$display.= "<div class='row align-center'><div class='large-10 columns userlineup'>".$requests_display."</div></div>";
						$display.= "<div class='row align-center'><div class='large-10 columns userlineup'>".$lineup_display."</div></div>";
						
						return $display;
						
					}
					
				}
				elseif(empty($events['current'])){
					
					//show movable lineup
					
					$leaderboard = $fsscores->getLeagueLeaderboard($user_id,$league);
					
					$lineup = $fslineup->getUsersLineup($user_id,$league,$events);
					$lineup_display = $this->prepareLineupDisplay($user_id,$lineup,$surfers,$wildcards,$events);
					
					$display.= "<div class='row expanded align-center navmenu'>
												<div class='small-3 columns navitem first' id='showLineup'>Lineup</div>
												<div class='small-3 columns navitem second selected' id='showLeaderboard'>Leaderboard</div>
											</div>";
					$display.= "<div class='leaderboardtable'>$leaderboard</div>";
					$display.= "<div class='row align-center lineupcontent isHidden'>
											<div class='large-10 columns userlineup'>".$lineup_display."</div></div>";
					
					return $display;
					
				}
				
			}	
			
		}
		
		public function prepareOpenLeagueDisplay($members){
			
			$display .= "<div class='openleaguetab'>";
			
			$display .= "<div class='row column openleagueheader'>League Teams</div>";
			
			$display.= "<div class='openleaguetable'>";
			
			foreach($members as $uid=>$v1){
				$this_name = $v1['name'];
				$this_team = $v1['team'];
				
				if(!empty($this_name) && !empty($this_team)){
					
					$display.= "<div class='row align-center align-middle joinedplayer'>
												<div class='large-4 medium-5 small-8 columns'>
													<div class='joinedname'>".strtolower($this_name)."</div>
													<div class='joinedteam'>".strtolower($this_team)."</div>
												</div>
											</div>";
					
				}
				
			}
			
			$display .= "</div>";
			
			$display .= "</div>";
			
			return $display;
			
		}
		
		public function prepareAvailSurfersDisplay($surfers){
			
			$display.= "<div class='row align-middle align-center availsurferstab isHidden'><div class='large-6 columns'>";
			
			$display.= "<div class='large-12 columns'><h5>Available Surfers</h5></div>";
			$display.= "<div class='large-12 columns'>
							<p>These are the surfers you'll be able to pick from once the draft starts. Each surfer has a limited availability - only a number of players can pick him. The availability shown below will change if more players join the league before the draft starts.</p>
					</div>";
			
			$display.= "<div class='avaialblesurferheader row is-collapse-child align-middle'>";
			$display.= "<div class='large-2 medium-2 small-2 columns'><b>Rank</b></div>";
			$display.= "<div class='shrink columns' style='width:50px;'></div>";
			$display.= "<div class='large-5 columns'> </div>";
			$display.= "<div class='columns'><b>Available</b></div>";
			$display.= "</div>";
			
			foreach($surfers as $sid=>$v1){
				$display.= "<div class='avaialblesurferrow row is-collapse-child align-middle'>";
				
				$display.= "<div class='avaialblerank large-2 medium-2 small-2 columns'>".$surfers[$sid]['rank']."</div>";
				
				$display.= "<div class='imgcontainer large-2 medium-2 small-2 columns'>
									<div class='availableimg' style='
									background: url(img/".$surfers[$sid]['img'].");
									background-size: 63px 50px;
									background-repeat: no-repeat;
									background-position: center;
									'> </div>
							</div>";
				
				$display.= "<div class='availablename large-5 columns'>".$surfers[$sid]['name'] ."</div>";
				
				$display.= "<div class='availablequantity columns'>".$surfers[$sid]['available'] ."</div>";
				
				$display.= "</div>";
			}
			
			$display.= "</div></div>";
			
			return $display;
			
		}
		
		public function prepareLineupDisplay($user_id,$lineup,$surfers,$wildcards,$events){
			
			//GET ALL ARRAYS IF EMPTY
			
			if(empty($user_id) || empty($surfers) || empty($events) || empty($wildcards)){
				
				if(empty($user_id)){$user_id = $_SESSION['user_id'];}
			
				if(empty($surfers) || empty($events)){
					$fsbasics = new FSBasics();
					if(empty($events)){$events = $fsbasics->getAllEvents();}
					if(empty($surfers)){$surfers = $fsbasics->getSurfers();}
				}
			
				if(empty($wildcards)){$fslineup = new FSLineup();$wildcards = $fslineup->getAvailableWildcards($events,0);}
				
			}
			//END GET ALL ARRAYS
			
			//FIND EVENT ID & STATUS
			
			if(!empty($events['current'])){$event_id = $events['current'];$eventstatus = $events['byid'][$event_id]['status'];}
			
			//END FIND EVENT ID
			
			//organize lineup by active first and bench second
			foreach($lineup as $sid=>$v){$sac = $v['priority'];$orderedlineup[$sac] = $sid;}
			ksort($orderedlineup);
			
			//arrange wildcards by replaced=replacement
			if(!empty($wildcards)){
				foreach($wildcards as $wid=>$v1){
					if($v1['type']==1){
						$injuries[$v1['status']] = $wid;
					}
				}
			}
			
			
			foreach($orderedlineup as $k=>$sid){
				
				if($k<8){
					
					$lineuptable.="
						<div class='lineuprow lineupactiverow row is-collapse-child align-middle' id='lineup.$event_id.$k.$sid'>
					
						 <div class='lineuprank large-1 medium-1 small-2 columns'>".$surfers[$sid]['rank']."</div>

							<div class='imgcontainer shrink columns'>
									<div class='lineupimg' style='
									background: url(img/".$surfers[$sid]['img'].");
									background-size: 75px 60px;
									background-repeat: no-repeat;
									background-position: center;
									'> </div>
							</div>
							
							<div class='lineupname shrink columns'>".$surfers[$sid]['name'] ."</div>";
					
					if($surfers[$sid]['status']==1){
						$lineuptable.="<div class='lineupstatus columns'>INJ</div>";
					}
					
					if(!empty($wildcards[$sid]) && $eventstatus==1){
						$lineuptable.="<div class='removewcbtn row column align-right'>
															<button class='button removeinstantbtn'
							 												id='remove.$event_id.$league_id.$k.$sid.".$wildcards[$sid]['status']."'>
																			X
															</button>
														</div>";
					}
					
					$lineuptable .="</div>";
					
				}	
				
				elseif($k>7 && $k<=99){
					
					$lineuptable .="
						<div class='lineuprow lineupbenchrow row is-collapse-child align-middle' id='lineup.$event_id.$k.$sid'>
						
						<div class='lineuprank large-1 medium-1 small-2 columns'>".$surfers[$sid]['rank']."</div>
						
						<div class='imgcontainer shrink columns'>
								<div class='lineupimg' style='
								background: url(img/".$surfers[$sid]['img'].");
								background-size: 75px 60px;
								background-repeat: no-repeat;
								background-position: center;
								'> </div>
						</div>
						
						<div class='lineupname shrink columns'>".$surfers[$sid]['name'] ."</div>";
						
						if($surfers[$sid]['status']>=1){
							$lineuptable.="<div class='lineupstatus columns'>INJ</div>";
						}
					
					$lineuptable .="</div>";
					
				}
				
				elseif($k>99){
					
					$lineuptable .="
						<div class='lineupoutrow row is-collapse-child align-middle'>
							<div class='lineuprank large-1 medium-1 small-2 columns'>".$surfers[$sid]['rank']."</div>
							
							<div class='imgcontainer shrink columns'>
								<div class='lineupimg' style='background: url(img/".$surfers[$sid]['img'].");
														background-size: 75px 60px;
														background-repeat: no-repeat;
														background-position: center;'>
									</div>
								</div>
								
								<div class='lineupname large-3 columns'>".$surfers[$sid]['name']."</div>
								
								<div class='lineupreplacement large-3 columns'>
											<div class='lineupreplacementlabel'>Replaced by</div>
											<div class='lineupreplacementname'>".$surfers[$injuries[$sid]]['name']."</div>
								</div>
								
								<div class='imgcontainer shrink columns'>
									<div class='lineupreplacementimg hide-for-small-only columns'
											style='background: url(img/".$surfers[$injuries[$sid]]['img'].");
														 background-size: 57px 47px;
														 background-repeat: no-repeat;
														 background-position: center;'> 
									</div>
								</div>
								
							</div>";
						
				}
					
			}
			
			return $lineuptable;
		
		}
		
		public function prepareWildcardDisplay($available_wildcards,$wildcards,$surfers,$events,$league){
			
			$eid = $events['current'];
			$lid = $league['id'];
			
			if(!empty($available_wildcards[1])){
				
				$toreturn.= "<div class='availimmediate'>";
				$toreturn.= "<div class='availsectiontitle'>Your Replacement Wildcards</div>";
				
				foreach($available_wildcards[1] as $k=>$wid){
				
						$toreturn.= "
												<div class='availrow row is-collapse-child align-middle'>
												
												<div class='imgcontainer hide-for-small-only shrink columns'>
													<div class='availimg'
															style='background: url(img/".$surfers[$wid]['img'].");
																		background-size: 57px 47px;
																		background-repeat: no-repeat;
																		background-position: center;'>
													</div>
												</div>
											
												<div class='availname large-4 medium-4 small-5 columns'>
													".$surfers[$wid]['name']."
												</div>
											
												<div class='requestreplacing large-4 medium-4 small-4 columns'>
													<div class='replacinglabel'>Replacing</div>
													<div class='replacingname'>
														".$surfers[$wildcards[$wid]['status']]['name'].
													"</div>
												</div>
												
												<div class='imgcontainer hide-for-small-only shrink columns'>
													<div class='availimg'
															style='background: url(img/".$surfers[$wildcards[$wid]['status']]['img'].");
																		background-size: 57px 47px;
																		background-repeat: no-repeat;
																		background-position: center;'>
													</div>
												</div>
											
												<div class='requestbtn columns'>
													<button class='button addimmediatebtn' 
																	type='submit' id='addinstant.$eid.$lid.".$wildcards[$wid]['status'].".$wid'>
														Add ".$surfers[$wid]['aka']."
													</button>
												</div>
											
												</div>";
												
				}
				
				$toreturn.= "</div>";
				
			}
			
			if(!empty($available_wildcards[2])){
				
				$toreturn.= "<div class='availguests'>";
				$toreturn.= "<div class='availsectiontitle'>Wildcards</div>";
				
				foreach($available_wildcards[2] as $k=>$wid){
					
					$toreturn.= "
						<div class='availrow row is-collapse-child align-middle'>
							
							<div class='imgcontainer hide-for-small-only shrink columns'>
								<div class='availimg'
											style='background: url(img/".$surfers[$wid]['img'].");
														background-size: 57px 47px;
														background-repeat: no-repeat;
														background-position: center;'>
								</div>
							</div>
						
							<div class='availname shrinkcolumns'>
								".$surfers[$wid]['name']."
							</div>
							
							<div class='requestbtn columns end'>
								<button class='button requestwcbtn' 
												type='submit' id='requestguest.$eid.$lid.$wid'>
									Request ".$surfers[$wid]['aka']."
								</button>
							</div>
							
						</div>
					";
					
				}
				
				$toreturn.= "</div>";
				
			}
			
			if(!empty($available_wildcards[3])){
				
				$toreturn.= "<div class='availothers'>";
				$toreturn.= "<div class='availsectiontitle'>Other Replacement Wildcards</div>";
				
				foreach($available_wildcards[3] as $k=>$wid){
					$toreturn.= "
						<div class='availrow row is-collapse-child align-middle'>
							
							<div class='imgcontainer hide-for-small-only'>
								<div class='availimg'
										style='background: url(img/".$surfers[$wid]['img'].");
													background-size: 57px 47px;
													background-repeat: no-repeat;
													background-position: center;'>
								</div>
							</div>
							
							<div class='availname large-4 medium-4 small-5 columns'>
								".$surfers[$wid]['name']."
							</div>
							
							<div class='requestreplacing large-4 medium-4 small-4 columns'>
								<div class='replacinglabel'>Replacing</div>
								<div class='replacingname'>
									".$surfers[$wildcards[$wid]['status']]['name'].
								"</div>
							</div>
								
								<div class='imgcontainer hide-for-small-only'>
									<div class='availimg'
												style='background: url(img/".$surfers[$wildcards[$wid]['status']]['img'].");
															background-size: 57px 47px;
															background-repeat: no-repeat;
															background-position: center;'>
									</div>
								</div>
							
							<div class='requestbtn columns'>
								<button class='button requestwcbtn' 
												type='submit' id='requestrepl.$eid.$lid.$wid'>
									Request ".$surfers[$wid]['aka']."
								</button>
							</div>
								
					</div>";
				}
				
				$toreturn.= "</div>";
				
			}

			return $toreturn;
		}
		
		public function prepareRequestsDisplay($league,$events,$requests,$wildcards,$surfers){
			
			$eid = $events['current'];
			$lid = $league['id'];
			
			$toreturn.= "<div class='availimmediate'>";
			$toreturn.= "<div class='availsectiontitle'>Your Wildcard Requests</div>";
			
			foreach($requests as $k=>$v){
				$priority = $v['priority'];
				$toreturn.= "<div class='availrow row is-collapse-child align-middle requestrow' id='switchreq.$eid.$lid.$priority.$k'>";
				$toreturn.= "<div class='small-1 columns requestpriority'>$priority</div>";
				$toreturn.= "<div class='shrink columns requestname'>".$surfers[$k]['name']."</div>";
				$toreturn.= "<div class='columns removereqbtn'>
											<button class='button removerequestbtn' id='removerequest.$eid.$lid.$priority.$k'>x</button>
										</div>";
				$toreturn.= "</div>";
			}
			
			$toreturn.= "</div></div>";
			
			return $toreturn;
			
		}
		
		public function prepareReportDisplay($user_id,$events,$league,$requests,$surfers){
			
			$eid = $events['current'];
			$lid = $league['id'];
			
			
			
			if(!empty($requests)){
				$toreturn.= "<div class='requestreport'>";
				$toreturn.= "<div class='availsectiontitle'>Your Wildcard Requests</div>";
				foreach($requests as $k=>$v){
					$priority = $v['priority'];
					$approved = $v['approved'];
				
					if($approved==1){
						$toreturn.= "<div class='availrow row is-collapse-child align-middle requestapproved'>";
						$toreturn.= "<div class='small-1 columns'> </div>";
						$toreturn.= "<div class='small-4 columns requestname'>".$surfers[$k]['name']."</div>";
						$toreturn.= "<div class='columns requestresult'>Approved</div>";
						$toreturn.= "</div>";	
					}
				
					elseif($approved==2){
						$toreturn.= "<div class='availrow row is-collapse-child align-middle requestdenied'>";
						$toreturn.= "<div class='small-1 columns'> </div>";
						$toreturn.= "<div class='small-4 columns requestname'>".$surfers[$k]['name']."</div>";
						$toreturn.= "<div class='columns requestresult'>Denied</div>";
						$toreturn.= "</div>";	
					}
				
				}
			}
			
			$toreturn.= "<div class='row column requestreport'>";
			$toreturn.= "<a href='#' class='requestreportlink' id='viewreport.$eid.$lid'>View Wildcard Request Report</a>";
			$toreturn.= "</div>";
			
			$toreturn.= "</div></div>";
			
			return $toreturn;
	
		}
		
		public function getRoundTitle($activeround){
			
			if($activeround<=5){
				$roundname = "<div class='row column align-center roundtitle'>Round $activeround</div>";
			}
			elseif($activeround==6){
				$roundname = "<div class='row column align-center roundtitle'>Quarterfinals</div>";
			}
			elseif($activeround==7){
				$roundname = "<div class='row column align-center roundtitle'>Semifinals</div>";
			}
			elseif($activeround==8){
				$roundname = "<div class='row column align-center roundtitle'>Final</div>";
			}
			
			return $roundname;
			
		}
		
		public function showLeagueTitle($league){
			
			$display = "<div class='row align-center align-middle openleaguetitle'>
										<div class='small-12 columns'>
											".$league['name']."
										</div>
									</div>";
			
			return $display;
			
		}
		
		public function showRunningStage(){
			
			//construct FSBasics
			$fsbasics = new FSBasics();
			$fsevent = new FSEvent();
			$fsscores = new FSScores();
			
			//get user data
			if(!empty($_SESSION['user_id'])){$user_id = $_SESSION['user_id'];}
			if(!empty($_SESSION['user_name'])){$user_name = $_SESSION['user_name'];}
			if(!empty($_SESSION['user_team'])){$user_team = $_SESSION['user_team'];}
			
			$league = $fsbasics->getUsersLeague($user_id);
			
			$league_id = $league['id'];
			
			//get running event
			$events = $fsbasics->getAllEvents();
			$event_id = $events['current'];
		
			if(!empty($event_id)){
				
				$roundheatsandsurfers = $fsevent->getAllEventRounds($event_id);
				$heats	 = $roundheatsandsurfers;
				$surfers = $fsbasics->getSurfers();
				
				$currentround = $fsevent->getCurrentRound($heats);
				
				$leaguepicks = $fsevent->getAllLeagueLineups($event_id,$league_id);//includes users, all picks by users, active and benched
				
				if(!empty($currentround['round'])){
					
					$roundtitle = $this->getRoundTitle($currentround['round']);
					
					$thisround = $fsevent->displayCurrentRound($user_id,$heats,$currentround,$leaguepicks,$surfers);
					
					if($currentround['round']>1){$pastscores = $fsevent->displayPrevRoundScores($user_id,$event_id,$leaguepicks,$surfers);}
					
					return $roundtitle.$thisround.$pastscores;
					
				}else{
					
					$pastround['round'] = $fsevent->getPastRound($heats);
					
					$roundtitle = $this->getRoundTitle($pastround['round']);
					
					$thisround = $fsevent->displayCurrentRound($user_id,$heats,$pastround,$leaguepicks,$surfers);
				
					if($pastround['round']>1){$pastscores = $fsevent->displayPrevRoundScores($user_id,$event_id,$leaguepicks,$surfers);}
					
					return $roundtitle.$thisround.$pastscores;
				}
				
				
				
			}
			
			elseif(empty($event_id)){
				
				//$nextevent = $fsbasics->getNextEvent();
				
				$menu = "<div class='row align-center leaguemenu'>
									<div class='large-2 columns goToTeam'>Team</div>
									<div class='large-2 columns goToLeaderboard'>Leaderboard</div>
								</div>";
				
				//$team = $fsbasics->getUsersEventLineup($user_id,$league,$nextevent);
								
				$leaderboard = $fsscores->getLeagueLeaderboard($user_id,$league);
				
				return $menu.$leaderboard;
				
			}
			
			
			
			
		}
		
}//class Login
