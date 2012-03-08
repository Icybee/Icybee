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
use ICanBoogie\Exception;
use ICanBoogie\Uploaded;

class Model extends \ICanBoogie\Modules\Nodes\Model
{
	const ACCEPT = '#files-accept';
	const UPLOADED = '#files-uploaded';

	public function save(array $properties, $key=null, array $options=array())
	{
		global $core;

		#
		# because the newly uploaded file might not overrite the previous file if there extensions
		# don't match, we use the $delete variable to delete the previous file. the variable
		# is defined after an upload.
		#

		$delete = null;

		#
		# $previous_title is used to check if the file has to been renamed.
		# It is set to the last value of the entry, or NULL if we are creating a
		# new one.
		#
		# If nedded, the file is renamed after the entry has been saved.
		#

		$title = null;

		$previous_title = null;
		$previous_path = null;

		if (isset($properties[File::TITLE]))
		{
			$title = $properties[File::TITLE];
		}

		#
		# If we are modifying an entry, we load its previous values to check for updates related
		# to the title.
		#

		if ($key)
		{
			#
			# load previous entry to check for changes
			#

			$previous = $this->select('title, path, mime')->find_by_nid($key)->one;

			#
			# extract previous to obtain previous_title, previous_path and previous_mime
			#

			extract($previous, EXTR_PREFIX_ALL, 'previous');

			$properties[File::MIME] = $previous_mime;
		}

		if (!empty($properties[File::PATH]))
		{
			#
			# Only the files located in the repository temporary folder can be saved. We need to
			# check if the file is actually in the repository temporary folder. The file is
			# required for new entries, so if the file is not defined here, the save process will
			# fail.
			#

			$root = $_SERVER['DOCUMENT_ROOT'];
			$file = basename($properties[File::PATH]);
			$path = $core->config['repository.temp'] . '/' . $file;

			if (is_file($root . $path))
			{
				$mime = Uploaded::getMIME($root . $path);
				$size = filesize($root . $path);

				$delete = $previous_path;

				$previous_path = $path;

				$properties[File::MIME] = $mime;
				$properties[File::SIZE] = $size;

				#
				# setting `previous_title` to null will force the update
				#

				$previous_title = null;

				#
				# setting the UPLOADED value in the options
				#

				if (array_key_exists(self::UPLOADED, $options))
				{
					$options[self::UPLOADED] = $file;
				}
			}
			else if (!$key)
			{
				throw new Exception('The file %file is not located in the repository temporary folder (%location)', array('%file' => $file, '%location' => $path));
			}
		}

		$file = null;

		#
		# before we continue, we have to check if we can actually move the file to the repository
		#

		$path = self::makePath($key, array('path' => $previous_path) + $properties);

		//wd_log('path: \1, preivous: \2', array($path, $previous_path));

		//wd_log('file: \1, values: \6 path: \2 ?= \3, title: \4 ?= \5, umask: \6 ', array($file, $previous_path, $path, $previous_title, $title, $properties, umask()));

		$root = $_SERVER['DOCUMENT_ROOT'];
		$parent = dirname($path);

		if (!is_dir($root . $parent))
		{
			mkdir($root . $parent, 0777, true);
		}

//		wd_log('path: \1', array($path));

		if (!is_writable($root . $parent))
		{
			throw new Exception('The directory %directory is not writable', array('%directory' => $parent));
		}

		$key = parent::save($properties, $key, $options);

		if (!$key)
		{
			return $key;
		}

		#
		# change path according to node's title
		#

//		wd_log("path: $previous_path ?= $path, title: $previous_title ?= $title");

		if (($path != $previous_path) || (!$previous_title || ($previous_title != $title)))
		{
			$path = self::makePath($key, array('path' => $previous_path) + $properties);

			//wd_log('previous_path: %previous_path, path: %path', array('%previous_path' => $previous_path, '%path' => $path));

			if ($delete && is_file($root . $delete))
			{
				unlink($root . $delete);
			}

			$ok = rename($root . $previous_path, $root . $path);

			if ($ok)
			{
				$this->update(array(File::PATH => $path), $key);
			}
			else
			{
				wd_log_error('Unable to rename %previous to %path', array('%previous' => $previous_path, '%path' => $path));
			}
		}

		return $key;
	}

	public function delete($key)
	{
		$path = $this->select('path')->find_by_nid($key)->rc;

		$rc = parent::delete($key);

		if ($rc && $path)
		{
			$root = $_SERVER['DOCUMENT_ROOT'];

			if (is_file($root . $path))
			{
				unlink($root . $path);
			}
		}

		return $rc;
	}

	static protected function makePath($key, array $properties)
	{
		global $core;
		//wd_log('makePath with: \1', array($properties));

		$rc = $core->config['repository.files'];

		$mime = $properties[File::MIME];

		$base = dirname($mime);

		if ($base == 'application')
		{
			$base = basename($mime);
		}

		if (!in_array($base, array('image', 'audio', 'pdf', 'zip')))
		{
			$base = 'bin';
		}

		$rc .= '/' . $base . '/' . ($key ? $key : 'temp') . '-' . wd_normalize($properties[File::TITLE]);

		#
		# append extension
		#

		if (isset($properties['extension']))
		{
			$extension = $properties['extension'];
		}
		else
		{
			$previous_path = $properties['path'];

			$pos = strrpos($previous_path, '.');

			$extension = $pos === false ? '.file' : substr($previous_path, $pos);
		}

		$rc .= $extension;

		//wd_log('path: \1', array($rc));

		return $rc;
	}
}
