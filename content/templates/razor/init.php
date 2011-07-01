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
<h2>Keyboard Shortcuts</h2>
<dl class="keyboard-shortcuts">
	<dt>?</dt>
	<dd>Open help screen</dd>
</dl>
<dl class="keyboard-shortcuts">
	<dt>j</dt>
	<dd>Select the previous item</dd>
</dl>
<dl class="keyboard-shortcuts">
	<dt>k</dt>
	<dd>Select the next item</dd>
</dl>
<dl class="keyboard-shortcuts">
	<dt>v</dt>
	<dd>View the current item</dd>
</dl>
<p>More coming soon!</p>
<h2>About Razor</h2>
<p>Razor was developed by <a href="http://ryanmccue.info/">Ryan McCue</a> as a proof-of-concept. It is intended to make Lilina act like a desktop feed reader, and to be as responsive.</p>',
		'permalink' => 'http://getlilina.org/',
		'timestamp' => time()
	);
}

class LibraryView {
	public $id;
	protected $title;
	protected $parameters = array();
	protected $children = array();

	public function __construct($id, $title, $children = array()) {
		$this->id = $id;
		$this->title = $title;
		foreach ($children as $child) {
			$this->add_child($child);
		}
	}

	public function add_child($child) {
		$this->children[$child->id] = $child;
	}

	public function get_title() {
		return $this->title;
	}

	public function get_children() {
		return $this->children;
	}

	public function has_children() {
		return count($this->children) > 0;
	}
}
function print_library_item(&$item, $parent) {
	if ($item->has_children()) {
		$return = '<li class="expandable"><a href="#!/view/' . $parent . '-' . $item->id  . '" id="' . $parent . '-' . $item->id . '"><span class="arrow">&#x25B6;</span>' . $item->get_title() . '</a>';
	}
	else {
		$return = '<li><a href="#!/view/' . $parent . '-' . $item->id  . '" id="' . $parent . '-' . $item->id . '">' . $item->get_title() . '</a>';
	}
	
	if ($item->has_children()) {
		$return .= '<ul>';
		foreach ($item->get_children() as $child) {
			$return .= print_library_item($child, $parent . '-' . $item->id);
		}
		$return .= '</ul>';
	}
	$return .= '</li>';
	return $return;
}