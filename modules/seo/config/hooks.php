<?php

namespace ICanBoogie\Modules\Seo\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Pages\Module::alter.block.edit' => __NAMESPACE__ . '::on_page_alter_block_edit',
		'ICanBoogie\Modules\Sites\Module::alter.block.edit' => __NAMESPACE__ . '::on_site_alter_block_edit',
		'Icybee::render' => __NAMESPACE__ . '::on_icybee_render',
		'ICanBoogie\Modules\Pages\ExportOperation::process' => __NAMESPACE__ . '::event_operation_export',
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