<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Files;

use ICanBoogie\ActiveRecord\File;
use ICanBoogie\Operation;
use ICanBoogie\Uploaded;

use Brickrouge\Element;
use Brickrouge\Form;

class EditBlock extends \ICanBoogie\Modules\Nodes\EditBlock
{
	const ACCEPT = '#files-accept';
	const UPLOADED = '#files-uploaded';
	const UPLOADER_CLASS = 'uploader class';

	protected $accept = null;
	protected $uploader_class = 'WdFileUploadElement';

	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		global $core;

		$folder = $core->config['repository.temp'];

		if (!is_writable($_SERVER['DOCUMENT_ROOT'] . $folder))
		{
			return array
			(
				Element::CHILDREN => array
				(
					t('The folder %folder is not writable !', array('%folder' => $folder))
				)
			);
		}

		$core->document->css->add('../../public/edit.css');
		$core->document->js->add('../../public/edit.js');

		#
		# options
		#

		$options = array
		(
			self::ACCEPT => $this->accept,
			self::UPLOADER_CLASS => $this->uploader_class
		);

		$accept = $options[self::ACCEPT];
		$uploader_class = $options[self::UPLOADER_CLASS];

		#
		# UPLOADED is set when the file has already been updated
		# and is available on our host
		#

		$values = array();
		$properties += array
		(
			File::NID => null,
			File::PATH => null,
			self::UPLOADED => null
		);

		$entry_nid = $properties[File::NID];
		$entry_path = $properties[File::PATH];

		$uploaded_path = $properties[self::UPLOADED];
		$uploaded_mime = null;

		#
		# check uploaded file
		#

		$file = new Uploaded(File::PATH, $accept);

		if ($file->location)
		{
			$values[File::TITLE] = $file->name;

			$uploaded_mime = $file->mime;
			$uploaded_path = $core->config['repository.temp'] . '/' . basename($file->location) . $file->extension;

			$file->move(\ICanBoogie\DOCUMENT_ROOT . $uploaded_path, true);

			if (array_key_exists(self::UPLOADED, $options))
			{
				$options[self::UPLOADED] = $file;
			}
		}

		// FIXME: now that we use a flash uploader, will the PATH defined in HIDDENS be a problem ?

		$values[File::PATH] = $uploaded_path ? $uploaded_path : $entry_path;

		#
		# elements
		#

		$attributes = wd_array_merge_recursive
		(
			$attributes, array
			(
				Form::HIDDENS => array
				(
					File::PATH => $uploaded_path,
					File::MIME => $uploaded_mime,

					self::UPLOADED => $uploaded_path
				),

				Form::VALUES => $values
			)
		);

		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				File::PATH => new $uploader_class
				(
					array
					(
						Form::LABEL => 'file',
						Element::REQUIRED => empty($entry_nid),
						Element::FILE_WITH_LIMIT => $core->site->metas[$this->module->flat_id . '.max_file_size'],
						Element::WEIGHT => -100,
						\Brickrouge\File::T_UPLOAD_URL => Operation::encode($this->module->id . '/upload')
					)
				),

				File::DESCRIPTION => new \moo_WdEditorElement
				(
					array
					(
						Form::LABEL => 'description',
						Element::WEIGHT => 50,

						'rows' => 5
					)
				)
			)
		);
	}
}