function getLeagueStatus(){
	
	$.ajax({
	  type: "POST",
	  data: "getLeaguePage",
	  url: "classes/fsTrafficHandler.php",
	  dataType: "html",
	  async: false,
		success: function(data){
			
			if(data=="setnameandteam"){
				$('.maincontent').load('views/userteamform.html');
			}else{
				$('.maincontent').html(data);
				$(document).foundation();
				$('.isHidden').hide();
			}
						
		}
	});
	
}

function doLogout(){
	
	$.ajax({
	  type: "POST",
	  data: "doLogout",
	  url: "classes/fsTrafficHandler.php",
	  dataType: "json",
	  async: false,
		success: function(data){window.location="index.html";}
	});
	
}

$(document).ready(function(){
	
	getLeagueStatus();
	
	if (sessionStorage.scrollTop != "undefined") {
	    $(window).scrollTop(sessionStorage.scrollTop);
	  }//returns to a scroll position if defined
	
});


/*NavMenu - Switch windows*/
$(document).on('click', '.navitem', function(e) {
	
	e.preventDefault();
	
	instructions = $(this).attr('id');
	
	if(instructions == "showLineup"){
		$(".leaderboardtable").hide();
		$(".userlineup").show();
		$("#showLineup").addClass("selected");
		$("#showLeaderboard").removeClass("selected");
	}
 	
	if(instructions == "showLeaderboard"){
		$(".leaderboardtable").show();
		$(".userlineup").hide();
		$("#showLineup").removeClass("selected");
		$("#showLeaderboard").addClass("selected");
	}
	
	if(instructions == "showAvails"){
		$(".availsurferstab").show();
		$(".openleaguetab").hide();
		$("#showMembers").removeClass("selected");
		$("#showAvails").addClass("selected");
	}
	
	if(instructions == "showMembers"){
		$(".openleaguetab").show();
		$(".availsurferstab").hide();
		$("#showAvails").removeClass("selected");
		$("#showMembers").addClass("selected");
	}
 
 return false;
 
});

$(document).on("click",".menuLogout",function(e){e.preventDefault();doLogout();});

$(document).on('click', '.addimmediatebtn', function(e) {
	
	e.preventDefault();
	
	instructions = $(this).attr('id');
		
  $.ajax({
    type: "POST",
    data: {addInstantWc:instructions},
    url: "classes/fsLineupHandler.php",
    dataType: "html",
    async: false,
    success: function(data){sessionStorage.scrollTop = $(this).scrollTop();location.reload();}
  }); 
 
 return false;
 
});

$(document).on('click', '.removeinstantbtn', function(e) {
	
	e.preventDefault();
	
	instructions = $(this).attr('id');
		
  $.ajax({
    type: "POST",
    data: {removeInstantWc:instructions},
    url: "classes/fsLineupHandler.php",
    dataType: "html",
    async: false,
    success: function(data){sessionStorage.scrollTop = $(this).scrollTop();location.reload();}
  }); 
 
 return false;
 
});

$(document).on('click', '.requestwcbtn', function(e) {
	
	e.preventDefault();
	
	instructions = $(this).attr('id');

  $.ajax({
    type: "POST",
    data: {requestWc:instructions},
    url: "classes/fsLineupHandler.php",
    dataType: "html",
    async: false,
    success: function(data){sessionStorage.scrollTop = $(this).scrollTop();location.reload();}
  }); 
	
	
 return false;
 
});

$(document).on('click', '.removerequestbtn', function(e) {
	
	e.preventDefault();
	
	instructions = $(this).attr('id');
	
  $.ajax({
    type: "POST",
    data: {removeRequest:instructions},
    url: "classes/fsLineupHandler.php",
    dataType: "html",
    async: false,
    success: function(data){sessionStorage.scrollTop = $(this).scrollTop();location.reload();}
  }); 
	
 return false;
 
});

