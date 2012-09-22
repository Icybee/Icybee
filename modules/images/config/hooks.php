<?php

namespace Icybee\Modules\Images;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\SaveOperation::process' => $hooks . 'on_nodes_save',
		'Icybee\Modules\Contents\ConfigBlock::alter_children' => $hooks . 'on_contents_configblock_alter_children',
		'Icybee\Modules\Contents\ConfigOperation::process:before' => $hooks . 'before_contents_config',
		'Icybee\Modules\Contents\Content::alter_css_class_names' => $hooks . 'on_alter_css_class_names',
		'Icybee\Modules\Contents\EditBlock::alter_children' => $hooks . 'on_contents_editblock_alter_children',
		'Icybee\Modules\Contents\ViewProvider::alter_result' => $hooks . 'on_contents_provider_alter_result',
		'Icybee\Modules\Pages\PageController::render' => $hooks . 'on_page_controller_render'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_image' => $hooks . 'get_image'
	),

	'textmark' => array
	(
		'images.reference' => array
		(
			$hooks . 'textmark_images_reference'
		)
	)
);