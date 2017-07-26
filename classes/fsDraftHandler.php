<?php
//require_once 'adminbasics.php';
require_once 'fsTraffic.php';
require_once 'fslineup.php';
require_once 'fsdraft.php';
session_start();


if(isset($_POST['startDraft'])){
		//
    $fsdraft = new FSDraft();    
		$return = $fsdraft->startDraft();
		echo $return;
}

if(isset($_POST['showDraft'])){
		//
    $fsdraft = new FSDraft();    
		$return = $fsdraft->showDraft();
		echo $return;
}

if(isset($_POST['makePick'])){
		//
    $fsdraft = new FSDraft();    
		$return = $fsdraft->makePick();
		echo $return;
}

?>