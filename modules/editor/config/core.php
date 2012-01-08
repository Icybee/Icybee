<?php

$_includes_root = $path . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		'WdEditorElement' => $_includes_root . 'wdeditorelement.php',
		'WdMultiEditorElement' => $_includes_root . 'wdmultieditorelement.php',

		'moo_WdEditorElement' => $path . 'editors/moo/editor.php',
		'feed_WdEditorElement' => $path . 'editors/feed/editor.php',
		'patron_WdEditorElement' => $_includes_root . 'patron_wdeditorelement.php',
		'raw_WdEditorElement' => $_includes_root . 'raw_wdeditorelement.php',
		'text_WdEditorElement' => $_includes_root . 'text_wdeditorelement.php',
		'textmark_WdEditorElement' => $_includes_root . 'textmark_wdeditorelement.php',
		'php_WdEditorElement' => $_includes_root . 'php_wdeditorelement.php',
		'widgets_WdEditorElement' => $path . 'editors/widgets/editor.php',

		'adjustimage_WdEditorElement' => $path . 'editors/adjustimage/editor.php',
	)
);