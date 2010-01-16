Razor = {};
Razor.init = function () {
	//RazorAPI.init();
	RazorUI.init();
};
String.prototype.shorten = function(length) {
	if (this.length > length) {
		var shorterLength = length - 4;
		return this.substr(0, shorterLength) + "...";
	} else {
		return String(this);
	}
};

RazorUI = {};
RazorUI.init = function () {
	$(window).resize(RazorUI.fitToWindow);
	RazorUI.fitToWindow();

	$('.relative').toRelativeTime();

	LilinaAPI.call('feeds.getList', {}, RazorUI.populateFeedList);
	// We'll fix this hardcoded limit later.
	LilinaAPI.call('items.getList', {"limit": 20}, RazorUI.populateItemList);

	$('#items-list li a').live('click', RazorUI.handleItemClick);
	$('#help').click(function () {
		var loading = $('<div class="loading">Loading...</div>');
		$('#item-view').html(loading);
		LilinaAPI.call('razor.help', {}, RazorUI.populateItemView);
	});
	$('#update a').click(function () {
		RazorUI.beginUpdate();
		return false;
	});
	$('#updating').hide();
};
RazorUI.fitToWindow = function () {
	$('#sidebar, #items-list, #item-view').css( {
		'height': $(window).height() - 51
	});
	$('#sidebar .item-list').css( {
		'height': $(window).height() - 84
	});
	$('#item-view').css( {
		'width': $(window).width() - 592
	});
	$('#item-content').css( {
		'height': $(window).height() - 137
	});
};
RazorUI.populateFeedList = function (list) {
	$('#feeds-list').empty();

	RazorUI.feeds = list;
	$.each(list, function (index, item) {
		var li = $('<li><a href="#"> <span /></a></li>');
		var a = $('a', li);
		var span = $('<span />');

		a.data('feed-id', item.id);
		a.text( item.name.shorten(25) ).attr('title', item.name);
		span.addClass('delete');
		span.text('Delete');
		a.append(span);
		$('#feeds-list').append(li);
	});
};
RazorUI.populateItemList = function (list) {
	$('#items-list ol').empty();

	index = 0;
	$.each(list, function (id, item) {
		var li = $('<li><a href="#"><span class="item-title" /> <span class="sep">from</span> <span class="item-source" /> <span class="sep">at</span> <span class="item-date" /></a></li>');
		var a = $('a', li);

		a.data('item-id', id).attr('title', item.title);
		$('.item-title', li).html( item.title.shorten(40) );
		var feed = RazorUI.feeds[item.feed_id];
		$('.item-source', li).html(feed.name);

		var date = new Date(item.timestamp * 1000);
		$('.item-date', li).text(date.toUTCString());

		if(index % 2) {
			li.addClass('alt');
		}

		$('#items-list ol').append(li);
		index++;
	});
	$('#items-list .item-date').toRelativeTime();
};
RazorUI.populateItemView = function (item) {
	$('#item-view').empty();
	var basics = $('<div id="item"><div id="heading"><h2 class="item-title"><a /></h2><p class="item-meta"><span class="item-source">From <a /></span>. <span class="item-date">Posted <abbr /></span></p></div><div id="item-content" /></div>');

	if(item.feed_id != undefined)
		var feed = RazorUI.feeds[item.feed_id];
	else
		var feed = {"name": "Razor", "url": "http://getlilina.org/"};

	var date = new Date(item.timestamp * 1000);

	$('.item-title a', basics).html(item.title).attr('href', item.permalink);
	$('.item-source a', basics).html(feed.name).attr('href', feed.url).addClass('external');
	$('.item-date abbr', basics).text(date.toUTCString()).attr('title', date.toUTCString()).toRelativeTime();
	$('#item-content', basics).html(item.content);

	if(item.actions != undefined && item.actions.length > 0) {
		$(basics).append('<div class="footer"><ul></ul></div>');
		$.each(item.actions, function (index, action) {
			var li = $('<li></li>').html(action);
			$('.footer ul', basics).append(li);
		});
	}

	$('#item-view').html(basics);
	RazorUI.fitToWindow();
};
RazorUI.handleItemClick = function () {
	var id = $(this).data('item-id');
	var loading = $('<div class="loading">Loading...</div>');
	$('#item-view').html(loading);
	LilinaAPI.call('items.get', {'id': id}, RazorUI.populateItemView);
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