<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\SaveOperation::process' => 'ICanBoogie\Modules\Taxonomy\Vocabulary\Hooks::on_node_save',
		'ICanBoogie\Modules\Nodes\Module::alter.block.edit' => 'ICanBoogie\Modules\Taxonomy\Vocabulary\Hooks::alter_block_edit',
		'ICanBoogie\ActiveRecord\Node::property' => 'ICanBoogie\Modules\Taxonomy\Vocabulary\Hooks::get_term',

		'Icybee\Views::alter' => 'ICanBoogie\Modules\Taxonomy\Vocabulary\Hooks::on_alter_views',
		'Icybee\Views\Provider::alter_query' => 'ICanBoogie\Modules\Taxonomy\Vocabulary\Hooks::on_alter_provider_query',
	)
);