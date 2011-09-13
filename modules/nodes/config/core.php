<?php

$widgets_path = $path . 'widgets' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		'system_nodes_view_WdMarkup' => $path . 'markups.php',
		'system_nodes_list_WdMarkup' => $path . 'markups.php',

		'BrickRouge\Widget\AdjustNode' => $widgets_path . 'adjust-node.php',
		'BrickRouge\Widget\PopNode' => $widgets_path . 'pop-node.php',
		'BrickRouge\Widget\TitleSlugCombo' => $widgets_path . 'title-slug-combo.php',
		'BrickRouge\Element\Nodes\Pager' => $path . 'module.php',
		'adjustnode_WdEditorElement' => $widgets_path . 'adjust-node.editor.php'
	)
);