$(document).on('click', '.requestrow', function(e) {
	
	e.preventDefault();
	
	req_data = $(this).attr('id');
	
	if(typeof req_1 === 'undefined'){
		
		req_1 = req_data;
		
	}else{
		//req_1 exists
		
		req_2 = req_data;
		
		var requests = {0:req_1,1:req_2,}
		
	  $.ajax({
	    type: "POST",
	    data: {switchRequest:requests},
	    url: "classes/fsLineupHandler.php",
	    dataType: "html",
	    async: false,
	    success: function(data){sessionStorage.scrollTop = $(this).scrollTop();location.reload();}
	  }); 
		
	}
 
	
 return false;
 
});

$(document).on('click', '.lineuprow', function(e) {
	
	e.preventDefault();
	
	req_data = $(this).attr('id');
	
	if(typeof req_1 === 'undefined'){
		
		req_1 = req_data;
		$(this).addClass('lineupselected');
		
	}else{
		//req_1 exists
		
		req_2 = req_data;
		
		var requests = {0:req_1,1:req_2,}
		
	  $.ajax({
	    type: "POST",
	    data: {switchLineup:requests},
	    url: "classes/fsLineupHandler.php",
	    dataType: "html",
	    async: false,
	    success: function(data){sessionStorage.scrollTop = $(this).scrollTop();location.reload();}
	  }); 
		
	}
	
 return false;
 
});

$(document).on('click', '.requestreportlink', function(e) {
	
	e.preventDefault();
	
	$instructions = $(this).attr('id');
	
  openNav();
	
 return false;
 
});


/* Wildcard Report - Open when someone clicks on the span element */
function openNav() {
    document.getElementById("myNav").style.width = "100%";
}

/* Wildcard Report - Close when someone clicks on the "x" symbol inside the overlay */
function closeNav() {
    document.getElementById("myNav").style.width = "0%";
}


/*Display scores details on leaderboard click */
$(document).on('click', '.shorteventscore', function() {
	
	var toshow = 'for' + this.id.substring(4);
	
	$( "."+toshow ).toggle();
	
 return false;
});


$(".maincontent").on("click","#addteam",function(e){
	
	e.preventDefault();
	
	formdata = $("#addnameandteam").serialize();
	
  $.ajax({
    type: "POST",
    data: {teamData:formdata},
    url: "classes/fsTrafficHandler.php",
    dataType: "html",
    async: false,
    success: function(data){
    	
			if(data=="success"){window.location="league.html";}
			else if(data=="userexists"){$( ".messages" ).load( "views/messages.html #userexists" );}
			else if(data=="teamexists"){$( ".messages" ).load( "views/messages.html #teamexists" );}
			else if(data=="shortuser"){$( ".messages" ).load( "views/messages.html #shortuser" );}
			else if(data=="shortteam"){$( ".messages" ).load( "views/messages.html #shortteam" );}
			else if(data=="longuser"){$( ".messages" ).load( "views/messages.html #longuser" );}
			else if(data=="longteam"){$( ".messages" ).load( "views/messages.html #longteam" );}
			else if(data=="teamexists"){$( ".messages" ).load( "views/messages.html #teamexists" );}
			else if(data=="funnychars"){$( ".messages" ).load( "views/messages.html #funnychars" );}
			else{$('.extracontent_top').html(data);}
			
    }
		
  }); 
 
 return false;
	
});

$(document).on('click', '.startdraftbutton', function(e) {
	
	e.preventDefault();
	
	$instructions = $(this).attr('id');
	
  $.ajax({
    type: "POST",
    data: {startDraft:$instructions},
    url: "classes/fsDraftHandler.php",
    dataType: "html",
    async: false,
    success: function(data){sessionStorage.scrollTop = $(this).scrollTop();location.reload();}
  });
	
 return false;
 
});

$(document).on('click', '.enterdraft', function() {
	
	$league_id = $(this).attr('id');
	
	window.open('draft.html#'+$league_id , 'newwindow', config=
	     			'titlebar=no, toolbar=no, menubar=no, scrollbars=yes, location=no, '
	    			+ 'directories=no, status=no');
	
 return false;
});




/*
$('.messagestripbar').html(data);
*/