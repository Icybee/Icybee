<?php

namespace ICanBoogie\Modules\Nodes;

$widgets_path = $path . 'widgets' . DIRECTORY_SEPARATOR;

return array
(
	__NAMESPACE__ . '\PopNode' => $widgets_path . 'pop-node.php',

	'Icybee\Views\Nodes\Provider' => $path . 'lib/views/provider.php',

	'system_nodes_view_WdMarkup' => $path . 'markups.php',
	'system_nodes_list_WdMarkup' => $path . 'markups.php',

	'Brickrouge\Widget\AdjustNode' => $widgets_path . 'adjust-node.php',
	'Brickrouge\Widget\TitleSlugCombo' => $widgets_path . 'title-slug-combo.php',
	'Brickrouge\Element\Nodes\Pager' => $path . 'module.php'
);