<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\Nodes\Save::process' => 'ICanBoogie\Hooks\Taxonomy\Vocabulary::on_node_save',
		'ICanBoogie\Module\Nodes::alter.block.edit' => 'ICanBoogie\Hooks\Taxonomy\Vocabulary::alter_block_edit',
		'ICanBoogie\ActiveRecord\Node::property' => 'ICanBoogie\Hooks\Taxonomy\Vocabulary::get_term'
	)
);