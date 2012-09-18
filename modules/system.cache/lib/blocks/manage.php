<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Cache;

use Brickrouge\Button;

use Brickrouge\Element;

class ManageBlock extends Element
{
	protected $module;

	public function __construct(Module $module, array $attributes=array())
	{
		$this->module = $module;

		parent::__construct
		(
			'table', $attributes + array
			(
				'class' => 'manage',
				'cellpadding' => 0,
				'cellspacing' => 0,
				'border' => 0,
				'width' => "100%"
			)
		);
	}

	public static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add(\Icybee\ASSETS . 'css/manage.css');
		$document->css->add('../../public/admin.css');
		$document->js->add('../../public/admin.js');
	}

	protected function render_inner_html()
	{
		global $core;

		$groups = array();
		$caches = new Collection();

		foreach ($caches as $cache_id => $cache)
		{
			$section_title = t(ucfirst($cache->group), array(), array('scope' => 'cache.section'));
			$groups[$section_title][$cache_id] = $cache;
		}

		$rows = '';

		foreach ($groups as $group_title => $group)
		{
			$rows .= <<<EOT
<tr class="section-title">
	<td>&nbsp;</td>
	<td>$group_title</td>
	<td colspan="3">&nbsp;</td>
</tr>
EOT;

			foreach ($group as $cache_id => $cache)
			{
				$checked = $cache->state;

				$checkbox = new Element
				(
					'label', array
					(
						Element::CHILDREN => array
						(
							new Element
							(
								Element::TYPE_CHECKBOX, array
								(
									'checked' => $checked,
									'disabled' => $cache->state === null,
									'name' => $cache_id
								)
							)
						),

						'title' => "Enable/disable the cache",
						'class' => 'checkbox-wrapper circle' . ($checked ? ' checked': '')
					)
				);

				$title = wd_entities($cache->title);
				$description = $cache->description;

				$config_preview = $cache->config_preview;

				if ($config_preview)
				{
					$config_preview = '<a title="Configure the cache" class="spinner">' . $config_preview . '</a>';
				}
				else
				{
					$config_preview = '&nbsp;';
				}

				list($n, $stat) = $cache->stat();

				$usage_empty = $n ? '' : 'empty';

				$button = new Button('Clear', array('class' => 'btn-warning', 'name' => 'clear'));

				$rows .= <<<EOT
<tr data-cache-id="$cache_id">
	<td class="state">$checkbox</td>
	<td class="title">$title<div class="element-description">$description</div></td>
	<td class="limit config">$config_preview</td>
	<td class="usage {$usage_empty}">$stat</td>
	<td class="cell--erase">{$button}</td>
</tr>
EOT;
			}
		}

		$rc = <<<EOT
	<thead>
		<tr>
			<th><div>&nbsp;</div></th>
			<th><div>Cache type</div></th>
			<th><div>Configuration</span></div></th>
			<th class="right"><div>Usage</div></th>
			<th><div>&nbsp;</div></th>
		</tr>
	</thead>

	<tbody>$rows</tbody>
EOT;

		return $rc;
	}
}