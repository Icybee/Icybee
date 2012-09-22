<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Pages;

use Icybee\Modules\Pages\Page;
use ICanBoogie\Event;
use ICanBoogie\Route;

class SaveOperation extends \ICanBoogie\Modules\Nodes\SaveOperation
{
	protected function get_properties()
	{
		global $core;

		$properties = parent::get_properties();

		if (!$this->key)
		{
			$siteid = $core->site_id;
			$properties[Page::SITEID] = $siteid;

			if (empty($properties[Page::WEIGHT]))
			{
				$weight = $this->module->model
				->where('siteid = ? AND parentid = ?', $siteid, isset($properties[Page::PARENTID]) ? $properties[Page::PARENTID] : 0)
				->maximum('weight');

				$properties[Page::WEIGHT] = ($weight === null) ? 0 : $weight + 1;
			}
		}

		return $properties;
	}

	protected function validate(\ICanBoogie\Errors $errors)
	{
		$contents = $this->request['contents'];
		$editors = $this->request['editors'];

		if ($contents)
		{
			foreach ($contents as $name => $dummy)
			{
				if (!array_key_exists($name, $editors))
				{
					$errors['content'][] = \ICanBoogie\format('The editor is missing for the content %name', array('name' => $name));
				}
			}
		}

		return parent::validate($errors);
	}

	protected function process()
	{
		global $core;

		$record = null;
		$oldurl = null;

		if ($this->record)
		{
			$record = $this->record;
			$pattern = $record->url_pattern;

			if (!Route::is_pattern($pattern))
			{
				$oldurl = $pattern;
			}
		}

		$rc = parent::process();
		$nid = $rc['key'];

		#
		# update contents
		#

		$preserve = array();
		$contents_model = $this->module->model('contents');

		$contents = $this->request['contents'];
		$editor_ids = $this->request['editors'];

		if ($contents && $editor_ids)
		{
			foreach ($contents as $content_id => $unserialized_content)
			{
				if (!$unserialized_content)
				{
					continue;
				}

				$editor_id = $editor_ids[$content_id];
				$editor = $core->editors[$editor_id];
				$content = $editor->serialize($unserialized_content);

				if (!$content)
				{
					continue;
				}

				$preserve[$content_id] = $content_id;

				$values = array
				(
					'content' => $content,
					'editor' => $editor_id
				);

				$contents_model->insert
				(
					array
					(
						'pageid' => $nid,
						'contentid' => $content_id
					)

					+ $values,

					array
					(
						'on duplicate' => $values
					)
				);
			}
		}

		#
		# we delete possible remaining content for the page
		#

		$arr = $contents_model->filter_by_pageid($nid);

		if ($preserve)
		{
			$arr->where(array('!contentid' => $preserve));
		}

		$arr->delete();

		if ($record && $oldurl)
		{
			$record = $this->module->model[$nid];
			$newurl = $record->url;

			//\ICanBoogie\log('oldurl: \1, newurl: \2', array($oldurl, $newurl));

			if ($oldurl != $newurl)
			{
// 				FIXME-20110710: the event should be 'move' with the record as sender.

				Event::fire
				(
					'urlchange', array
					(
						'from' => $oldurl,
						'to' => $newurl
					),

					$record
				);
			}
		}

		return $rc;
	}
}