<?php

namespace ICanBoogie\Modules\Editor;

return array
(
	'autoload' => array
	(
		__NAMESPACE__ . '\Collection' => $path . 'lib/collection.php',
		__NAMESPACE__ . '\Editor' => $path . 'lib/editor.php',
		__NAMESPACE__ . '\EditorElement' => $path . 'lib/editor.php',
		__NAMESPACE__ . '\SelectorElement' => $path . 'lib/elements/selector.php',
		__NAMESPACE__ . '\MultiEditorElement' => $path . 'lib/elements/multieditor.php',

		# editors

		__NAMESPACE__ . '\FeedEditor' => $path . 'lib/editors/feed/editor.php',
		__NAMESPACE__ . '\FeedEditorElement' => $path . 'lib/editors/feed/element.php',
		__NAMESPACE__ . '\ImageEditor' => $path . 'lib/editors/image/editor.php',
		__NAMESPACE__ . '\ImageEditorElement' => $path . 'lib/editors/image/element.php',
		__NAMESPACE__ . '\NodeEditor' => $path . 'lib/editors/node/editor.php',
		__NAMESPACE__ . '\NodeEditorElement' => $path . 'lib/editors/node/element.php',
		__NAMESPACE__ . '\PatronEditor' => $path . 'lib/editors/patron/editor.php',
		__NAMESPACE__ . '\PatronEditorElement' => $path . 'lib/editors/patron/element.php',
		__NAMESPACE__ . '\PHPEditor' => $path . 'lib/editors/php/editor.php',
		__NAMESPACE__ . '\PHPEditorElement' => $path . 'lib/editors/php/element.php',
		__NAMESPACE__ . '\RawEditor' => $path . 'lib/editors/raw/editor.php',
		__NAMESPACE__ . '\RawEditorElement' => $path . 'lib/editors/raw/element.php',
		__NAMESPACE__ . '\RTEEditor' => $path . 'lib/editors/rte/editor.php',
		__NAMESPACE__ . '\RTEEditorElement' => $path . 'lib/editors/rte/element.php',
		__NAMESPACE__ . '\TextEditor' => $path . 'lib/editors/text/editor.php',
		__NAMESPACE__ . '\TextEditorElement' => $path . 'lib/editors/text/element.php',
		__NAMESPACE__ . '\TextmarkEditor' => $path . 'lib/editors/textmark/editor.php',
		__NAMESPACE__ . '\TextmarkEditorElement' => $path . 'lib/editors/textmark/element.php',
		__NAMESPACE__ . '\WidgetsEditor' => $path . 'lib/editors/widgets/editor.php',
		__NAMESPACE__ . '\WidgetsEditorElement' => $path . 'lib/editors/widgets/element.php'
	)
);