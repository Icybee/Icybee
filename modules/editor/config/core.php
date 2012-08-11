<?php

namespace ICanBoogie\Modules\Editor;

$_includes_root = $path . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		__NAMESPACE__ . '\Collection' => $path . 'lib/collection.php',
		__NAMESPACE__ . '\Editor' => $path . 'lib/editor.php',
		__NAMESPACE__ . '\EditorElement' => $path . 'lib/editor.php',
		__NAMESPACE__ . '\PatronEditor' => $path . 'lib/editors/patron/editor.php',
		__NAMESPACE__ . '\PatronEditorElement' => $path . 'lib/editors/patron/element.php',
		__NAMESPACE__ . '\RawEditor' => $path . 'lib/editors/raw/editor.php',
		__NAMESPACE__ . '\RawEditorElement' => $path . 'lib/editors/raw/element.php',
		__NAMESPACE__ . '\RTEEditor' => $path . 'lib/editors/rte/editor.php',
		__NAMESPACE__ . '\RTEEditorElement' => $path . 'lib/editors/rte/element.php',

		'WdEditorElement' => $_includes_root . 'wdeditorelement.php',
		'WdMultiEditorElement' => $_includes_root . 'wdmultieditorelement.php',

		'feed_WdEditorElement' => $path . 'editors/feed/editor.php',
		'text_WdEditorElement' => $_includes_root . 'text_wdeditorelement.php',
		'textmark_WdEditorElement' => $_includes_root . 'textmark_wdeditorelement.php',
		'php_WdEditorElement' => $_includes_root . 'php_wdeditorelement.php',
		'widgets_WdEditorElement' => $path . 'editors/widgets/editor.php',

		'adjustimage_WdEditorElement' => $path . 'editors/adjustimage/editor.php',
	)
);