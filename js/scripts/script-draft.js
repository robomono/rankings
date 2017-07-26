function getDraftData(){
	
	$lid = window.location.hash;
	
	$.ajax({
	  type: "POST",
	  data: {showDraft:$lid},
	  url: "classes/fsDraftHandler.php",
	  dataType: "html",
	  async: false,
		success: function(data){
			if(data=="closewindow"){
				window.close();
			}else{
				$('.messagestripbar').html(data);}
			}
			
	});
	
	
}


$(document).ready(function(){
	
	getDraftData();
	
	setTimeout(function(){
	   window.location.reload(1);
	}, 30000);
	
	if (sessionStorage.scrollTop != "undefined") {
	    $(window).scrollTop(sessionStorage.scrollTop);
	  }//returns to a scroll position if defined
	
});

//makes order container same height as surfer list
$(document).ready(function () {
	draftheight = $('.draftsurferscontainer').height();
	$('.draftordercontainer').css('height',window.innerHeight);
});

//scrolls to next-pick row
$(document).ready( function(){
    var elem = $('.orderisnext');
		if(elem){var main = $(".draftordercontainer"),t = main.offset().top;main.scrollTop(elem.position().top - t - 50);}
});

//LIVE SEARCH - create caps-agnostic pseudos
$.expr[":"].contains = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});

$(document).on('keyup', '.search', function(e) {
	
	$('.draftsurfer').hide();
	var txt = $('.search').val();
	$(".draftsurfer").find(".draftsurfersname:contains('"+txt+"')").parent().show();
	
 return false;
 
});
//LIVE SEARCH - END

$(document).on('click', '.thinkpick', function(e) {
	
	e.preventDefault();
	
	$(this).removeClass('thinkpick');
	$(this).addClass('makepick');
	
	oldhtml = $(this).html();
	var newhtml = oldhtml.replace(/Pick/g, "Confirm");
	$(this).html(newhtml);
	
 return false;
 
});

$(document).on('click', '.makepick', function(e) {
	
	e.preventDefault();
	
	$instructions = $(this).attr('id');
	
  $.ajax({
    type: "POST",
    data: {makePick:$instructions},
    url: "classes/fsDraftHandler.php",
    dataType: "html",
    async: false,
    success: function(data){
			if(data=='closewindow'){window.close();}
			else{sessionStorage.scrollTop = $(this).scrollTop();location.reload();}
			}
  });
	
 return false;
 
});



/*
$('.messagestripbar').html(data);
*/