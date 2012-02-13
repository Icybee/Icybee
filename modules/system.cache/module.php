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

use Brickrouge\Element;
use Brickrouge\Text;
use ICanBoogie\Event;

class Module extends \Icybee\Module
{
	protected function block_manage()
	{
		global $core, $document;

		$document->css->add(\Icybee\ASSETS . 'css/manage.css');
		$document->css->add('public/admin.css');
		$document->js->add('public/admin.js');

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

						'title' => "Cliquez pour activer ou désactiver le cache",
						'class' => 'checkbox-wrapper circle' . ($checked ? ' checked': '')
					)
				);

				$title = wd_entities($cache->title);
				$description = $cache->description;

				$config_preview = $cache->config_preview;

				if ($config_preview)
				{
					$config_preview = '<button title="Configurer le cache" class="spinner btn-info">' . $config_preview . '</button>';
				}
				else
				{
					$config_preview = '&nbsp;';
				}

				list($n, $stat) = $cache->stat();

				$usage_empty = $n ? '' : 'empty';

				$rows .= <<<EOT
<tr data-cache-id="$cache_id">
	<td class="state">$checkbox</td>
	<td class="title">$title<div class="element-description">$description</div></td>
	<td class="limit config">$config_preview</td>
	<td class="usage {$usage_empty}">$stat</td>
	<td class="erase"><button type="button" class="btn-warning" name="clear">Vider</button></td>
</tr>
EOT;
			}
		}

		$rc = <<<EOT
<table class="manage" cellpadding="0" cellspacing="0" border="0" width="100%">
	<thead>
		<tr>
			<th><div>&nbsp;</div></th>
			<th><div>Type de cache</div></th>
			<th><div>Configuration</span></div></th>
			<th class="right"><div>Utilisation</div></th>
			<th><div>&nbsp;</div></th>
		</tr>
	</thead>

	<tbody>$rows</tbody>
</table>
EOT;

		/*
		$rc = new \Brickrouge\PopoverWidget
		(
			array
			(
// 				\Brickrouge\Popover::ANCHOR => '[data-cache-id="thumbnails"] td.erase button',
// 				\Brickrouge\Popover::ANCHOR => '[data-cache-id="contents.pages"] input',
				\Brickrouge\Popover::ANCHOR => '#menu li a',
				\Brickrouge\Popover::LEGEND => 'Configuration',
				\Brickrouge\Popover::INNER_HTML => new \feedback_comments_WdForm(),
				\Brickrouge\Popover::ACTIONS => 'boolean'
			)
		)

		. $rc;
		*/

		return $rc;
	}

	public static function get_files_stat($path, $pattern=null)
	{
		$root = \ICanBoogie\DOCUMENT_ROOT;

		if (!file_exists($path))
		{
			$path = $root . $path;
		}

		if (!file_exists($path))
		{
			mkdir($path, 0777, true);

			if (!file_exists($path))
			{
				return array
				(
					0, '<span class="warn">Impossible de créer le dossier&nbsp: <em>' . wd_strip_root($path) . '</em></span>'
				);
			}
		}

		if (!is_writable($path))
		{
			return array
			(
				0, '<span class="warn">Dossier vérouillé en écriture&nbsp: <em>' . wd_strip_root($path) . '</em></span>'
			);
		}

		$n = 0;
		$size = 0;

		$iterator = new \DirectoryIterator($path);

		if ($pattern)
		{
			$iterator = new \RegexIterator($iterator, $pattern);
		}

		foreach ($iterator as $file)
		{
			$filename = $file->getFilename();

			if ($filename{0} == '.')
			{
				continue;
			}

			++$n;
			$size += $file->getSize();
		}

		if (!$n)
		{
			return array(0, 'Le cache est vide');
		}

		return array
		(
			$n, $n . ' fichiers<br /><span class="small">' . wd_format_size($size) . '</span>'
		);
	}

	public static function get_vars_stat($regex)
	{
		global $core;

		$n = 0;
		$size = 0;

		foreach ($core->vars->matching($regex) as $pathname => $fileinfo)
		{
			++$n;
			$size += $fileinfo->getSize();
		}

		if (!$n)
		{
			return array(0, 'Le cache est vide');
		}

		return array
		(
			$n, $n . ' fichiers<br /><span class="small">' . wd_format_size($size) . '</span>'
		);
	}

	/**
	 * Deletes files in a directory according to a RegEx pattern.
	 *
	 * @param string $path Path to the directory where the files shoud be deleted.
	 * @param string|null $pattern RegEx pattern to delete matching files, or null to delete all
	 * files.
	 */
	public static function clear_files($path, $pattern=null)
	{
		$root = \ICanBoogie\DOCUMENT_ROOT;

		if (!is_dir($root . $path))
		{
			return false;
		}

		$n = 0;
		$dh = opendir($root . $path);

		while (($file = readdir($dh)) !== false)
		{
			if ($file{0} == '.' || ($pattern && !preg_match($pattern, $file)))
			{
				continue;
			}

			$n++;
			unlink($root . $path . '/' . $file);
		}

		return $n;
	}
}