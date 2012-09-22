<?php

namespace Icybee\Modules\I18n;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\EditBlock::alter_children' => $hooks . 'on_nodes_editblock_alter_children'
	)
);