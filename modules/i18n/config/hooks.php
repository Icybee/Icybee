<?php

namespace ICanBoogie\Modules\I18n\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\EditBlock::alter_children' => __NAMESPACE__ . '::on_nodes_editblock_alter_children'
	)
);