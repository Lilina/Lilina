/* JQuery Stuff! */
$(document).ready(function() {
	$(".title").next().next().hide().parent().addClass("c1").removeClass("c2");
	$("h1").css({cursor:"pointer"}).click(function(){
		$(this).next().slideToggle();
	});
	$(".title").click(function(){
		$(this).next().next().slideToggle().parent().toggleClass("c1").toggleClass("c2");
		return false;
	});
	$("#expandall").css({cursor:"pointer"}).click(function(){
		$(".title").next().next().slideDown().parent().removeClass("c1").addClass("c2");
		return false;
	});
	$("#collapseall").css({cursor:"pointer"}).click(function(){
		$(".title").next().next().slideUp().parent().addClass("c1").removeClass("c2");
		return false;
	});
	$("#removedates").css({cursor:"pointer"}).click(function(){
		$("h1").slideUp().next().slideDown();
		return false;
	});
	$("#viewallitems").click(function(){
		$("#main").children().toggle().parent().load("index.php?hours=-1 #main");
		$(".title").next().next().slideUp().parent().addClass("c1").removeClass("c2");
		return false;
	});
});