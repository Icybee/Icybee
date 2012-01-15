<?php

namespace ICanBoogie\Modules\Images\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\Nodes\Save::process' => __NAMESPACE__ . '::on_nodes_save',
		'ICanBoogie\Operation\Contents\Config::process:before' => __NAMESPACE__ . '::before_contents_config',
		'ICanBoogie\Modules\Contents\Module::alter.block.edit' => __NAMESPACE__ . '::on_alter_block_edit',
		'ICanBoogie\Modules\Contents\Module::alter.block.config' => __NAMESPACE__ . '::on_alter_block_config',

		'Icybee::render' => __NAMESPACE__ . '::on_icybee_render',
		'ICanBoogie\ActiveRecord\Content::get_css_class' => __NAMESPACE__ . '::on_get_css_class'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::__get_image' => __NAMESPACE__ . '::__get_image'
	),

	'textmark' => array
	(
		'images.reference' => array
		(
			__NAMESPACE__ . '::textmark_images_reference'
		)
	)
);