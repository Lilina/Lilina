/* JQuery Stuff! */
$(document).ready(function() {
	$('.river-page .excerpt').hide().parent().addClass('c1').removeClass('c2');
	$('.river-page h1')
		.css( {cursor:'pointer'} )
		.click(function() {
			$(this).next().slideToggle();
	});
	$('.river-page .title').click(function() {
		
		$(this).siblings('.excerpt').slideToggle();
		$(this).parent().toggleClass('c1').toggleClass('c2');
		
		return false;
	});
	$('.river-page #expandall').css({cursor:'pointer'}).click(function() {
		
		$('.excerpt').slideDown();
		$('.item').removeClass('c2').addClass('c1');
		
		return false;
	});
	$('.river-page #collapseall').css({cursor:'pointer'}).click(function() {
		
		$('.excerpt').slideUp();
		$('.item').addClass('c1').removeClass('c2');
		
		return false;
	});
	$('.river-page #viewallitems').click(function() {
		$('#main').load('index.php?hours=-1 #main').children().toggle();
		
		$('.title').siblings('.excerpt').slideUp().parent().addClass('c1').removeClass('c2');
		
		$('.river-page .title').click(function() {
			$(this).siblings().slideToggle().parent().toggleClass('c1').toggleClass('c2');
			return false;
		});
		
		return false;
	});
});