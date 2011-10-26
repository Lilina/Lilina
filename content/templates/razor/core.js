/* Relative time extensions */
/*
 * Returns a description of this past date in relative terms.
 * Example: '3 years ago'
 */
Date.prototype.toRelativeTime = function() {
	var delta       = new Date() - this;
	var units       = null;
	var conversions = {
	millisecond: 1, // ms    -> ms
		second: 1000,   // ms    -> sec
		minute: 60,     // sec   -> min
		hour:   60,     // min   -> hour
		day:    24,     // hour  -> day
		month:  30,     // day   -> month (roughly)
		year:   12      // month -> year
	};

	for (var key in conversions) {
		if(delta < conversions[key]) {
			break;
		} else {
			units = key; // keeps track of the selected key over the iteration
			delta = delta / conversions[key];
		}
	}

	// pluralize a unit when the difference is greater than 1.
	delta = Math.floor(delta);
	if(delta !== 1) { units += "s"; }
	return [delta, units, "ago"].join(" ");
};

/*
 * Wraps up a common pattern used with this plugin whereby you take a String 
 * representation of a Date, and want back a date object.
 */
Date.fromString = function(str) {
	return new Date(Date.parse(str));
};

String.prototype.lpad = function (padding, length) {
	var string = this;
	while (string.length < length) {
		string = padding + string;
	}

	return string;
};
String.prototype.rpad = function (padding, length) {
	var string = this;
	while (string.length < length) {
		string += padding;
	}

	return string;
};

Date.prototype.toHumanString = function () {
	var string = this.getFullYear();
	string += '-' + (this.getMonth() + 1).toString().lpad("0", 2);
	string += '-' + (this.getDate() + 1).toString().lpad("0", 2);
	string += ' ' + this.getHours().toString().lpad("0", 2);
	string += ':' + this.getMinutes().toString().lpad("0", 2);
	string += ':' + this.getSeconds().toString().lpad("0", 2);

	return string;
};

(function($) {
	/*
	 * A handy jQuery wrapper for converting tags with JavaScript parse()-able
	 * time-stamps into relative time strings.
	 *
	 * Usage:
	 *   Suppose numerous Date.parse()-able time-stamps are available in the 
	 *   inner-HTML of some <span class="rel"> elements...
	 *
	 *   $("span.rel").toRelativeTime()
	 *
	 * Examples: '5 years ago', '45 minutes ago'
	 *
	 * Requires date.extensions.js to be loaded first.
	 */
	$.fn.toRelativeTime = function() {
		this.each(function() {
			var $this = $(this);
			$this.text(Date.fromString($this.html()).toRelativeTime());
		});
	};
})(jQuery);



/* Hotkeys */
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
		space: 64,
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



/* Shorten strings */
String.prototype.shorten = function(length) {
	if (this.length > length) {
		var shorterLength = length - 4;
		return this.substr(0, shorterLength) + "...";
	} else {
		return String(this);
	}
};



/* Razor */
Razor = {};
Razor.useFrame = false;
Razor.currentItem = false;
Razor.conditions = {};
Razor.currentlyLoading = null;
Razor.init = function () {
	//RazorAPI.init();
	RazorUI.init();
};
Razor.api = function (method, conditions, callback) {
	$.extend(conditions, Razor.conditions);
	return LilinaAPI.call(method, conditions, callback, false, 'GET', Razor.baseURL);
};
Razor.getScript = function(url, callback){
	// This allows caching, unlike $.getScript
	return $.ajax({
		type: "GET",
		url: url,
		success: callback,
		dataType: "script",
		cache: true
	});
};
Razor.lightbox = function (url) {
	$.fancybox({
		'transitionIn' : 'none',
		'transitionOut' : 'none',
		'type': 'iframe',
		'href': url
	});
	$(document).bind('close-frame', function () {
		RazorUI.feedLoader = LilinaAPI.call('feeds.getList', {}, RazorUI.populateFeedList);
		$.fancybox.close();
	});
};
Razor.maybeScroll = function (elem, parent) {
	elem = $(elem);
	parent = $(parent);

	var pos = elem.position().top;
	var parentHeight = parent.innerHeight();
	var height = elem.outerHeight();
	if (pos < 0) {
		Razor.scrollToTop(elem, parent);
		return true;
	}
	else if ((pos + height) > parentHeight) {
		Razor.scrollToBottom(elem, parent);
		return true;
	}
	return false;
};
Razor.scrollToTop = function (elem, parent) {
	pos = $(parent).scrollTop() + $(elem).position().top;
	$(parent).stop(true).animate({scrollTop: pos}, 200);
};
Razor.scrollToBottom = function (elem, parent) {
	var pos = $(elem).position().top;
	var parentHeight = $(parent).innerHeight();
	var height = $(elem).outerHeight();
	pos = $(parent).scrollTop() + (pos - parentHeight) + height;
	$(parent).stop(true).animate({scrollTop: pos}, 200);
};

