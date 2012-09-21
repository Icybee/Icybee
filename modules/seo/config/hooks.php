<?php

namespace ICanBoogie\Modules\Seo\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Pages\EditBlock::alter_children' => __NAMESPACE__ . '::on_page_editblock_alter_children',
		'ICanBoogie\Modules\Sites\EditBlock::alter_children' => __NAMESPACE__ . '::on_site_editblock_alter_children',
		'ICanBoogie\Modules\Pages\PageController::render' => __NAMESPACE__ . '::on_page_controller_render',
		'ICanBoogie\Modules\Pages\ExportOperation::process' => __NAMESPACE__ . '::on_operation_export',
		'Brickrouge\Document::render_title:before' => __NAMESPACE__ . '::on_document_render_title',
		'Brickrouge\Document::render_metas:before' => __NAMESPACE__ . '::before_document_render_metas',
		'Brickrouge\Document::render_metas' => __NAMESPACE__ . '::on_document_render_metas'
	),

	'patron.markups' => array
	(
		'document:metas' => array
		(
			__NAMESPACE__ . '::markup_document_metas', array()
		),

		'document:title' => array
		(
			__NAMESPACE__ . '::markup_document_title', array()
		),
	)
);