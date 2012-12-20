<?php

namespace Icybee\Modules\Seo;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Pages\EditBlock::alter_children' => $hooks . 'on_page_editblock_alter_children',
		'Icybee\Modules\Pages\ExportOperation::process' => $hooks . 'on_operation_export',
		'Icybee\Modules\Pages\PageController::render' => $hooks . 'on_page_controller_render',
		'Icybee\Modules\Sites\EditBlock::alter_children' => $hooks . 'on_site_editblock_alter_children',
		'Brickrouge\Document::render_title:before' => $hooks . 'before_document_render_title',
		'Brickrouge\Document::render_metas:before' => $hooks . 'before_document_render_metas',
		'Brickrouge\Document::render_metas' => $hooks . 'on_document_render_metas'
	),

	'patron.markups' => array // TODO-20120922: these markups should be defined elsewhere
	(
		'document:metas' => array
		(
			$hooks . 'markup_document_metas', array()
		),

		'document:title' => array
		(
			$hooks . 'markup_document_title', array()
		),
	)
);