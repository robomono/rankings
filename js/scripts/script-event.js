function checkLogin(){
	
	$.ajax({
	  type: "POST",
	  data: "checkLogin",
	  url: "classes/fsTrafficHandler.php",
	  dataType: "html",
	  async: false,
		success: function(data){if(data=="true"){showEvent();}}
	});
	
}

function showEvent(){
	
  $.ajax({
    type: "POST",
    data: "eventTraffic",
    url: "classes/fsTrafficHandler.php",
    dataType: "html",
    async: false,
    success: function(data){
			
			$('.maincontent').append(data);
			$(document).foundation();
		}
 	}); 
	
}

$(document).ready(function(){
	
	proceedflag = checkLogin();
	
});

