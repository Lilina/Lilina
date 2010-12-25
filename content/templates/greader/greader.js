Raincoat = {};
Raincoat.messageTimeout = 0;

Raincoat.init = function () {
	$('.item').addClass('collapsed');
	$('.item .title-bar').click(function (e) {
		if($(e.target).is('a')) return;
		parent = $(this).parent();
		if(Raincoat.isCollapsed(parent)) {
			Raincoat.expand(parent);
		}
		else {
			Raincoat.collapse();
		}
		return false;
	});
	$('.item .action-bar .service-inline').fancybox({
		'width'				: '75%',
		'height'			: '75%',
        'autoScale'     	: false,
        'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe'
	});

	$('.item .title-bar').css('cursor', 'pointer');
	Raincoat.resizer();

	//$(window).bind('keydown', Raincoat.keyboardWatcher);
	$(window).bind('keypress', Raincoat.keyboardWatcher);
	$(window).bind('resize', Raincoat.resizer);
};

Raincoat.message = function (msg, type) {
	if($('#message').length !== 0) {
		$('#message p').text(msg);
		var newMargin = (parseInt($('#message').css('width')) / 2);
		$('#message').addClass(type + '-message').css('margin-left', '-' + newMargin + 'px');
		window.clearTimeout(Raincoat.messageTimeout);
		if(type == 'loading')
			return;
		Raincoat.messageTimeout = window.setTimeout(Raincoat.removeMessage, 2000);
		return;
	}
	content = $('<div id="message" style="display: none"><div class="inner"><p></p></div</div>');
	$('p', content).text(msg);
	var newMargin = (parseInt($(content).css('width')) / 2);
	$(content).addClass(type + '-message').css('margin-left', '-' + newMargin + 'px');
	$('body').append(content);
	$('#message').fadeIn(100);

	if (type == 'loading')
		return;

	// Prepare the different ways it can be removed
	Raincoat.messageTimeout = window.setTimeout(Raincoat.removeMessage, 2000);
	$(window).bind('mousemove', Raincoat.removeMessage);
	$(document).bind('keydown', Raincoat.removeMessage);
};
Raincoat.removeMessage = function () {
	// Remove ourself so we don't get called twice
	$(window).unbind('mousemove', Raincoat.removeMessage);
	$(document).unbind('keydown', Raincoat.removeMessage);
	if (Raincoat.messageTimeout) {
		window.clearTimeout(Raincoat.messageTimeout);
		Raincoat.messageTimeout = 0;
	}

	if ($('#message').length === 0)
		return;

	$('#message').fadeOut(1000, function() {
		$('#message').remove();
	})
};

Raincoat.expand = function (elem) {
	Raincoat.collapse();
	Raincoat.select(elem);
	$(elem).toggleClass('collapsed').addClass('selected');
};

Raincoat.collapse = function () {
	$('.item:not(.collapsed)').addClass('collapsed');
};

Raincoat.isCollapsed = function (elem) {
	return $(elem).hasClass('collapsed');
};

Raincoat.select = function (elem) {
	Raincoat.deselect();
	$(elem).addClass('selected');
	//window.location.hash = '#' + $(elem).attr('id');
};

Raincoat.deselect = function () {
	$('.item.selected').removeClass('selected');
};

Raincoat.maybeScroll = function (elem, parent) {
	elem = $(elem);
	parent = $(parent);

	var pos = elem.position().top;
	var parentHeight = parent.innerHeight();
	var height = elem.outerHeight();
	if (pos < 0) {
		Raincoat.scrollToTop(elem, parent);
		return true;
	}
	else if ((pos + height) > parentHeight) {
		Raincoat.scrollToBottom(elem, parent);
		return true;
	}
	return false;
};

Raincoat.scrollToTop = function (elem, parent) {
	pos = $(parent).scrollTop() + $(elem).position().top;
	$(parent).scrollTop(pos);
};

Raincoat.scrollToBottom = function (elem, parent) {
	var pos = $(elem).position().top;
	var parentHeight = $(parent).innerHeight();
	var height = $(elem).outerHeight();
	pos = $(parent).scrollTop() + (pos - parentHeight) + height;
	$(parent).scrollTop(pos);
};

Raincoat.keyboardWatcher = function (e) {
	if(!e)
		e = window.event;

	if (e.target)
		element = e.target;
	else if (e.srcElement)
		element = e.srcElement;

	if (element.nodeType==3)
		element = element.parentNode;

	if ( e.ctrlKey == true || e.altKey == true || e.metaKey == true )
		return;

	var keyCode = e.which;

	if (keyCode && (keyCode != 27 && (element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') ) )
		return;
	
	var current = $('.item.selected');
	switch(String.fromCharCode(keyCode)) {
		//  "j" key
		case "j":
			var next = current.next();
			if(next != undefined && next.length !== 0) {
				Raincoat.expand(next);
				Raincoat.scrollToTop(next, $('#items'));
			}
			else {
				Raincoat.message('No more items', 'info');
			}
			break;
		//  "k" key
		case "k":
			var prev = current.prev();
			if(prev != undefined && prev.length !== 0) {
				Raincoat.expand(prev);
				Raincoat.scrollToTop(prev, $('#items'));
			}
			else {
				Raincoat.message('No more items', 'info');
			}
			break;
		case "n":
			var next = current.next();
			if(next != undefined && next.length !== 0) {
				Raincoat.select(next);
				Raincoat.maybeScroll(next, $('#items'));
			}
			else {
				Raincoat.message('No more items', 'info');
			}
			break;
		case "p":
			var prev = current.prev();
			if(prev != undefined && prev.length !== 0) {
				Raincoat.select(prev);
				Raincoat.maybeScroll(prev, $('#items'));
			}
			else {
				Raincoat.message('No more items', 'info');
			}
			break;
		// "v" key
		case "v":
			var newWindow = window.open($('.item.selected .read a').attr('href'));
			if(!newWindow)
				Raincoat.message('Disable your popup blocker to view links.', 'error');
			break;
		// "o" key
		case "o":
			if (Raincoat.isCollapsed(current)) {
				Raincoat.expand(current);
				Raincoat.scrollToTop(current, $('#items'));
			}
			else {
				Raincoat.collapse();
			}
			break;
		case "?":
			Raincoat.showHelp();
			break;
		default:
			return true;
	}
	return false;
};
Raincoat.showHelp = function () {
	$.fancybox({
		'href': Raincoat.baseURL + '?method=raincoat_help',
		'width': '75%',
		'height': '75%',
	    'autoScale': false,
	    'transitionIn': 'none',
		'transitionOut': 'none',
		'type': 'iframe'
	});
	
};
Raincoat.resizer = function () {
	var height = window.innerHeight - $('#items').offset().top;
	$('#items').height(height);
};

$(document).ready(Raincoat.init);