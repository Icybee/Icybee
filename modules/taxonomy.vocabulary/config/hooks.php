<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\Nodes\Save::process' => 'ICanBoogie\Hooks\Taxonomy\Vocabulary::on_node_save',
		'ICanBoogie\Module\Nodes::alter.block.edit' => 'ICanBoogie\Hooks\Taxonomy\Vocabulary::alter_block_edit',
		'ICanBoogie\ActiveRecord\Node::property' => 'ICanBoogie\Hooks\Taxonomy\Vocabulary::get_term',

		'Icybee\Views::alter' => 'ICanBoogie\Hooks\Taxonomy\Vocabulary::on_alter_views',
		'Icybee\Views\Provider::alter_query' => 'ICanBoogie\Hooks\Taxonomy\Vocabulary::on_alter_provider_query',
	)
);