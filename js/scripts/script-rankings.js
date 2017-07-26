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


$(document).ready(function(){
	
	getWslRankings();
	
});


