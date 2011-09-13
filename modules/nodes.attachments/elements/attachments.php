<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Uploaded;
use BrickRouge\Element;
use BrickRouge\Document;
use ICanBoogie\Operation;

class WdAttachmentsElement extends Element
{
	const T_NODEID = '#attachments-nodeid';
	const T_HARD_BOND = '#attachments-hard-bond';

	public function __construct($tags, $dummy=null)
	{
		parent::__construct('div', $tags);

		$this->add_class('resources-files-attached');
	}

	protected function render_inner_html()
	{
		global $core;

		$document = $core->document;

		$document->css->add('attachments.css');
		$document->js->add('attachments.js');
		$document->js->add('../../files/elements/Swiff.Uploader.js');

		$nid = $this->get(self::T_NODEID);
		$hard_bond = $this->get(self::T_HARD_BOND, false);

		$lines = null;

		if ($nid)
		{
			$entries = $core->models['nodes.attachments']->query
			(
				'SELECT {alias}.*, file.nid, file.size, file.path
				FROM {self} {alias}
				INNER JOIN {prefix}files file ON {alias}.fileid = file.nid
				WHERE nodeid = ?', array
				(
					$nid
				)
			)
			->fetchAll(PDO::FETCH_OBJ);

			foreach ($entries as $entry)
			{
				$lines .= self::create_attachment($entry, $hard_bond);
			}
		}

		$formats = null;

		//$formats = 'Seules les pièces avec les extensions suivantes sont prises en charge&nbsp;: jpg jpeg gif png txt doc xls pdf ppt pps odt ods odp.';

		$limit = ini_get('upload_max_filesize') * 1024 * 1024;
		$limit_formated = wd_format_size($limit);

		$this->dataset = array
		(
			'path' => Document::resolve_url('../../files/elements/Swiff.Uploader.swf'),
// 			'verbose' => false,
			'file-size-max' => $limit
		)

		+ $this->dataset;

		$label_join = t('Add a new attachment');
		$label_limit = t('The maximum size for each attachment is :size', array(':size' => $limit_formated));

		return <<<EOT
<ol>
	$lines
	<li class="progress">&nbsp;</li>
</ol>

<button type="button">$label_join</button>

<div class="element-description">$label_limit.$formats</div>
EOT;
	}

	static public function create_attachment($entry, $hard_bond=false)
	{
		global $core;

		$hiddens = null;
		$links = array();

		$i = uniqid();
		$size = wd_format_size($entry->size);
		$preview = null;

		if ($entry instanceof Uploaded)
		{
			$title = $entry->name;
			$extension = $entry->extension;

			$hiddens .= '<input type="hidden" class="file" name="nodes_attachments[' . $i .'][file]" value="' . wd_entities(basename($entry->location)) . '" />' . PHP_EOL;
			$hiddens .= '<input type="hidden" name="nodes_attachments[' . $i .'][mime]" value="' . wd_entities($entry->mime) . '" />' . PHP_EOL;

			$links = array
			(
				'<a href="#remove" class="remove">' . t('label.remove') . '</a>'
			);
		}
		else
		{
			$fid = $entry->nid;
			$title = $entry->title;
			$extension = substr($entry->path, strrpos($entry->path, '.'));

			$hiddens .= '<input type="hidden" name="nodes_attachments[' . $i .'][fileid]" value="' . $fid . '" />';

			$links = array
			(
				'<a href="' . Route::contextualize('/admin/resources.files/' . $fid . '/edit') . '">' . t('label.edit') .'</a>',
				'<a href="' . Operation::encode('resources.files/' . $fid . '/download') . '">' . t('label.download') . '</a>',
				$hard_bond ? '<a href="#delete" class="danger">' . t('Delete file') .'</a>' : '<a href="#remove" class="warn">' . t('Break link') . '</a>'
			);

			$node = $core->models['nodes'][$entry->nid];

			if ($node instanceof ICanBoogie\ActiveRecord\Image)
			{
				$preview = new Element
				(
					'img', array
					(
						'src' => $node->thumbnail('$icon'),
						'width' => Module\Images::ICON_WIDTH,
						'height' => Module\Images::ICON_HEIGHT,
						'alt' => $node->alt
					)
				);
			}
		}

		$title = wd_entities($title);
		$links = empty($links) ? '' : (' &ndash; ' . implode(', ', $links));

		if ($extension)
		{
			$extension = '<span class="lighter">(' . $extension . ')</span>';
		}

		return <<<EOT
<li>
	<span class="handle">↕</span>$preview<input type="text" name="nodes_attachments[$i][title]" value="$title" />
	<span class="small">
		<span class="info light">$size $extension</span> $links
	</span>

	$hiddens
</li>
EOT;
	}
}