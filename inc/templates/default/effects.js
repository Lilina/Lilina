/* JQuery Stuff! */
$(document).ready(function() {
	$(".river-page .title").next().next().hide().parent().addClass("c1").removeClass("c2");
	$(".river-page h1").css({cursor:"pointer"}).click(function(){
		$(this).next().slideToggle();
	});
	$(".river-page .title").click(function(){
		$(this).next().next().slideToggle().parent().toggleClass("c1").toggleClass("c2");
		return false;
	});
	$(".river-page #expandall").css({cursor:"pointer"}).click(function(){
		$(".title").next().next().slideDown().parent().removeClass("c1").addClass("c2");
		return false;
	});
	$(".river-page #collapseall").css({cursor:"pointer"}).click(function(){
		$(".title").next().next().slideUp().parent().addClass("c1").removeClass("c2");
		return false;
	});
	$(".river-page #removedates").css({cursor:"pointer"}).click(function(){
		$("h1").slideUp().next().slideDown();
		return false;
	});
	$(".river-page #viewallitems").click(function(){
		$("#main").children().toggle().parent().load("index.php?hours=-1 #main");
		$(".title").next().next().slideUp().parent().addClass("c1").removeClass("c2");
		$(".river-page .title").click(function(){
			$(this).next().next().slideToggle().parent().toggleClass("c1").toggleClass("c2");
			return false;
		});
		return false;
	});
	$(".river-page .hide_feed").click(function(){
		whichFeed = $(this).children("span").attr("class");
		$("." + whichFeed).slideUp();
		return false;
	});
});