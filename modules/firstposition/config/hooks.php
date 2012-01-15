<?php

namespace ICanBoogie\Modules\Firstposition\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\Module::alter.block.edit' => __NAMESPACE__ . '::event_alter_block_edit',
		'Icybee::render' => __NAMESPACE__ . '::on_icybee_render',
		'ICanBoogie\Modules\Pages\ExportOperation::process' => __NAMESPACE__ . '::event_operation_export',
		'BrickRouge\Document::render_title:before' => __NAMESPACE__ . '::on_document_render_title',
		'BrickRouge\Document\::render_metas:before' => __NAMESPACE__ . '::before_document_render_metas',
		'BrickRouge\Document\::render_metas' => __NAMESPACE__ . '::on_document_render_metas'
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