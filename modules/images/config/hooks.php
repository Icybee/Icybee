<?php

namespace ICanBoogie\Modules\Images\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\SaveOperation::process' => __NAMESPACE__ . '::on_nodes_save',
		'ICanBoogie\Modules\Contents\ConfigOperation::process:before' => __NAMESPACE__ . '::before_contents_config',
		'ICanBoogie\Modules\Contents\EditBlock::alter_children' => __NAMESPACE__ . '::on_contents_editblock_alter_children',
		'ICanBoogie\Modules\Contents\ConfigBlock::alter_children' => __NAMESPACE__ . '::on_contents_configblock_alter_children',

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