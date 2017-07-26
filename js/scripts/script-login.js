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

function getHomeRedirect(){
	
  $.ajax({
    type: "POST",
    data: "checkLeagueStatus",
    url: "classes/fsTrafficHandler.php",
    dataType: "html",
    async: false,
    success: function(data){
    	
			if(data==1){window.location="league.html";}
			else if(data==2){window.location="event.html";}
			
    }
 	}); 
	
}

function doLogin(){
	
	var logindata = {0:$("#login_input_username").val(),1:$("#login_input_password").val(),}
	
  $.ajax({
    type: "POST",
    data: {loginArray:logindata},
    url: "classes/fsTrafficHandler.php",
    dataType: "html",
    async: false,
    success: function(data) {
			
			if(data=="success"){getHomeRedirect();}
			
			else if(data=="activate"){

				
				var theuser = "Registration for <span id='activationemail'>"+$("#login_input_username").val()+"</span>";

				$('.extracontent_top').html(theuser);
				$('#loginformarea').load('views/passwordform.html');
				$('.messages').html('');
				$('#activatelink').html('');
				
				
			}
			
			else if(data=="user"){$( ".messages" ).load( "views/messages.html #wronguser" );}
			else if(data=="password"){$( ".messages" ).load( "views/messages.html #wrongpassword" );}
    }
		
  }); 

	return false; 
		
}

$(document).ready(function(){
	
	proceedflag = checkLogin();
	
});

$(".maincontent").on("click",".loginbutton",function(e){e.preventDefault();doLogin();});

$(".maincontent").on("click","#addpassword",function(e){
	
	e.preventDefault();
	
	formdata = $("#adduserpassword").serialize();
	
  $.ajax({
    type: "POST",
    data: {passwordData:formdata},
    url: "classes/fsTrafficHandler.php",
    dataType: "html",
    async: false,
    success: function(data){
    	
			if(data=="success"){window.location="league.html";}
			else if(data=="passmatch"){$( ".messages" ).load( "views/messages.html #passwordmatch" );}
			else if(data=="shortpass"){$( ".messages" ).load( "views/messages.html #shortpassword" );}
			else if(data=="unknownfail"){$( ".messages" ).load( "views/messages.html #unkmownfail" );}
			else{$('.extracontent_top').html(data);}
			
    }
		
  }); 
 
 return false;
	
});