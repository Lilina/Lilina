$(document).ready(function () {
	$('.item').addClass('collapsed').click(function (e) {
		if($(e.target).is('a')) return;
		$(this).toggleClass('collapsed');
	});
	$('.item .title-bar').css('cursor', 'pointer');
});