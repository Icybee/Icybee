<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\ActiveRecord\Site;
use ICanBoogie\Exception;
use ICanBoogie\Event;
use ICanBoogie\I18n;
use ICanBoogie\Object;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge;
use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\SplitButton;

use Icybee\Operation\Module\Config as ConfigOperation;

/**
 * Extends the Module class with the following features:
 *
 * - Special handling for the 'edit', 'new' and 'configure' blocks.
 * - Inter-users edit lock on records.
 */
class Module extends \ICanBoogie\Module
{
	const OPERATION_CONFIG = 'config';

	/**
	 * Fires the "alter_views" event with the module as sender:
	 *
	 * - &views: The views to alter.
	 *
	 * @param array $params
	 *
	 * @return Event|null
	 */
	protected function fire_alter_views(array $params)
	{
		return Event::fire('alter_views', $params, $this);
	}

	/**
	 * Fires the "alter_views" event before the value of the `views` property is returned.
	 *
	 * @see ICanBoogie.Object::__get()
	 */
	public function __get($property)
	{
		$rc = parent::__get($property);

		if ($property === 'views')
		{
			$this->fire_alter_views(array('views' => &$rc));
		}

		return $rc;
	}

	/**
	 * Returns the views defined by the module.
	 *
	 * Each _key/value_ pair defines a view, _key_ is its type, _value_ its definition:
	 *
	 * - (string) title: Title of the view. The title of the view is localized use the
	 * "<module_flat_id>.view" scope.
	 *
	 * @return array[string]array
	 */
	protected function __get_views()
	{
		return array();
	}

