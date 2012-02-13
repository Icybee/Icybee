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

use ICanBoogie;
use ICanBoogie\ActiveRecord\File;
use ICanBoogie\Uploaded;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Module extends \ICanBoogie\Modules\Nodes\Module
{
	const OPERATION_UPLOAD = 'upload';
	const OPERATION_UPLOAD_RESPONSE = 'uploadResponse';

	const ACCEPT = '#files-accept';
	const UPLOADED = '#files-uploaded';
	const UPLOADER_CLASS = 'uploader class';

	const SESSION_UPLOAD_RESPONSE = 'resources.files.upload.responses';

	static protected $repository = array();

	static protected function repository($name)
	{
		global $core;

		if (empty(self::$repository[$name]))
		{
			self::$repository[$name] = $core->config['repository'] . '/' . $name . '/';
		}

		return self::$repository[$name];
	}

	protected $accept = null;
	protected $uploader_class = 'WdFileUploadElement';

	/**
	 * Overrides the method to create the "/repository/tmp/" and "/repository/files/" directories,
	 * and add a ".htaccess" file in the "/repository/tmp/" direcotry which denies all access and
	 * a ".htaccess" file in the "/repository/files/" directory which allows all access.
	 *
	 * @see ICanBoogie.Module::install()
	 */
	public function install(\ICanBoogie\Errors $errors)
	{
		global $core;

		$root = ICanBoogie\DOCUMENT_ROOT;
		$path = $core->config['repository.temp'];

		if ($path)
		{
			$path = $root . $path;

			if (!file_exists($path))
			{
				$parent = dirname($path);

				if (is_writable($parent))
				{
					mkdir($path);

					file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'Deny from all');
				}
				else
				{
					$errors[$this->id] = t('Unable to create %directory directory, its parent is not writtable', array('%directory' => $path));
				}
			}
		}
		else
		{
			$errors[$this->id] = t('The %var var is empty is core config', array('%var' => 'repository.temp'));
		}

		$path = $core->config['repository.files'];

		if ($path)
		{
			$path = $root . $path;

			if (!file_exists($path))
			{
				$parent = dirname($path);

				if (is_writable($parent))
				{
					mkdir($path);

					file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'Allow from all');
				}
				else
				{
					$errors[$this->id] = t('Unable to create %directory directory, its parent is not writtable', array('%directory' => $path));
				}
			}
		}
		else
		{
			$errors[$this->id] = t('The %var var is empty is core config', array('%var' => 'repository.files'));
		}

		return parent::install($errors);
	}

	/**
	 * Overrides the method to check if the "tmp" and "files" directories exist in the repository.
	 *
	 * @see ICanBoogie.Module::is_installed()
	 */
	public function is_installed(\ICanBoogie\Errors $errors)
	{
		global $core;

		$root = ICanBoogie\DOCUMENT_ROOT;
		$path = $core->config['repository.temp'];

		if (!is_dir($root . $path))
		{
			$errors[$this->id] = t('The %directory directory is missing.', array('%directory' => $path));
		}

		$path = $core->config['repository.files'];

		if (!is_dir($root . $path))
		{
			$errors[$this->id] = t('The %directory directory is missing.', array('%directory' => $path));
		}

		return parent::is_installed($errors);
	}

	public function clean_repository($repository=':repository.temp', $lifetime=3600)
	{
		global $core;

		$root = $_SERVER['DOCUMENT_ROOT'];

		if ($repository{0} == ':')
		{
			$repository = $core->config[substr($repository, 1)];
		}

		if (!is_dir($root . $repository))
		{
			wd_log_error('The directory %directory does not exists', array('%directory' => $repository));

			return;
		}

		if (!is_writable($root . $repository))
		{
			wd_log_error('The directory %directory is not writtable', array('%directory' => $repository));

			return;
		}

		$dh = opendir($root . $repository);

		if (!$dh)
		{
			return;
		}

		$now = time();
		$location = getcwd();

		chdir($root . $repository);

		while ($file = readdir($dh))
		{
			if ($file{0} == '.')
			{
				continue;
			}

			$stat = stat($file);

			if ($now - $stat['ctime'] > $lifetime)
			{
				unlink($file);

				wd_log
				(
					'The temporary file %file has been deleted form the repository %directory', array
					(
						'%file' => $file,
						'%directory' => $repository
					)
				);
			}
		}

		chdir($location);

		closedir($dh);
	}

	/*
	**

	BLOCKS

	**
	*/

	protected function block_config()
	{
		return array
		(
			Element::CHILDREN => array
			(
				"local[$this->flat_id.max_file_size]" => new Text
				(
					array
					(
						Text::ADDON => 'Ko', // TODO-20110206: use conventions

						Form::LABEL => 'max_file_size',
						Element::DEFAULT_VALUE => 16000,

						'class' => 'measure',
						'size' => 6
					)
				)
			)
		);
	}

	protected function block_edit(array $properties, $permission, array $options=array())
	{
		global $core, $document;

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

		$document->css->add('public/edit.css');
		$document->js->add('public/edit.js');

		#
		# options
		#

		$options += array
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

			$file->move($_SERVER['DOCUMENT_ROOT'] . $uploaded_path, true);

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

		return wd_array_merge_recursive
		(
			parent::block_edit($properties, $permission), array
			(
				Form::HIDDENS => array
				(
					File::PATH => $uploaded_path,
					File::MIME => $uploaded_mime,

					self::UPLOADED => $uploaded_path
				),

				Form::VALUES => $values,

				Element::CHILDREN => array
				(
					File::PATH => new $uploader_class
					(
						array
						(
							Form::LABEL => 'file',
							Element::REQUIRED => empty($entry_nid),
							Element::FILE_WITH_LIMIT => $core->site->metas[$this->flat_id . '.max_file_size'],
							Element::WEIGHT => -100,
							\Brickrouge\File::T_UPLOAD_URL => '/api/' . $this->id . '/upload'
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
			)
		);
	}

	protected function block_manage()
	{
		return new Manager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array('title', 'uid', 'mime', 'is_online', 'size', 'modified')
			)
		);
	}
}