RazorUI = {};
RazorUI.itemCount = 0;
RazorUI.showing = 'full';
RazorUI.headerHeight = 59;
RazorUI.init = function () {
	$(window).resize(RazorUI.fitToWindow);
	RazorUI.fitToWindow();

	$('.relative').toRelativeTime();
	var loading = $('<div class="loading">Loading...</div>');
	$("#items-list").html(loading);

	RazorUI.feedLoader = LilinaAPI.call('feeds.getList', {}, RazorUI.populateFeedList);
	Razor.api('items.getList', {"limit": 40}, RazorUI.initializeItemList);
	$('#items-reload').click(RazorUI.reloadItems);

	$('#items-list li a').live('click', RazorUI.handleItemClick);
	/*$('#sidebar .expandable > a .arrow')
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
			.hide();*/
	$('#help a').click(RazorUI.showHelp);
	$('#update a').click(function (e) {
		e.preventDefault();
		RazorUI.beginUpdate();
	});
	$('#items-list')
		.bind('initialized', function() {
			$('#load-more').click(RazorUI.loadMoreItems);
		})
		.bind('scroll', function () {
			if (Razor.loading)
				return;

			var remaining = RazorUI.itemCount - ($('#items-list').scrollTop() + $('#items-list').height()) / $('#items-list li').height();
			if (remaining < 8)
				RazorUI.loadMoreItems();
		});
	$.hotkeys({
		"?": RazorUI.showHelp,
		"k": RazorUI.selectPrevious,
		"j": RazorUI.selectNext,
		"v": RazorUI.openCurrent,
		"r": RazorUI.reloadItems,
		"h": RazorUI.showHelp
	});
	$('#switcher-sidebar').click(function () {
		RazorUI.showing = 'sidebar';
		RazorUI.fitToWindow();
	});
	$('#switcher-items').click(function () {
		RazorUI.showing = 'items';
		RazorUI.fitToWindow();
	});

	/* Sidebar bindings */
	$('#library-everything').live('click', function (e) {
		e.preventDefault();

		var loading = $('<div class="loading">Loading...</div>');
		$("#items-list").html(loading);
		$('#sidebar .selected').removeClass('selected');
		$(this).addClass('selected');
		Razor.conditions.conditions = {};

		Razor.api('items.getList', {"limit": 40}, RazorUI.initializeItemList);
	});
	$('#feeds-list .feed').live('click', function (e) {
		e.preventDefault();

		var loading = $('<div class="loading">Loading...</div>');
		$("#items-list").html(loading);
		$('#sidebar .selected').removeClass('selected');
		$(this).addClass('selected');
		Razor.conditions.conditions = {"feed": $(this).data('feed-id')};

		Razor.api('items.getList', {"limit": 40}, RazorUI.initializeItemList);
	});
	$('body').click(function () {
		$('#context-menu').removeClass('active');
	});
	$('#feeds-list .feed .menu').live('click', function (e) {
		var offset = $(this).offset();
		offset.top += $(this).outerHeight();
		var feed = 'feed-' + $(this).parent().data('feed-id');
		var oldFeed = $('#context-menu').data('current');
		$('#context-menu').data('current', feed).css(offset);
		if (oldFeed == feed) {
			$('#context-menu').removeClass('active').data('current', false);
		}
		else {
			$('#context-menu').addClass('active');
		}

		return false;
	});

	/* Dynamically load scripts */
	Razor.getScript(Razor.scriptURL + '/resources/fancybox/fancybox.js', function () {
		$('#footer-add').click(function (event) {
			Razor.lightbox( $('#header > h1 > a').attr('href') + 'admin/subscribe.php?framed' );
			event.preventDefault();
		});
		$('#item-services a.type-inline').live('click', function (event) {
			Razor.lightbox($(this).attr('href'));
			event.preventDefault();
		});
	});

	$.when(
		Razor.getScript(Razor.scriptURL + '/resources/raphael-min.js'),
		Razor.getScript(Razor.scriptURL + '/resources/icons.js')
	).then(function () {
		$('#update a').iconify('refresh');
		$('#settings a').iconify('gear');
		$('#help a').iconify('?');
		$('#login a').iconify('user');
		$('#logout a').iconify('power');

		// in case it has already loaded, since we're not using
		// deferred objects
		/*$('#feeds-list li .delete').iconify({
			icon: 'cross',
			style: {
				initial: { scale: "0.5833 0.5833" },
				normal: { fill: '#fff', stroke: 'none' },
				hover: { fill: '#911515'},
				active: { fill: '#911515', stroke: '#f00'}
			}
		});

		$('#feeds-list').bind('populated', function () {
			$('#feeds-list li .delete').iconify({
				icon: 'cross',
				style: {
					initial: { scale: "0.5833 0.5833" },
					normal: { fill: '#fff', stroke: 'none' },
					hover: { fill: '#911515'},
					active: { fill: '#911515', stroke: '#f00'}
				}
			});
		});*/
	});
};
RazorUI.selectItem = function (item) {
	Razor.currentItem = item;
	if (Razor.currentlyLoading !== null) {
		Razor.currentlyLoading.abort();
	}
	var loading = $('<div class="loading">Loading...</div>');
	$('#items-list li a.current').removeClass('current');
	$('#list-item-' + item).children('a').addClass('current');
	Razor.maybeScroll($("#list-item-" + item), $("#items-list"));
	$('#item-view').html(loading);

	Razor.currentlyLoading = LilinaAPI.call('items.get', {'id': item}, RazorUI.populateItemView);
};
RazorUI.selectNext = function () {
	var next = $('#items-list li:has(a.current)').next();
	if (next.length == 0 && !Razor.currentItem) {
		next = $('#items-list li:first');
	}
	else if (next.length == 0) {
		RazorUI.showMessage('No next item', 1500);
		return false;
	}

	if (next.attr('id') === 'load-more') {
		RazorUI.loadMoreItems();
		return;
	}
	var id = $('a', next).data('item-id');
	RazorUI.selectItem(id);
};
RazorUI.selectPrevious = function () {
	var prev = $('#items-list li:has(a.current)').prev();
	if (prev.length == 0 && !Razor.currentItem) {
		prev = $('#items-list li:first');
	}
	else if (prev.length == 0) {
		alert('No previous item');
		return false;
	}
	var id = $('a', prev).data('item-id');
	RazorUI.selectItem(id);
};
RazorUI.showHelp = function () {
	Razor.lightbox(Razor.baseURL + "?method=razor.help");
	return false;
};
RazorUI.openCurrent = function () {
	var current = $('#heading .item-title a').attr('href');
	if (current === undefined)
		return;

	if (!window.open(current)) {
		alert("Looks like your browser is blocking popup windows. Try unblocking them to open links.");
	}
};
RazorUI.fitToWindow = function () {
	var normalMode = $(window).width() > 920;

	if (RazorUI.showing != 'full' && normalMode) {
		$('#sidebar').show();
		$('#items-list-container').show();
		RazorUI.showing = 'full';
	}
	else if (RazorUI.showing == 'full' && !normalMode) {
		RazorUI.showing = 'items';
	}

	if (RazorUI.showing == 'items') {
		$('#sidebar').hide();
		$('#items-list-container').show();
	}
	else if (RazorUI.showing == 'sidebar') {
		$('#items-list-container').hide();
		$('#sidebar').show();
	}

	if (normalMode) {
		$('#sidebar, #items-list-container, #item-view').css( {
			'height': $(window).height() - $('#items-list-container').position().top
		});
	}
	else {
		$('#sidebar, #items-list-container,').css( {
			'height': $(window).height() - ($('#items-list-container').position().top + $('#switcher').outerHeight())
		});
		$('#item-view').css( {
			'height': $(window).height() - $('#items-list-container').position().top
		});
	}
	$('#sidebar .item-list').css( {
		'height': $('#sidebar').height() - $('#sidebar .footer').outerHeight()
	});
	$('#items-list').css( {
		'height': $('#items-list-container').height() - $('#items-list-container .footer').outerHeight()
	});

	if (normalMode) {
		$('#item-view').css( {
			'width': $(window).width() - ($('#sidebar').outerWidth() + $('#items-list-container').outerWidth())
		});
	}
	else {
		$('#item-view').css( {
			'width': $(window).width() - $('#sidebar').outerWidth()
		});
	}
	var contentHeight = $("#item-view").innerHeight() - ($('#item-content').position().top + $('#item-services').outerHeight() + 20); //header + footer + item header + padding
	if (Razor.useFrame) {
		contentHeight += 20;
	}

	$('#item-content').css( {
		'height': contentHeight
	});
};
RazorUI.showMessage = function (html, duration) {
	$('#messagearea').html(html).show();

	if (duration) {
		window.setTimeout(RazorUI.hideMessage, duration);
	}
};
RazorUI.hideMessage = function () {
	$('#messagearea').fadeOut(800, function () {
		$('#messagearea').html('');
	});
};
RazorUI.populateFeedList = function (list) {
	$('#feeds-list').empty();

	RazorUI.feeds = list;
	$.each(list, function (index, item) {
		var li = $('<li><a href="#"><img src="" /> <span /><span class="menu" /></a></li>');
		var a = $('a', li);

		a.addClass('feed').data('feed-id', item.id).attr('title', item.name);
		$('span:not(.delete)', a).text( item.name.shorten(25) );
		if (item.icon === false || item.icon === true) {
			$('img', li).attr('src', 'lilina-favicon.php?feed=' + item.id);
		}
		else {
			$('img', li).attr('src', item.icon);
		}
		$('.menu', a).text('\u25BC');
		//a.append(span);
		$('#feeds-list').append(li);
	});
	$('#feeds-list').trigger('populated');
};
RazorUI.feedContextMenu = function () {
	
};
RazorUI.initializeItemList = function (list) {
	RazorUI.itemCount = 0;
	$('#items-list').empty();
	var li = $('<li id="load-more"><a href="#">Load More Items</a></li>');
	$('#items-list').append(li);
	RazorUI.feedLoader.success(function () {
		RazorUI.populateItemList(list);
	});
	$('#items-list').trigger('initialized');
};
RazorUI.populateItemList = function (list) {
	var oldCount = RazorUI.itemCount;
	var hasTextOverflow = ('textOverflow' in document.documentElement.style || 'OTextOverflow' in document.documentElement.style)

	$.each(list, function (id, item) {
		var li = $('<li><a href="#"><span class="item-title" /> <span class="sep">from</span> <span class="item-source" /> <span class="sep">at</span> <span class="item-date" /></a></li>');
		var a = $('a', li);

		a.data('item-id', id).attr('title', item.title);

		if (!hasTextOverflow) {
			$('.item-title', li).html( item.title.shorten(45) );
		}
		else {
			$('.item-title', li).html( item.title/*.shorten(45)*/ );
		}

		if (item.feed_id != undefined && RazorUI.feeds[item.feed_id] !== undefined)
			var feed = RazorUI.feeds[item.feed_id];
		else
			var feed = {"name": "Unknown", "url": ""};

		$('.item-source', li).html(feed.name);

		var date = new Date(item.timestamp * 1000);
		$('.item-date', li).attr("title", date.toHumanString()).text(date.toRelativeTime());

		li.attr('id', 'list-item-' + id);
		li.insertBefore('#load-more');

		RazorUI.itemCount++;
	});

	if (RazorUI.itemCount === oldCount) {
		RazorUI.showMessage('No more items to load', 1500);
		$("#load-more").remove();
	}
	$('#items-list').trigger('populated');
};
RazorUI.loadMoreItems = function (e) {
	if (e)
		e.preventDefault();

	Razor.loading = true
	RazorUI.showMessage('Loading&hellip;');
	Razor.api('items.getList', {"limit": 20, "start": RazorUI.itemCount}, RazorUI.populateItemList).complete(function () {
		RazorUI.hideMessage();
		Razor.loading = false;
	});
};
RazorUI.reloadItems = function (e) {
	if (e)
		e.preventDefault();

	var loading = $('<div class="loading">Loading...</div>');
	$("#items-list").html(loading);
	RazorUI.showMessage('Loading&hellip;');
	Razor.api('items.getList', {"limit": 40}, RazorUI.initializeItemList).complete(RazorUI.hideMessage);
};
RazorUI.populateItemView = function (item) {
	$('#item-view').empty();
	var basics = $('<div id="item"><div id="heading"><h2 class="item-title"><a /></h2><p class="item-meta"><span class="item-source">From <a /></span>. <span class="item-date">Posted <abbr /></span><span class="item-author"> by <a /></span>.</p></div><div id="item-content" /></div>');
	if (Razor.useFrame) {
		basics = $('<div id="item"><div id="heading"><h2 class="item-title"><a /></h2><p class="item-meta"><span class="item-source">From <a /></span>. <span class="item-date">Posted <abbr /></span><span class="item-author"> by <a /></span>.</p></div><iframe id="item-content" class="framed" /></div>');
		$('#item-content', basics).attr('src', item.permalink);
	}

	if(item.feed_id != undefined && RazorUI.feeds[item.feed_id] !== undefined)
		var feed = RazorUI.feeds[item.feed_id];
	else
		var feed = {"name": "Unknown", "url": ""};

	var date = new Date(item.timestamp * 1000);

	$('.item-title a', basics).html(item.title).attr('href', item.permalink);
	$('.item-source a', basics).html(feed.name).attr('href', feed.url).addClass('external');
	if (item.author.name == null || item.author.name == false || item.author.name.length == 0) {
		$('.item-author', basics).remove();
	}
	else {
		$('.item-author a', basics).text(item.author.name);

		if (item.author.url != null && item.author.name != false && item.author.url.length != 0 && item.author.url != false) {
			$('.item-author a', basics).attr('href', item.author.url).addClass('external');
		}
		else {
			$('.item-author a', basics).replaceWith($('<span />').text(item.author.name));
		}
	}
	$('.item-date abbr', basics).attr("title", date.toHumanString()).text(date.toRelativeTime());
	if (!Razor.useFrame) {
		$('#item-content', basics).html(item.content);
	}

	var item_footer = $('<div class="footer" id="item-services"><ul></ul></div>');
	if(item.services != undefined) {
		$.each(item.services, function (index, service) {
			var service_item = $('<li><a></a></li>');
			$('a', service_item)
				.html(service.label)
				.addClass('type-' + service.type)
				.attr('href', service.action);
			$('ul', item_footer).append(service_item);
		});
	}
	$(basics).append(item_footer);

	$('#item-view').html(basics);
	$('#item-content').focus();
	RazorUI.fitToWindow();
	$('#item-view').trigger('populated');
};
RazorUI.handleItemClick = function (e) {
	e.preventDefault();

	var id = $(this).data('item-id');
	RazorUI.selectItem(id);
};
RazorUI.beginUpdate = function () {
	$('#update').hide();
	RazorUI.showMessage('<span id="updating">Now updating&hellip; <span class="progress"></span></span>');
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
	RazorUI.showMessage('Done!', 1000);
	$('#update').show();
	if(Razor.updated > 0) {
		Razor.reloadItems();
	}

};

$(document).ready(Razor.init);