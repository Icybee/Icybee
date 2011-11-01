<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use BrickRouge\Element;
use ICanBoogie\Uploaded;

class WdFileUploadElement extends BrickRouge\File
{
	protected function infos()
	{
		$path = $this->get('value');
		$details = $this->details($path);
		$preview = $this->preview($path);

		$rc = '';

		if ($preview)
		{
			$rc .= '<div class="preview">';
			$rc .= $preview;
			$rc .= '</div>';
		}

		if ($details)
		{
			$rc .= '<ul class="details">';

			foreach ($details as $detail)
			{
				$rc .= '<li>' . $detail . '</li>';
			}

			$rc .= '</ul>';
		}

		return $rc;
	}

	protected function preview($path)
	{
		$rc = '<a class="download" href="' . $path . '">' . t('download', array(), array('scope' => array('fileupload', 'element'))) . '</a>';

		return $rc;
	}

	protected function options()
	{
		global $document;

		$limit = $this->get(self::T_FILE_WITH_LIMIT, 2 * 1024);

		if ($limit === true)
		{
			$limit = ini_get('upload_max_filesize') * 1024;
		}

		return array
		(
			'name' => $this->get('name'),
			'max-file-size' => $limit * 1024
		);
	}

}