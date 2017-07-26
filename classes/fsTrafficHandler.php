<?php
//require_once 'adminbasics.php';
require_once 'fsLogin.php';
require_once 'fsTraffic.php';
require_once 'fsevent.php';
require_once 'fsWsl.php';
session_start();

if(isset($_POST['checkLogin'])){
//find out if user is logged in - returns 1 for yes, 0 for no
    $fslogin = new Login();
    echo json_encode($fslogin->isUserLoggedIn());
}

if(isset($_POST['loginArray'])){
		//log in
    $fslogin = new Login();
    $result = $fslogin->doLogin();
		echo $result;
}

if(isset( $_POST['checkLeagueStatus'] )) {
//find out what page to redirect the user to 1-for league, 2-for event, 3-for user details form
    $fstraffic = new Traffic();
    $result = $fstraffic->getLeagueAndEventStatus();
		echo $result;
}	

if(isset($_POST['doLogout'])){
//find out if user is logged in - returns 1 for yes, 0 for no
    $fslogin = new Login();
    $fslogin->doLogout();
		echo json_encode(true);
}

if(isset($_POST['getLeaguePage'])){
		//get the league status and what should be displayed
    $fstraffic = new Traffic();
    $result = $fstraffic->getLeaguePage();
		echo $result;
}

if(isset($_POST['eventTraffic'])){
		//get the league status and what should be displayed
    $fstraffic = new Traffic();
    $result = $fstraffic->showRunningStage();
		echo $result;
}

if(isset($_POST['passwordData'])){
		//log in
    $fslogin = new Login();
    $result = $fslogin->registerPassword();
		echo $result;
}	
	
if(isset($_POST['teamData'])){
		//log in
    $fslogin = new Login();
    $result = $fslogin->registerTeam();
		echo $result;
}

if(isset($_POST['getWslRankings'])){
	$fswsl = new WSL();
	$result = $fswsl->getWslRankings();
	echo $result;
}		
	
	
	
?>