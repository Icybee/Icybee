<?php

/*
 * This file is part of the Icybee package.
*
* (c) Olivier Laviale <olivier.laviale@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace ICanBoogie\ActiveRecord;

class File extends Node
{
	const PATH = 'path';
	const MIME = 'mime';
	const SIZE = 'size';
	const DESCRIPTION = 'description';

	public $path;
	public $mime;
	public $size;
	public $description;

	protected function __get_extension()
	{
		$path = $this->path;

		return substr($path, strrpos($path, '.'));
	}

	protected function __get_download_url()
	{
		return '/api/' . $this->constructor . '/' . $this->nid . '/download';
	}

	public function url($type='view')
	{
		if ($type == 'download')
		{
			return $this->download_url;
		}

		return site_pages_view_WdHooks::url($this, $type);
	}
}