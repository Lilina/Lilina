Razor = {};
Razor.useFrame = false;
Razor.currentItem = false;
Razor.conditions = {};
Razor.init = function () {
	//RazorAPI.init();
	RazorUI.init();
};
Razor.selectItem = function (item) {
	Razor.currentItem = item;
	var loading = $('<div class="loading">Loading...</div>');
	$('#items-list li a.current').removeClass('current');
	$('#list-item-' + item).children('a').addClass('current');
	RazorUI.maybeScroll($("#list-item-" + item), $("#items-list"));
	$('#item-view').html(loading);
	LilinaAPI.call('items.get', {'id': item}, RazorUI.populateItemView);
};
Razor.selectNext = function () {
	var next = $('#items-list li:has(a.current)').next();
	if (next.length == 0 && !Razor.currentItem) {
		next = $('#items-list li:first');
	}
	else if (next.length == 0) {
		alert('No next item');
		return false;
	}

	if (next.attr('id') === 'load-more') {
		alert('loading more');
		RazorUI.loadMoreItems();
		return;
	}
	var id = $('a', next).data('item-id');
	Razor.selectItem(id);
};
Razor.selectPrevious = function () {
	var prev = $('#items-list li:has(a.current)').prev();
	if (prev.length == 0 && !Razor.currentItem) {
		prev = $('#items-list li:first');
	}
	else if (prev.length == 0) {
		alert('No previous item');
		return false;
	}
	var id = $('a', prev).data('item-id');
	Razor.selectItem(id);
};
Razor.api = function (method, conditions, callback) {
	$.extend(conditions, Razor.conditions);
	LilinaAPI.call(method, conditions, callback);
};


String.prototype.shorten = function(length) {
	if (this.length > length) {
		var shorterLength = length - 4;
		return this.substr(0, shorterLength) + "...";
	} else {
		return String(this);
	}
};

/* From GitHub's jquery.hotkeys.js */
(function ($) {
	$.hotkeys = function (c) {
		for (key in c) $.hotkey(key, c[key]);
		return this
	};
	$.hotkey = function (c, d) {
		c = $.hotkeys.special[c] == null ? c.charCodeAt(0) : $.hotkeys.special[c];
		$.hotkeys.cache[c] = d;
		return this
	};
	$.hotkeys.cache = {};
	$.hotkeys.special = {
		enter: 45,
		"?": 191,
		"/": 223,
		"\\": 252,
		"`": 224
	};
	if ($.browser.mozilla && navigator.userAgent.indexOf('Macintosh') != -1) $.hotkeys.special["?"] = 0
})(jQuery);
jQuery(document).ready(function (a) {
	$("a[hotkey]").each(function () {
		$.hotkey($(this).attr("hotkey"), $(this).attr("href"))
	});
	$(document).bind("keydown.hotkey", function (c) {
		if (!$(c.target).is(":input")) {
			if (c.ctrlKey || c.altKey || c.metaKey) return true;
			c = c.shiftKey ? c.keyCode : c.keyCode + 32;
			if (c = $.hotkeys.cache[c]) {
				$.isFunction(c) ? c.call(this) : (window.location = c);
				return false
			}
		}
	})
});

