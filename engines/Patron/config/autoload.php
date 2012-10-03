<?php

$includes_root = $path . 'includes' . DIRECTORY_SEPARATOR;

return array
(
	'Patron\Compiler' => $path . 'lib/patron/compiler.php',
	'Patron\ControlNode' => $path . 'lib/patron/nodes/control.php',
	'Patron\EvaluateNode' => $path . 'lib/patron/nodes/evaluate.php',
	'Patron\Template' => $path . 'lib/patron/template.php',

	'Patron\HTMLParser' => $path . 'lib/html-parser.php',
	'Patron\Engine' => $path . 'lib/patron/engine.php',
	'Patron\TextHole' => $path . 'lib/texthole.php',
	'Textmark_Parser' => $path . 'lib/textmark.php',

	'patron_WdMarkup' => $includes_root . 'patron_wdmarkup.php',
	'patron_markups_WdHooks' => $includes_root . 'markups.php',

	'patron_native_WdMarkups' => $path . 'markups/native.markups.php',
	'patron_feed_WdMarkups' => $path . 'markups/feed.markups.php',
	'patron_elements_WdMarkups' => $path . 'markups/elements.markups.php'
);