	public function getBlock($name)
	{
		global $core;

		$args = func_get_args();

		$class_name = $this->resolve_block_class($name);

		if ($class_name)
		{
			\ICanBoogie\log_info("Block instanciated from <q>$class_name</q>.");

			array_shift($args);

			$block = new $class_name($this, $args);

			I18n::push_scope($module->flat_id . '.edit');

			$rendered_block = (string) $block;

			I18n::pop_scope();

			return $rendered_block;
		}

		switch ($name)
		{
			case 'manage':
			{
				$permission = $core->user->has_permission(Module::PERMISSION_ACCESS, $this);

				if (!$permission)
				{
					#
					# The user don't have the permission to acces this block, we redirect him to
					# the dashboard.
					#

					throw new Exception\HTTP("You don't have permission to access the block %name.", array('%name' => $name), 403);
				}
			}
			break;

			case 'edit':
			{
				return $this->handle_block_edit(isset($args[1]) ? $args[1] : null);
			}
			break;

			case 'config':
			{
				return $this->handle_block_config();
			}
			break;
		}

		return call_user_func_array((PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 2)) ? 'parent::' . __FUNCTION__ : array($this, 'parent::' . __FUNCTION__), $args);
	}

	protected function resolve_block_class($name)
	{
		$module = $this;
		$class_name = wd_camelize('-' . $name) . 'Block';

		while ($module)
		{
			$try = $module->descriptor[self::T_NAMESPACE] . '\\' . $class_name;

			if (class_exists($try, true))
			{
				return $try;
			}

			$module = $module->parent;
		}
	}

	private function handle_block_edit($key)
	{
		global $core, $document;

		$entry = null;
		$record = null;
		$url = null;
		$properties = array();

		$document->js->add(ASSETS . 'js/edit.js');

		$permission = $core->user->has_permission(Module::PERMISSION_CREATE, $this);

		if ($key)
		{
			$entry = $record = $this->model[$key];

			#
			# check user ownership
			#

			if (isset($record->uid))
			{
				$permission = $core->user->has_ownership($this, $record);
			}
		}

		if (!$key && !$permission)
		{
			throw new Exception\HTTP("You don't have permission to create entries in the %id module.", array('%id' => $this->id), 403);
		}

		#
		# Records that belong to a site can only be edited on that site, thus we need to change
		# site if the current site if not the one associated with the record.
		#

		if ($record && !($record instanceof Site) && !empty($record->siteid) && $record->siteid != $core->site_id)
		{
			$url = $core->models['sites'][$record->siteid]->url;

			header("Location: $url/admin/$this->id/$key/edit");

			exit;
		}

		#
		# Initialize properties from the record and the request params.
		#
		# All values missing from the schema are defined as null.
		#

		$nulls = array();
		$schema = $this->model->extended_schema;

		if ($schema)
		{
			$nulls = array_fill_keys(array_keys($schema['fields']), null);
		}

		$properties = array_merge($nulls, $record ? get_object_vars($record) : array(), $core->request->params);

		#
		#
		#

		I18n::push_scope($this->flat_id . '.edit');

		#
		# get save mode used for this module
		#

		$mode = isset($core->session->wdpmodule[SaveOperation::MODE][$this->id]) ? $core->session->wdpmodule[SaveOperation::MODE][$this->id] : SaveOperation::MODE_LIST;

		$save_mode_options = array
		(
			SaveOperation::MODE_LIST => t('save_mode_list', array(), array('scope' => 'option')),
			SaveOperation::MODE_CONTINUE => t('save_mode_continue', array(), array('scope' => 'option')),
			SaveOperation::MODE_NEW => t('save_mode_new', array(), array('scope' => 'option')),
		);

		if ($record instanceof Object && $record->has_property('url'))
		{
			$url = $record->url;

			if ($url)
			{
				$save_mode_options[\ICanBoogie\Modules\Nodes\SaveOperation::MODE_DISPLAY] = t('save_mode_display', array(), array('scope' => 'option'));
			}
		}

		if (empty($save_mode_options[$mode]))
		{
			$mode = key($save_mode_options);
		}

		#
		#
		#

		$tags = wd_array_merge_recursive
		(
			array
			(
				Form::ACTIONS => !$permission ? array() : array
				(
					SaveOperation::MODE => new Element
					(
						Element::TYPE_RADIO_GROUP, array
						(
							Element::GROUP => 'save',
							Element::OPTIONS => $save_mode_options,

							'value' => $mode,
							'class' => 'inputs-list save-mode'
						)
					),

					new Button
					(
						'Save', array
						(
							Element::GROUP => 'save',

							'class' => 'btn-primary',
							'type' => 'submit'
						)
					)
				),

				Form::DISABLED => !$permission,
				Form::HIDDENS => array
				(
					Operation::DESTINATION => $this->id,
					Operation::NAME => 'save',
					Operation::KEY => $key
				),

				Form::RENDERER => new \Brickrouge\Renderer\Simple(array(\Brickrouge\Renderer\Simple::GROUP_CLASS => 'Icybee\Element\Group')),
				Form::VALUES => &$properties,

				Element::GROUPS => array
				(
					'primary' => array
					(

					),

					'admin' => array
					(
						'title' => 'admin',
						'weight' => 900
					),

					'save' => array
					(
						'weight' => 1000,
						'no-panels' => true
					)
				),

				// TODO-20091228: create an element for this lovely submit-save-mode-combo

				Element::CHILDREN => array(),

				'id' => 'editor',
				'action' => '',
				'class' => 'form-primary edit',
				'name' => (string) $this
			),

			$this->block_edit($properties, $permission)
		);

		#
		# alterators
		#

		// FIXME: permission won't get updated !!

		Event::fire
		(
			'alter.block.edit', array
			(
				'tags' => &$tags,
				'key' => $key,
				'entry' => $entry,
				'properties' => &$properties,
				'permission' => &$permission
			),

			$this
		);

		#
		#
		#

		$form = new Form($tags);

		$form->save();

		$form = (string) $form;

		I18n::pop_scope();

		$record = $entry;
		$module = $this;

		Event::add
		(
			'Icybee\Admin\Element\ActionbarToolbar::alter_buttons', function(Event $event, \Icybee\Admin\Element\ActionbarToolbar $sender) use($record, $module, $key, $save_mode_options, $mode)
			{
				global $core;

				if ($record instanceof ICanBoogie\ActiveRecord\Node && $record->url[0] != '#')
				{
					$event->buttons[] = '<a href="' . $record->url . '" class="actionbar-link">' . t('label.display') . '</a>';
				}

				if ($key && $core->user->has_permission(Module::PERMISSION_MANAGE, $module))
				{
					$event->buttons[] = new A
					(
						'Delete', Route::contextualize('/admin/' . $module . '/' . $key . '/delete'), array
						(
							'class' => 'btn btn-danger'
						)
					);
				}

				$event->buttons[] = new SplitButton
				(
					$save_mode_options[$mode], array
					(
						Element::OPTIONS => $save_mode_options,

						'value' => $mode,
						'class' => 'btn-primary record-save-mode'
					)
				);
			}
		);

		return $form;
	}

	protected function handle_block_config()
	{
		global $core;

		if (!$core->user->has_permission(Module::PERMISSION_ADMINISTER, $this))
		{
			throw new Exception\HTTP("You don't have permission to administer the %id module.", array('%id' => $this->id), 403);
		}

		I18n::push_scope(array($this->flat_id, 'config'));

		$tags = wd_array_merge_recursive
		(
			array
			(
				Form::ACTIONS => array
				(
					new Button
					(
						'Save', array
						(
							'class' => 'btn-primary',
							'type' => 'submit'
						)
					)
				),

				Form::HIDDENS => array
				(
					Operation::DESTINATION => $this->id,
					Operation::NAME => self::OPERATION_CONFIG
				),

				Form::RENDERER => new \Brickrouge\Renderer\Simple(array(\Brickrouge\Renderer\Simple::GROUP_CLASS => 'Icybee\Element\Group')),
				Form::VALUES => array(),

				Element::CHILDREN => array(),
				Element::GROUPS => array
				(
					'primary' => array
					(
// 						'title' => 'primary'
					)
				),

				'class' => 'form-primary config edit',
				'name' => (string) $this
			),

			$this->block_config($this->flat_id)
		);

		Event::fire
		(
			'alter.block.config', array
			(
				'tags' => &$tags,
				'target' => $this
			),

			$this
		);

		$form = new Form($tags);

		$registry = $core->registry;
		$local = $core->site->metas;
		$iterator = new \RecursiveIteratorIterator($form, \RecursiveIteratorIterator::SELF_FIRST);
		$values = array();

		foreach ($iterator as $element)
		{
			$name = $element['name'];

			if (!$name)
			{
				continue;
			}

			$dotted_name = strtr($name, array('[' => '.', ']' => ''));

//			wd_log("element: $name");

			$value = null;

			if (substr($dotted_name, 0, 6) == 'local.')
			{
				$value = $local[substr($dotted_name, 6)];
			}
			else if (substr($dotted_name, 0, 7) == 'global.')
			{
				$value = $registry[substr($dotted_name, 7)];
			}
			else
			{
				// COMPAT

				$value = $registry[$dotted_name];
			}

			if ($value === null)
			{
//				wd_log("$dotted_name := <em>null</em>");

				continue;
			}

//			wd_log("$dotted_name := !value", array('!value' => $value));

			$values[$name] = $value;
		}

		$config = array();

		$form->set(Form::VALUES, $form->get(Form::VALUES) + $values);

		$form->save();

		$form = (string) $form;

		I18n::pop_scope();

		return $form;
	}

	protected function block_delete($key)
	{
		try
		{
			$record = $this->model[$key];
		}
		catch (\Exception $e)
		{
			return '<div class="group--delete">' . t('Unknown record id: %key', array('%key' => $key)) . '</div>';
		}

		$form = (string) new Form
		(
			array
			(
				Form::HIDDENS => array
				(
					Operation::DESTINATION => $this,
					Operation::NAME => self::OPERATION_DELETE,
					Operation::KEY => $key,

					'#location' => Route::contextualize("/admin/{$this->id}")
				),

				Element::CHILDREN => array
				(
					new Button
					(
						'Delete', array
						(
							'class' => 'btn-danger',
							'type' => 'submit'
						)
					)
				)
			)
		);

		return <<<EOT
<div class="block--delete">
<h3>Delete a record</h3>
<p>Are you sure you want to delete this record?</p>
$form
</div>
EOT;
	}

	protected function block_config()
	{
		return array();
	}

	private function create_activerecord_lock_name($key)
	{
		return "activerecord_locks.$this->flat_id.$key";
	}

	/**
	 * Locks an activerecord.
	 *
	 * @param int $key
	 *
	 * @throws Exception
	 * @return array|false
	 */
	public function lock_entry($key, &$lock=null)
	{
		global $core;

		$user_id = $core->user_id;

		if (!$user_id)
		{
			throw new Exception('Guest users cannot lock records');
		}

		if (!$key)
		{
			throw new Exception('There is no key baby');
		}

		#
		# is the node already locked by another user ?
		#
		$registry = $core->registry;

		$lock_name = $this->create_activerecord_lock_name($key);
		$lock = json_decode($registry[$lock_name], true);
		$lock_uid = $user_id;
		$lock_until = null;

		$now = time();
		$until = date('Y-m-d H:i:s', $now + 2 * 60);

		if ($lock)
		{
			$lock_uid = $lock['uid'];
			$lock_until = $lock['until'];

			if ($now > strtotime($lock_until))
			{
				#
				# Because the lock has expired we can claim it.
				#

				$lock_uid = $user_id;
			}
			else if ($lock_uid != $user_id)
			{
				return false;
			}
		}

		$lock = array
		(
			'uid' => $lock_uid,
			'until' => $until
		);

		$registry[$lock_name] = json_encode($lock);

		return true;
	}

	public function unlock_entry($key)
	{
		global $core;

		$registry = $core->registry;

		$lock_name = $this->create_activerecord_lock_name($key);
		$lock = json_decode($registry[$lock_name], true);

		if (!$lock)
		{
			return;
		}

		if ($lock['uid'] != $core->user_id)
		{
			return false;
		}

		unset($registry[$lock_name]);

		return true;
	}
}