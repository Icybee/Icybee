<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Files;

use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Operation;

/**
 * Downloads a file.
 *
 * The file transfert is handled by PHP, the location of the file is not be revealed.
 *
 * Offline files cannot be downloaded by visitors.
 */
class Download extends Operation
{
	/**
	 * Controls for the operation: record.
	 *
	 * @see ICanBoogie.Operation::__get_controls()
	 */
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_RECORD => true
		)

		+ parent::__get_controls();
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

	protected function validate()
	{
		return true;
	}

	protected function process()
	{
		$record = $this->record;

		// TODO-20090512: Implement Accept-Range

		$filename = $record->title . $record->extension;
		$filename = strtr($filename, '"', '');

		#
		# http://tools.ietf.org/html/rfc2183
		#

		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
		{
			$filename = wd_remove_accents($filename);
		}

		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Type: ' . $record->mime);
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '. $record->size);
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: public');

		$fh = fopen($_SERVER['DOCUMENT_ROOT'] . $record->path, 'rb');

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

		exit;
	}
}