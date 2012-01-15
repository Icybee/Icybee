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

use ICanBoogie\Operation;
use ICanBoogie\Uploaded;

class UploadOperation extends Operation
{
	/**
	 * @var Uploaded The target file of the operation.
	 */
	protected $file;

	/**
	 * @var array Accepted file types.
	 */
	protected $accept;

	/**
	 * Controls for the operation: permission(create).
	 *
	 * @see ICanBoogie.Operation::__get_controls()
	 */
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_CREATE
		)

		+ parent::__get_controls();
	}

	public function reset()
	{
		parent::reset();

		$this->module->clean_repository();
	}

	/**
	 * Validates the operation if the file upload succeeded.
	 *
	 * @see ICanBoogie.Operation::validate()
	 */
	protected function validate(\ICanboogie\Errors $errors)
	{
		#
		# forces 'application/json' response type
		#

		$_SERVER['HTTP_ACCEPT'] = 'application/json';

		#
		# TODO-20100624: we use 'Filedata' because it's used by Swiff.Uploader. We need to change
		# that as soon as possible.
		#

		$file = new Uploaded('Filedata', $this->accept, true);

		$this->file = $file;
		$this->response['file'] = $file;

		if ($file->er)
		{
			wd_log_error($file->er_message);

			return false;
		}

		return true;
	}

	/**
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		global $core;

		$file = $this->file;
		$path = $core->config['repository.temp'] . '/' . basename($file->location) . $file->extension;

		$file->move($_SERVER['DOCUMENT_ROOT'] . $path, true);

		$file->location = $path;
		$name = $file->name;

		$this->response['infos'] = null;
		$this->response['properties'] = array
		(
			'title' => $name
		);

		if (isset($_SERVER['HTTP_X_USING_FILE_API']))
		{
			$size = wd_format_size($file->size);

			$this->response['infos'] = <<<EOT
<ul class="details">
	<li><span title="Path: {$file->location}">{$name}</span></li>
	<li>$file->mime</li>
	<li>$size</li>
</ul>
EOT;

			return true;
		}

		#
		# Damn you Flash !!
		#

		$core->session;

		$id = uniqid();

		$_SESSION[Module::SESSION_UPLOAD_RESPONSE][$id] = array
		(
			'name' => $name,
			'path' => $path,
			'fields' => array
			(
				'title' => $file->name
			)
		);

		return $id;
	}
}