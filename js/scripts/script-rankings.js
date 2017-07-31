function getWslRankings(){
	
	$.ajax({
	  type: "POST",
	  data: "getWslRankings",
	  url: "classes/fsTrafficHandler.php",
	  dataType: "html",
	  async: false,
		success: function(data){
			
			$(".testmessage").html(data);
						
		}
	});
	
}

function checkLogin(){
	
	$.ajax({
	  type: "POST",
	  data: "checkLogin",
	  url: "classes/fsTrafficHandler.php",
	  dataType: "json",
	  async: false,
		success: function(data){
			if (data.success){
				//getHomeRedirect();
				alert("y");}
			else{$('.maincontent').load('views/loginform.html');}
		}
	});
	
}



$(document).ready(function(){
	
	//checkLogin();
	getWslRankings();
	
	
});