RazorUI = {};
RazorUI.itemCount = 40;
RazorUI.showing = 'full';
RazorUI.init = function () {
	$(window).resize(RazorUI.fitToWindow);
	RazorUI.fitToWindow();

	$('.relative').toRelativeTime();

	LilinaAPI.call('feeds.getList', {}, RazorUI.populateFeedList);
	// We'll fix this hardcoded limit later.
	Razor.api('items.getList', {"limit": RazorUI.itemCount}, RazorUI.initializeItemList);

	$('#items-list li a').live('click', RazorUI.handleItemClick);
	$('#sidebar .expandable > a .arrow')
		.live('click', function() {
			$(this).parent().blur();
			$(this).parent().parent().toggleClass('expanded').children('ul').toggle();
			if ($(this).parent().parent().hasClass('expanded')) {
				$(this).html('&#x25BC;');
			}
			else {
				$(this).html('&#x25B6;');
			}
			return false;
		})
		.parent().parent().children('ul')
			.hide();
	$('#help a').click(RazorUI.showHelp);
	$('#update a').click(function () {
		RazorUI.beginUpdate();
		return false;
	});
	$('#items-list').bind('initialized', function() {
		$('#load-more').click(RazorUI.loadMoreItems);
	});
	$('#updating').hide();
	$.hotkeys({
		"?": RazorUI.showHelp,
		"j": Razor.selectPrevious,
		"k": Razor.selectNext
	});
	$('#switcher-sidebar').click(function () {
		RazorUI.showing = 'sidebar';
		RazorUI.fitToWindow();
	});
	$('#switcher-items').click(function () {
		RazorUI.showing = 'items';
		RazorUI.fitToWindow();
	});
};
RazorUI.maybeScroll = function (elem, parent) {
	elem = $(elem);
	parent = $(parent);

	var pos = elem.position().top;
	var parentHeight = parent.innerHeight();
	var height = elem.outerHeight();
	if (pos < 0) {
		RazorUI.scrollToTop(elem, parent);
		return true;
	}
	else if ((pos + height) > parentHeight) {
		RazorUI.scrollToBottom(elem, parent);
		return true;
	}
	return false;
};
RazorUI.scrollToTop = function (elem, parent) {
	pos = $(parent).scrollTop() + $(elem).position().top;
	$(parent).animate({scrollTop: pos}, 200);
};
RazorUI.scrollToBottom = function (elem, parent) {
	var pos = $(elem).position().top;
	var parentHeight = $(parent).innerHeight();
	var height = $(elem).outerHeight();
	pos = $(parent).scrollTop() + (pos - parentHeight) + height;
	$(parent).animate({scrollTop: pos}, 200);
};
RazorUI.showHelp = function () {
	var loading = $('<div class="loading">Loading...</div>');
	$('#item-view').html(loading);
	LilinaAPI.call('razor.help', {}, RazorUI.populateItemView);
};
RazorUI.headerHeight = 59;
RazorUI.fitToWindow = function () {
	var normalMode = $(window).width() > 920;

	if (RazorUI.showing != 'full' && normalMode) {
		$('#sidebar').show();
		$('#items-list').show();
		RazorUI.showing = 'full';
	}
	else if (RazorUI.showing == 'full' && !normalMode) {
		RazorUI.showing = 'items';
	}

	if (RazorUI.showing == 'items') {
		$('#sidebar').hide();
		$('#items-list').show();
	}
	else if (RazorUI.showing == 'sidebar') {
		$('#items-list').hide();
		$('#sidebar').show();
	}

	if (normalMode) {
		$('#sidebar, #items-list, #item-view').css( {
			'height': $(window).height() - $('#items-list').position().top
		});
	}
	else {
		$('#sidebar, #items-list,').css( {
			'height': $(window).height() - ($('#items-list').position().top + $('#switcher').outerHeight())
		});
		$('#item-view').css( {
			'height': $(window).height() - $('#items-list').position().top
		});
	}
	$('#sidebar .item-list').css( {
		'height': $('#sidebar').height() - $('#sidebar .footer').outerHeight()
	});
	$('#items-list ol').css( {
		'height': $('#items-list').height() - $('#items-list .footer').outerHeight()
	});

	if (normalMode) {
		$('#item-view').css( {
			'width': $(window).width() - ($('#sidebar').outerWidth() + $('#items-list').outerWidth())
		});
	}
	else {
		$('#item-view').css( {
			'width': $(window).width() - $('#sidebar').outerWidth()
		});
	}
	var contentHeight = $("#item-view").innerHeight() - ($('#item-content').position().top + $('#item-services').outerHeight() + 20); //header + footer + item header + padding
	$('#item-content').css( {
		'height': contentHeight
	});
};
RazorUI.populateFeedList = function (list) {
	$('#feeds-list').empty();

	RazorUI.feeds = list;
	$.each(list, function (index, item) {
		var li = $('<li><a href="#"><img src="" /> <span /><span class="delete" /></a></li>');
		var a = $('a', li);

		a.data('feed-id', item.id).attr('title', item.name);
		$('span:not(.delete)', a).text( item.name.shorten(25) );
		if (item.icon === false) {
			$('img', li).attr('src', 'lilina-favicon.php?feed=' + item.id);
		}
		else {
			$('img', li).attr('src', item.icon);
		}
		$('.delete', a).addClass('delete').text('Delete');
		//a.append(span);
		$('#feeds-list').append(li);
	});
	$('#feeds-list').trigger('populated');
};
RazorUI.initializeItemList = function (list) {
	$('#items-list ol').empty();
	var li = $('<li id="load-more"><a href="#">Load More Items</a></li>');
	$('#items-list ol').append(li);
	RazorUI.populateItemList(list);
	$('#items-list').trigger('initialized');
};
RazorUI.populateItemList = function (list) {
	$.each(list, function (id, item) {
		var li = $('<li><a href="#"><span class="item-title" /> <span class="sep">from</span> <span class="item-source" /> <span class="sep">at</span> <span class="item-date" /></a></li>');
		var a = $('a', li);

		a.data('item-id', id).attr('title', item.title).attr('href', '#!/item/' + id);
		$('.item-title', li).html( item.title.shorten(40) );

		if(item.feed_id != undefined)
			var feed = RazorUI.feeds[item.feed_id];
		else
			var feed = {"name": "Razor", "url": "http://getlilina.org/"};

		$('.item-source', li).html(feed.name);

		var date = new Date(item.timestamp * 1000);
		$('.item-date', li).text(date.toUTCString()).toRelativeTime();

		li.attr('id', 'list-item-' + id);
		li.insertBefore('#load-more');
	});
	$('#items-list').trigger('populated');
};
RazorUI.loadMoreItems = function () {
	var oldCount = RazorUI.itemCount;
	RazorUI.itemCount += 20;
	Razor.api('items.getList', {"limit": 20, "start": oldCount}, RazorUI.populateItemList);

	return false;
};
RazorUI.populateItemView = function (item) {
	$('#item-view').empty();
	var basics = $('<div id="item"><div id="heading"><h2 class="item-title"><a /></h2><p class="item-meta"><span class="item-source">From <a /></span>. <span class="item-date">Posted <abbr /></span></p></div><div id="item-content" /></div>');
	if (Razor.useFrame) {
		basics = $('<div id="item"><div id="heading"><h2 class="item-title"><a /></h2><p class="item-meta"><span class="item-source">From <a /></span>. <span class="item-date">Posted <abbr /></span></p></div><iframe id="item-content" class="framed" /></div>');
		$('#item-content', basics).attr('src', item.permalink);
	}

	if(item.feed_id != undefined)
		var feed = RazorUI.feeds[item.feed_id];
	else
		var feed = {"name": "Razor", "url": "http://getlilina.org/"};

	var date = new Date(item.timestamp * 1000);

	$('.item-title a', basics).html(item.title).attr('href', item.permalink);
	$('.item-source a', basics).html(feed.name).attr('href', feed.url).addClass('external');
	$('.item-date abbr', basics).text(date.toUTCString()).attr('title', date.toUTCString()).toRelativeTime();
	if (!Razor.useFrame) {
		$('#item-content', basics).html(item.content);
	}

	if(item.services != undefined) {
		var item_footer = $('<div class="footer" id="item-services"><ul></ul></div>');
		$.each(item.services, function (index, service) {
			var service_item = $('<li><a></a></li>');
			$('a', service_item)
				.html(service.label)
				.addClass('type-' + service.type)
				.attr('href', service.action);
			if (service.type == 'inline') {
				$('a', service_item).fancybox({
					'transitionIn' : 'none',
					'transitionOut' : 'none',
					'type': 'iframe'
				});
			}
			$('ul', item_footer).append(service_item);
		});
		$(basics).append(item_footer);
	}

	$('#item-view').html(basics);
	$('#item-content').focus();
	RazorUI.fitToWindow();
	$('#item-view').trigger('populated');
};
RazorUI.handleItemClick = function () {
	var id = $(this).data('item-id');
	Razor.selectItem(id);
};
RazorUI.beginUpdate = function () {
	$('#update').hide();
	$('#updating').show();
	Razor.feeds = [];
	var feed;
	for (feed in RazorUI.feeds) {
		Razor.feeds.push(feed);
	}
	Razor.currentID = 0;
	Razor.updated = 0;
	RazorUI.updateFeed();
};
RazorUI.updateFeed = function (data) {
	if(data != undefined && data.msgs != undefined && data.msgs[0] != undefined && data.msgs[0].updated != undefined)
		Razor.updated = Razor.updated + data.msgs[0].updated;

	feed = Razor.feeds[Razor.currentID];
	if(feed == undefined)
		return RazorUI.finishUpdate();
	$('#updating .progress').text('(' + Razor.currentID + '/' + Razor.feeds.length + ')');
	LilinaAPI.call('update.single', {"id": feed}, RazorUI.updateFeed, RazorUI.updateFeed);
	Razor.currentID++;
};
RazorUI.finishUpdate = function () {
	$('#updating').hide();
	$('#update').show();
	if(Razor.updated > 0) {
		$('#menu').prepend($('<li>New items are available. Reload to view.</li>'));
	}
};

$(document).ready(Razor.init);