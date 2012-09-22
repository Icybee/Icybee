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

use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Operation;

/**
 * Downloads a file.
 *
 * The file transfert is handled by PHP, the location of the file is not be revealed.
 *
 * Offline files cannot be downloaded by visitors.
 */
class DownloadOperation extends Operation
{
	/**
	 * Controls for the operation: record.
	 *
	 * @see ICanBoogie.Operation::get_controls()
	 */
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_RECORD => true
		)

		+ parent::get_controls();
	}

	/**
	 * Overrides the method to check the availability of the record to the requesting user.
	 *
	 * @see ICanBoogie.Operation::control_record()
	 * @throws HTTPException with HTTP code 401, if the user is a guest and the record is
	 * offline.
	 */
	protected function control_record()
	{
		global $core;

		$record = parent::control_record();

		if ($core->user->is_guest && !$record->is_online)
		{
			throw new HTTPException
			(
				'The requested resource requires authentication: %resource', array
				(
					'%resource' => $record->constructor . '/' . $this->key
				),

				401
			);
		}

		return $record;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	protected function process()
	{
		$record = $this->record;
		$request = $this->request;
		$response = $this->response;

		// TODO-20090512: Implement Accept-Range

		$filename = $record->title . $record->extension;
		$filename = strtr($filename, '"', '');

		$response->headers['Content-Description'] = 'File Transfer';
		$response->headers['Content-Disposition'] = array('attachment', $filename);
		$response->headers['Content-Type'] = $record->mime;
		$response->headers['Content-Transfer-Encodin'] = 'binary';
		$response->headers['Content-Length'] = $record->size;

		return function() use ($record)
		{
			$fh = fopen(\ICanBoogie\DOCUMENT_ROOT . $record->path, 'rb');

			if ($fh)
		    {
				#
				# Reset time limit for big files
				#

		    	if (!ini_get('safe_mode'))
		    	{
					set_time_limit(0);
		    	}

				while (!feof($fh) && !connection_status())
				{
					echo fread($fh, 1024 * 8);

					#
					# flushing frees memory used by the PHP buffer
					#

					flush();
				}

				fclose($fh);
			}
		};
	}
}