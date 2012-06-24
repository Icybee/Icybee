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

	public function __construct($model='files')
	{
		parent::__construct($model);
	}

	protected function get_extension()
	{
		/*DIRTY
		$path = $this->path;

		return substr($path, strrpos($path, '.'));
		*/

		return '.' . pathinfo($this->path, PATHINFO_EXTENSION);
	}

	public function url($type='view')
	{
		if ($type == 'download')
		{
			return ($this->siteid ? $this->site->path : '') . '/api/' . $this->constructor . '/' . $this->nid . '/download';
		}

		return parent::url($type);
	}
}