
function resize_faux_frame() {
	$('#main').height( ($(window).height() - 51));
}

$(document).ready(function() {
	$(window).resize(resize_faux_frame);
	resize_faux_frame();
});