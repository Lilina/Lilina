
function resize_faux_frame() {
	$('#main').height( ($(window).height() - 31));
}

$(document).ready(function() {
	$(window).resize(resize_faux_frame);
	resize_faux_frame();
});