<?php
//require_once 'adminbasics.php';
require_once 'fsTraffic.php';
require_once 'fslineup.php';
//session_start();


if(isset($_POST['addInstantWc'])){
		//
    $fslineup = new FSLineup();    
		$return = $fslineup->addInstantWc();
		echo $return;
}
	
if(isset($_POST['removeInstantWc'])){
		//
    $fslineup = new FSLineup();    
		$return = $fslineup->removeInstantWc();
		echo $return;
}	


if(isset($_POST['requestWc'])){
		//
    $fslineup = new FSLineup();    
		$return = $fslineup->requestWc();
		echo $return;
}	

if(isset($_POST['removeRequest'])){
		//
    $fslineup = new FSLineup();    
		$return = $fslineup->removeRequest();
		echo $return;
}		

if(isset($_POST['switchRequest'])){
		//
    $fslineup = new FSLineup();    
		$return = $fslineup->changeRequestOrder();
		echo $return;
}	

if(isset($_POST['switchLineup'])){
		//
    $fslineup = new FSLineup();    
		$return = $fslineup->changeLineupOrder();
		echo $return;
}	

if(isset($_POST['viewRequestReport'])){
		//
    $fslineup = new FSLineup();    
		$return = $fslineup->viewRequestReport();
		echo $return;
}	


?>