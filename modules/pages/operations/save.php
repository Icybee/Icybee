<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

use ICanBoogie\ActiveRecord\Page;
use ICanBoogie\Event;
use ICanBoogie\Route;

class SaveOperation extends \ICanBoogie\Modules\Nodes\SaveOperation
{
	protected function __get_properties()
	{
		global $core;

		$properties = parent::__get_properties();

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

		$content_ids = array();
		$contents_model = $this->module->model('contents');

		if (isset($this->request['contents']))
		{
			$contents = $this->request['contents'];
			$content_ids = array_keys($contents);

			foreach ($contents as $content_id => $values)
			{
				$editor = $values['editor'];
				$editor_class = $editor . '_WdEditorElement';
				$content = call_user_func(array($editor_class, 'to_content'), $values, $content_id, $nid);

				#
				# if the content is made of an array of values, the values are serialized in JSON.
				#

				if (is_array($content))
				{
					$content = json_encode($content);
				}

				#
				# if there is no content, the content object is deleted
				#

				if (!$content)
				{
					$contents_model->where(array('pageid' => $nid, 'contentid' => $content_id))->delete();

					continue;
				}

				$values['content'] = $content;

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

		$arr = $contents_model->find_by_pageid($nid);

		if ($content_ids)
		{
			$arr->where(array('!contentid' => $content_ids));
		}

		$arr->delete();

		if ($record && $oldurl)
		{
			$record = $this->module->model[$nid];
			$newurl = $record->url;

			//wd_log('oldurl: \1, newurl: \2', array($oldurl, $newurl));

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