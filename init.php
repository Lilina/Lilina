<?php

function razor_register(&$controller) {
	$controller->registerMethod('razor.help', 'razor_help');
}
add_action('LilinaAPI-register', 'razor_register', 10, 1);

function razor_help() {
	return array(
		'title' => 'Welcome to Razor!',
		'content' =>
'<h1>Razor</h1>
<p>Razor is a template for Lilina, built to act and feel like a desktop feed reader.</p>
<h2>Using the interface</h2>
<p>To the far left, you&apos;ll notice a column with all your feeds. In the future, there&apos;ll be much more here. As you can see, there isn&apos;t much there yet. :)</p>
<p>The middle column contains all of your items. Simply click on one and it&apos;ll appear over here.</p>
<p>Over here is where the item details are displayed. Up the top, you&apos;ll see the title of the item, the site where the item comes from and when the item was posted.</p>
<blockquote>Quotes are displayed thusly.</blockquote>
<h2>About Razor</h2>
<p>Razor was developed by <a href="http://ryanmccue.info/">Ryan McCue</a> as a proof-of-concept. It is intended to make Lilina act like a desktop feed reader, and to be as responsive.</p>',
		'permalink' => 'http://getlilina.org/',
		'timestamp' => time()
	);
}