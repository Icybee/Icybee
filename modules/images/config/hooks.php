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

		'ICanBoogie\Modules\Pages\PageController::render' => __NAMESPACE__ . '::on_page_controller_render',
		'ICanBoogie\ActiveRecord\Content::alter_css_class_names' => __NAMESPACE__ . '::on_alter_css_class_names',
		'ICanBoogie\Modules\Contents\Provider::alter_result' => __NAMESPACE__ . '::on_contents_provider_alter_result'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_image' => __NAMESPACE__ . '::get_image'
	),

	'textmark' => array
	(
		'images.reference' => array
		(
			__NAMESPACE__ . '::textmark_images_reference'
		)
	)
);