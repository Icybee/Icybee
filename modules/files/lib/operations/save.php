<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Files;

use ICanBoogie\I18n\FormattedString;

use ICanBoogie\Uploaded;

class SaveOperation extends \Icybee\Modules\Nodes\SaveOperation
{
	/**
	 * @var Uploaded|bool The optional file to save with the record.
	 */
	protected $file;

	/**
	 * @var array Accepted file types.
	 */
	protected $accept;

	/**
	 * Unset the `mime` and `size` properties because they cannot be modified by the user. If the
	 * `file` property is defined, which is the case when an asynchronous upload happend, it is
	 * copied to the `path` property.
	 *
	 * @see Icybee\Modules\Nodes.SaveOperation::get_properties()
	 */
	protected function get_properties()
	{
		$properties = parent::get_properties();

		unset($properties[File::MIME]);
		unset($properties[File::SIZE]);

		#
		# TODO-20100624: Using the 'file' property might be the way to go
		#

		if (isset($properties['file']))
		{
			$properties[File::PATH] = $properties['file'];
		}

		#
		# File:PATH is set to true when the file is not mandatory and there is no uploaded file in
		# order fot the form still validates, in which case the property must be unset, otherwise
		# the boolean is used a the new path.
		#

		if (isset($properties[File::PATH]) && $properties[File::PATH] === true)
		{
			unset($properties[File::PATH]);
		}

		return $properties;
	}

	public function reset()
	{
		parent::reset();

		$this->module->clean_repository();
	}

	/**
	 * If PATH is not defined, we check for a file upload, which is required if the operation key
	 * is empty. If a file upload is found, the Uploaded object is set as the operation `file`
	 * property, and the PATH parameter of the operation is set to the file location.
	 *
	 * Note that if the upload is not required - because the operation key is defined for updating
	 * an entry - the PATH parameter of the operation is set to TRUE to avoid error reporting from
	 * the form validation.
	 *
	 * TODO: maybe this is not ideal, since the file upload should be made optionnal when the form
	 * is generated to edit existing entries.
	 *
	 * @see ICanBoogie.Operation::control()
	 */
	protected function control(array $controls)
	{
		global $core;

		$this->file = null;
		$request = $this->request;

		if (empty($request[File::PATH]))
		{
			$required = empty($this->key);
			$file = new Uploaded(File::PATH, $this->accept, $required);

			$this->file = $file;

			if ($file->location)
			{
				$path = $core->config['repository.temp'] . '/' . basename($file->location) . $file->extension;
				$file->move($_SERVER['DOCUMENT_ROOT'] . $path, true);

				$request[File::PATH] = $path;

				if (empty($request[File::TITLE]))
				{
					$request[File::TITLE] = $file->name;
				}
			}
			else if (!$required)
			{
				$request[File::PATH] = true;
			}
		}

		return parent::control($controls);
	}

	/**
	 * The method validates unless there was an error during the file upload.
	 *
	 * @see Icybee\Modules\Nodes.SaveOperation::validate()
	 */
	protected function validate(\ICanboogie\Errors $errors)
	{
		$file = $this->file;

		if ($file && $file->er)
		{
			$errors[File::PATH] = new FormattedString('Unable to upload file %file: :message.', array('%file' => $file->name, ':message' => $file->er_message));

			return false;
		}

		return parent::validate($errors);
	}

	/**
	 * Trigger a {@link File\MoveEvent} when the path of the updated record is modified.
	 *
	 * @see Icybee\Modules\Nodes.SaveOperation::process()
	 */
	protected function process()
	{
		$record = $this->record;
		$oldpath = $record ? $record->path : null;

		$rc = parent::process();

		if ($oldpath)
		{
			$newpath = $this->module->model->select('path')->filter_by_nid($rc['key'])->rc;

			if ($oldpath != $newpath)
			{
				new File\MoveEvent($record, $oldpath, $newpath);
			}
		}

		return $rc;
	}
}

namespace Icybee\Modules\Files\File;

/**
 * Event class for the `Icybee\Modules\Files\File` event.
 */
class MoveEvent extends \ICanBoogie\Event
{
	/**
	 * Previous path.
	 *
	 * @var string
	 */
	public $from;

	/**
	 * New path.
	 *
	 * @var string
	 */
	public $to;

	/**
	 * The event is constructed with the type `move`.
	 *
	 * @param \Icybee\Modules\Files\File $target
	 * @param string $from Previous path.
	 * @param string $to New path.
	 */
	public function __construct(\Icybee\Modules\Files\File $target, $from, $to)
	{
		parent::__construct($target, 'move', array('from' => $from, 'to' => $to));
	}
}