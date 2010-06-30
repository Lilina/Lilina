Raincoat = {};

Raincoat.init = function () {
	$('.item').addClass('collapsed');
	$('.item .title-bar').click(function (e) {
		if($(e.target).is('a')) return;
		$('.item.selected').removeClass('selected');
		$(this).parent().toggleClass('collapsed').addClass('selected');
	});

	$('.item .title-bar').css('cursor', 'pointer');

	$(document).bind('keydown', Raincoat.keyboardWatcher);
};

Raincoat.message = function (msg) {
	content = $('<div id="message"><p></p></div>');
	$('p', content).text(msg);
	$('body').append(content);
	window.setTimeout(Raincoat.removeMessage, 5000);
};
Raincoat.removeMessage = function () {
	$('#message').remove();
};

Raincoat.keyboardWatcher = function (e) {
	if (e.target)
		element = e.target;
	else if (e.srcElement)
		element = e.srcElement;

	if (element.nodeType==3)
		element = element.parentNode;

	if ( e.ctrlKey == true || e.altKey == true || e.metaKey == true )
		return;

	var keyCode = (e.keyCode) ? e.keyCode : e.which;

	if (keyCode && (keyCode != 27 && (element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') ) )
		return;

	switch(keyCode) {
		//  "j" key
		case 74:
			current = $('.item.selected');
			next = current.next();
			if(next != undefined) {
				current.removeClass('selected');
				next.addClass('selected');
			}
			else {
				Raincoat.message('No more items');
			}
			break;
		//  "k" key
		case 75:
			current = $('.item.selected');
			prev = current.prev();
			if(prev != undefined) {
				current.removeClass('selected');
				prev.addClass('selected');
			}
			else {
				Raincoat.message('No more items');
			}
			break;
		// "v" key
		case 86:
			var newWindow = window.open($('.item.selected .read a').attr('href'));
			if(!newWindow)
				alert('It looks like a popup blocker is preventing Lilina from opening this page. If you have a popup blocker, try disabling it for this page.');
			break;
		// "o" key
		case 79:
		case 13:
			$('.item.selected').toggleClass('collapsed');
			break;
	}
};

$(document).ready(Raincoat.init);