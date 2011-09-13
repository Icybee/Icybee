<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use ICanBoogie;
use ICanBoogie\ActiveRecord\File;
use ICanBoogie\Uploaded;

use BrickRouge\Element;
use BrickRouge\Form;

use Icybee\Manager;

class Files extends Nodes
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

	public function install()
	{
		global $core;

		$root = ICanBoogie\DOCUMENT_ROOT;

		$path = $core->config['repository.temp'];

		if (!$path)
		{
			throw new Exception('The %var var is empty is core config', array('%var' => 'repository.temp'));
		}

		$path = $root . $path;

		if (!file_exists($path))
		{
			$parent = dirname($path);

			if (!is_writable($parent))
			{
				throw new Exception('Unable to create %directory directory, its parent is not writtable', array('%directory' => $path));
			}

			mkdir($path);

			file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'Deny from all');
		}

		$path = $core->config['repository.files'];

		if (!$path)
		{
			throw new Exception('The %var var is empty is core config', array('%var' => 'repository.files'));
		}

		$path = $root . $path;

		if (!file_exists($path))
		{
			$parent = dirname($path);

			if (!is_writable($parent))
			{
				throw new Exception('Unable to create %directory directory, its parent is not writtable', array('%directory' => $path));
			}

			mkdir($path);

			file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'Allow from all');
		}

		return parent::install();
	}

	public function is_installed()
	{
		global $core;

		$root = ICanBoogie\DOCUMENT_ROOT;

		if (!is_dir($root . $core->config['repository.temp']))
		{
			return false;
		}

		if (!is_dir($root . $core->config['repository.files']))
		{
			return false;
		}

		return parent::is_installed();
	}

	/* FIXME-20112307: OBSOLETE

	protected function validate_operation_uploadResponse(WdOperation $operation)
	{
		global $core;

		$core->session;

		$id = $operation->params['uploadId'];
		$key = self::SESSION_UPLOAD_RESPONSE;

		if (empty($_SESSION[$key][$id]))
		{
			return false;
		}

		$operation->upload = $_SESSION[$key][$id];
		$count = count($_SESSION[$key]);

		if ($count > 10)
		{
			$_SESSION[$key] = array_splice($_SESSION[$key], $count - 10);
		}

		return true;
	}

	protected function operation_uploadResponse(WdOperation $operation, array $options=array())
	{
		$operation->terminus = true;

		$options += array
		(
			self::UPLOADER_CLASS => $this->uploader_class
		);

		$class = $options[self::UPLOADER_CLASS];
		$upload = $operation->upload;

		return array
		(
			'element' => (string) new $class
			(
				array
				(
					Element::T_FILE_WITH_LIMIT => true,

					'name' => isset($_GET['name']) ? $_GET['name'] : File::PATH,
					'value' => $upload['path']
				)
			),

			'title' => $upload['name'],
			'fields' => $upload['fields']
		);
	}

	*/

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
			Element::T_CHILDREN => array
			(
				"local[$this->flat_id.max_file_size]" => new Element
				(
					Element::E_TEXT, array
					(
						Form::T_LABEL => '.max_file_size',
						Element::T_LABEL => 'Ko', // TODO-20110206: use conventions
						Element::T_GROUP => 'primary',
						Element::T_DEFAULT => 16000,

						'size' => 6,
						'style' => 'text-align: right'
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
				Element::T_CHILDREN => array
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
				Form::T_HIDDENS => array
				(
					File::PATH => $uploaded_path,
					File::MIME => $uploaded_mime,

					self::UPLOADED => $uploaded_path
				),

				Form::T_VALUES => $values,

				Element::T_CHILDREN => array
				(
					File::PATH => new $uploader_class
					(
						array
						(
							Form::T_LABEL => '.file',
							Element::T_REQUIRED => empty($entry_nid),
							Element::T_FILE_WITH_LIMIT => $core->site->metas[$this->flat_id . '.max_file_size'],
							Element::T_WEIGHT => -100
						)
					),

					/*
					File::PATH => new Element
					(
						Element::E_FILE, array
						(
							Form::T_LABEL => '.file',
							Element::T_REQUIRED => empty($entry_nid),
							Element::T_FILE_WITH_REMINDER => true,
							Element::T_FILE_WITH_LIMIT => $core->site->metas[$this->flat_id . '.max_file_size'],
							Element::T_WEIGHT => -100
						)
					),
					*/

					File::DESCRIPTION => new \moo_WdEditorElement
					(
						array
						(
							Form::T_LABEL => '.description',
							Element::T_WEIGHT => 50,

							'rows' => 5
						)
					)
				)
			)
		);
	}

	protected function block_manage()
	{
		return new Manager\Files
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array('title', 'uid', 'mime', 'is_online', 'size', 'modified')
			)
		);
	}
}