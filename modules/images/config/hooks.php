<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\Nodes\Save::process' => 'ICanBoogie\Hooks\Images::on_nodes_save',
		'ICanBoogie\Operation\Contents\Config::process:before' => 'ICanBoogie\Hooks\Images::before_contents_config',
		'ICanBoogie\Module\Contents::alter.block.edit' => 'ICanBoogie\Hooks\Images::on_alter_block_edit',
		'ICanBoogie\Module\Contents::alter.block.config' => 'ICanBoogie\Hooks\Images::on_alter_block_config',

		'Icybee::render' => 'ICanBoogie\Hooks\Images::on_icybee_render',
		'ICanBoogie\ActiveRecord\Content::get_css_class' => 'ICanBoogie\Hooks\Images::on_get_css_class'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::__get_image' => 'ICanBoogie\Hooks\Images::__get_image'
	),

	'textmark' => array
	(
		'images.reference' => array
		(
			'ICanBoogie\Hooks\Images::textmark_images_reference'
		)
	)
);