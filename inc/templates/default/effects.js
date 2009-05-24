Items = {};
Items.count = 0;
Items.current = 0;
Items.clickHandler = function (selector) {
	$(selector)
		.siblings('.excerpt')
			.slideToggle()
		.parent()
			.toggleClass('c1')
			.toggleClass('c2');
	return false;
};
Items.setup = function () {
	$('#main .item')
		.children('.excerpt')
			.hide()
			.parent()
				.addClass('c1')
				.removeClass('c2')
			.end()
		.end()
		.children('h1')
			.css( {cursor:'pointer'} )
			.click(function() {
				$(this).next().slideToggle();
			})
		.end()
		.children('.title')
			.click(function () { Items.clickHandler(this); })
		.end()
		.each(function (item) {
			var thisId = jQuery(this).attr("id").replace('item-', '');
			Items.count++;
		})
		.filter(':first')
			.addClass('currentItem');
};

function keyboardWatcher(e) {
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
				$('#main .item.currentItem').removeClass('currentItem');
				Items.current++;
				if(Items.current == Items.count) {
					$('#item-0').addClass('currentItem');
					Items.current = 0;
				}
				else {
					$('#item-' + Items.current).addClass('currentItem');
				}
				$.scrollTo('#item-' + Items.current, 400);
				//window.location = '#item-' + Items.current;
				break;
			//  "k" key
			case 75:
				$('#main .item.currentItem').removeClass('currentItem');
				Items.current--;
				if( Items.current < 0 ) {
					$('#item-' + (Items.count - 1)).addClass('currentItem');
					Items.current = (Items.count - 1);
				}
				else {
					$('#item-' + Items.current).addClass('currentItem');
				}
				$.scrollTo('#item-' + Items.current, 400);
				//window.location = '#item-' + Items.current;
				break;
			// "v" key
			case 86:
				var newWindow = window.open($('#item-' + Items.current + ' .source a').attr('href'));
				if(!newWindow)
					alert('It looks like a popup blocker is preventing Lilina from opening this page. If you have a popup blocker, try disabling it for this page.');
				break;
			// "o" key
			case 79:
			case 13:
				Items.clickHandler('#main .item.currentItem .title');
				break;
		}
	}

/* JQuery Stuff! */
$(document).ready(function() {
	/* Setup our global buttons */
	$('.river-page #expandall').css({cursor:'pointer'}).click(function (item) {
		$('.excerpt').slideDown();
		$('.item').removeClass('c2').addClass('c1');

		return false;
	});
	$('.river-page #collapseall').css({cursor:'pointer'}).click(function() {
		$('.excerpt').slideUp();
		$('.item').addClass('c1').removeClass('c2');

		return false;
	});

	/* Ajax loading if no items are found */
	$('.river-page #viewallitems').click(function() {
		$('#main').load('index.php?hours=-1 #main', {}, function () {
			setup_items();
		});
		
		return false;
	});

	$(document).bind('keydown', keyboardWatcher);

	/* Per-item setup */
	Items.setup();
});