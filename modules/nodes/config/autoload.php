<?php

namespace Icybee\Modules\Nodes;

$widgets_path = $path . 'widgets' . DIRECTORY_SEPARATOR;

return array
(
	__NAMESPACE__ . '\PopNode' => $widgets_path . 'pop-node.php',
	__NAMESPACE__ . '\ViewProvider' => $path . 'lib/views/provider.php',

	'Brickrouge\Widget\AdjustNode' => $widgets_path . 'adjust-node.php',
	'Brickrouge\Widget\TitleSlugCombo' => $widgets_path . 'title-slug-combo.php',
	'Brickrouge\Element\Nodes\Pager' => $path . 'module.php'
);