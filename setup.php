<?php

/*
 * Sadly, the following code has not been updated since 2007, there is little chance that it is
 * still working because so many things have changed since. We will finish the installer once all
 * the beta testing has been done and a stable version can be released.
 */

exit('setup is disabled');

#
# define vital constants
#

$url = $_SERVER['REQUEST_URI'];

if ($_SERVER['QUERY_STRING'])
{
	$url = substr($url, 0, -strlen($_SERVER['QUERY_STRING']) - 1);
}

define('WDPUBLISHER_URL', $url);
define('PUBLISHR_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('WDCORE_ROOT', realpath(PUBLISHR_ROOT . '../wdcore') . DIRECTORY_SEPARATOR);
define('WDELEMENTS_ROOT', realpath(PUBLISHR_ROOT . '../wdelements') . DIRECTORY_SEPARATOR);

//echo WDPUBLISHER_URL . '<br />' . PUBLISHR_ROOT . '<br />' . WDCORE_ROOT . '<br />' . WDELEMENTS_ROOT;

#
# the following constant is used to indicate that we are in the installation process
#

define('WDPUBLISHER_INSTALL', true);

#
#
#

class WdPInstaller
{
	private static $steps = array
	(
		'Informations',
		'Database',
		'Setup',
		'Packages',
		'Modules',
		'User',
		'SavePackages',
		'Config'
	);

	#
	# packages
	#

	const PACKAGES = 'packages';

	static private $mandatory_packages = array
	(
		'users', 'users.roles', 'system.packages'
	);

	static private $recommanded_packages = array
	(
		'blog_articles', 'contents.images',
		'comments',
		'publisher.feed', 'publisher.cache', 'publisher.elements', 'publisher.native',
		'support.thumbnailer',
		'system.aggregate',
		'xhr.textmark'
	);

	public function __construct()
	{
		global $core;

		require_once PUBLISHR_ROOT . 'includes/wdpcore.php';

		$core = new WdPCore();
	}

	public function run()
	{
		global $core;

		$core->locale->addCatalog(PUBLISHR_ROOT . 'admin/');

		#
		#
		#

		global $document;

		$document->on_setup = true;

		$document->css->add('css/setup.css');

		//FIXME: steps should be displayed in the title.
		// NEED: blocks need to have a priority so that we can safuly add the header later

		$document->addToBlock('<h1>' . t('Configure <span>Wd</span>Publisher') . '</h1>', 'main');

		#
		# steps
		#
		# it's all about try and catch.
		# for each step we try and if we fail we catch.
		# when all steps are complete, the installation is complete
		#

		foreach (self::$steps as $step)
		{
			$function = 'try' . $step;

			\ICanBoogie\log('try \1', $step);

			if (!$this->$function())
			{
				$function = 'catch' . $step;

				\ICanBoogie\log('catch \1', $step);

				if (!$this->$function())
				{
					break;
				}
			}
		}
	}

	private function get($which, $default=NULL)
	{
		return isset($_SESSION['wdinstaller'][$which]) ? $_SESSION['wdinstaller'][$which] : NULL;
	}

	private function set($which, $value)
	{
		$_SESSION['wdinstaller'][$which] = $value;
	}

	/*
	**

	STEPS

	**
	*/

	private function tryInformations()
	{
		if (isset($_SESSION['wdinstaller']))
		{
			return true;
		}

		$form = Brickrouge\Form::load($_REQUEST);

		if (!$form || !$form->validate($_REQUEST))
		{
			return false;
		}

		static $properties = array
		(
			'sql_username', 'sql_password', 'sql_server', 'sql_database', 'sql_prefix',
			'site_repository',
			'user_username', 'user_password', 'user_name', 'user_email'
		);

		foreach ($properties as $property)
		{
			$this->set($property, $_REQUEST[$property]);
		}

		\ICanBoogie\log('<h3>session</h3>\1', $_SESSION['wdinstaller']);

		return true;
	}

	private function catchInformations()
	{
		global $core;
		global $document;

		#
		# add help
		#

		$document->addSideMenu
		(
			'help', t('Help'), $core->locale->getFileContents('setup-help.html', __DIR__)
		);

		$document->css->add('css/edit.css');

		#
		# create form
		#

		$form = new Brickrouge\Form
		(
			array
			(
				Brickrouge\Form::VALUES => $_REQUEST,

				Element::CHILDREN => array
				(
					#
					# SQL setup
					#

					'<h2>' . t('SQL setup') . '</h2>',

					'sql_username' => new Text
					(
						array
						(
							Form::LABEL => 'Username',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => 'root'
						)
					),

					'sql_password' => new Text
					(
						array
						(
							Form::LABEL => 'Password'
						)
					),

					'sql_server' => new Text
					(
						array
						(
							Form::LABEL => 'Server',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => 'localhost'
						)
					),

					'sql_database' => new Text
					(
						array
						(
							Form::LABEL => 'Database',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => 'blogvipere'
						)
					),

					'sql_prefix' => new Text
					(
						array
						(
							Form::LABEL => 'Prefix'
						)
					),

					#
					# site setup
					#

					'<h2>' . t('Site setup') . '</h2>',

					'site_repository' => new Text
					(
						array
						(
							Form::LABEL => 'Repository',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => '/repository/'
						)
					),

					#
					# user setup
					#

					'<h2>' . t('Administrator') . '</h2>',

					'user_username' => new Text
					(
						array
						(
							Form::LABEL => 'Username',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => 'gofromiel'
						)
					),

					'user_password' => new Text
					(
						array
						(
							Form::LABEL => 'Password',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => 'lovepub',

							'type' => 'password'
						)
					),

					'user_name' => new Text
					(
						array
						(
							Form::LABEL => 'Name',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => 'Olivier Laviale'
						)
					),

					'user_email' => new Text
					(
						array
						(
							Form::LABEL => 'E-Mail',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => 'gofromiel@gofromiel.com'
						)
					),

					#
					# submit button
					#

					new Button
					(
						'Next', array
						(
							'class' => 'next',
							'type' => 'submit'
						)
					)
				),

				'class' => 'edit management'
			)
		);

		$form->save();

		$document->addToBlock((string) $form, 'main');
	}


	private function tryDatabase()
	{
		global $core;

		$username = $this->get('sql_username');
		$password = $this->get('sql_password');
		$host = $this->get('sql_server');
		$database = $this->get('sql_database');
		$prefix = $this->get('sql_prefix');

		$url  = 'mysql://' . $username;

		if ($password)
		{
			$url .= ':' . $password;
		}

		$url .= '@' . $host . '/' . $database;

		if ($prefix)
		{
			$url .= '?prefix=' . $prefix;
		}

		try
		{
			$core->connect($url);
		}
		catch (\Exception $e)
		{
			\ICanBoogie\log('Unable to connect to the database <em>\1</em> on <em>\2</em> with username <em>\3</em>', $database, $host, $username);

			\ICanBoogie\log_raw($e);

			return false;
		}

		$this->set('sql_url', $url);

		return true;
	}

	private function catchDatabase()
	{
		$this->catchInformations();
	}


	private function trySetup()
	{
		global $core;

		#
		# create config constants
		#

		define('WDDATABASE_URL', $this->get('sql_url'));
		define('WDPUBLISHER_REPOSITORY_URL', $this->get('site_repository'));
		define('WDPUBLISHER_REPOSITORY_TEMPORARY_URL', WDPUBLISHER_REPOSITORY_URL . '_temporary/');

		#
		# create repository folder
		#

		if (!is_dir($_SERVER['DOCUMENT_ROOT'] . WDPUBLISHER_REPOSITORY_URL))
		{
			if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . WDPUBLISHER_REPOSITORY_URL))
			{
				\ICanBoogie\log('Unable to create directory <em>"\1"</em>', WDPUBLISHER_REPOSITORY_URL);

				return false;
			}
		}

		#
		# create temporary folder
		#

		if (!is_dir($_SERVER['DOCUMENT_ROOT'] . WDPUBLISHER_REPOSITORY_TEMPORARY_URL))
		{
			if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . WDPUBLISHER_REPOSITORY_TEMPORARY_URL))
			{
				\ICanBoogie\log('Unable to create directory <em>"\1"</em>', WDPUBLISHER_REPOSITORY_TEMPORARY_URL);

				return false;
			}
		}

		#
		#
		#

		$rc = $core->addPackages(PUBLISHR_ROOT . 'modules');

		if (!$rc)
		{
			\ICanBoogie\log('Unable to load any packages from <em>\1</em>', PUBLISHR_ROOT . 'modules');

			return false;
		}

		return true;
	}

	private function catchSetup()
	{
	}

	private function tryPackages()
	{
		if (isset($_REQUEST[self::PACKAGES]))
		{
			$this->set('packages', $_REQUEST[self::PACKAGES]);
		}

		return $this->get('packages');
	}


	private function catchPackages()
	{
		global $core;
		global $document;

		#
		# add help
		#

		$document->addSideMenu
		(
			'help', t('Help'), $core->locale->getFileContents
			(
				'setup-help-packages.html', __DIR__
			)
		);

		#
		# create false user
		#

		// FIXME-20081226: use system.packages.forms[manage]

		$module = $core->modules['system.packages'];

		$block = $module->getBlock
		(
			'manage', array
			(
				$module->getConstant('MANAGE_MODE') => $module->getConstant('MANAGE_MODE_INSTALLER')
			)
		);

		$form = $block['element'];

		$form->adopt('<br />');

		$form->adopt
		(
			new Element
			(
				'button', array
				(
					'type' => 'submit',
					'class' => 'next',
					Element::INNER_HTML => t('Next')
				)
			)
		);

		$document->addToBlock((string) $form, 'main');

		return;
	}


	private function tryModules()
	{
		return $this->get('modules_installed');
	}

	private function catchModules()
	{
		global $core;

		$modules_ok = true;

		#
		# install modules by priority
		#

		$ids = $core->getModuleIdsByProperty(WdModuleDescriptor::PRIORITY, 0);

		arsort($ids);

		if (!$ids)
		{
			$modules_ok = false;
		}
		else
		{
			$packages = $this->get('packages');
			$mandatories = $core->getModuleIdsByProperty(Module::T_REQUIRED);

			$packages += $mandatories;

//			\ICanBoogie\log('packages: \1, order: \2, mandatories: \3', $packages, $ids, $mandatories);

			foreach ($ids as $id => $priority)
			{
				#
				# skip packages that were not selected by the user
				#

				if (empty($packages[$id]))
				{
					continue;
				}

				$module = $core->modules[$id];

				if (!method_exists($module, 'install'))
				{
					continue;
				}

				#
				# is the module already installed ?
				#

				if ($module->is_installed())
				{
					\ICanBoogie\log('The module <em>\1</em> is already installed !', $id);

					continue;
				}

				#
				# install the module
				#

				if (!$module->install())
				{
					\ICanBoogie\log('Unable to install the module <em>\1</em> !', $id);

					$modules_ok = false;

					continue;
				}

				\ICanBoogie\log('The module <em>\1</em> has been installed.', $id);
			}
		}

		$this->set('modules_installed', $modules_ok);

		return $modules_ok;
	}


	private function tryUser()
	{
		global $core;

		$module = $core->modules['users'];

		if ($module->find(1))
		{
			return true;
		}

		return false;
	}

	private function catchUser()
	{
		global $core;

		$module = $core->modules['users'];

		return $module->save
		(
			array
			(
				$module->getConstant('USERNAME') => $this->get('user_username'),
				$module->getConstant('PASSWORD') => $this->get('user_password'),
				$module->getConstant('NAME') => $this->get('user_name'),
				$module->getConstant('EMAIL') => $this->get('user_email')
			),

			0
		);
	}


	private function trySavePackages()
	{
		global $core;

		$user = $core->user;

		$module = $core->modules['users'];

		$user = $module->find(1);

		#
		# start config module
		#

		$module = $core->modules['system.config'];

		$module->startup();

		#
		#
		#

		$module = $core->modules['system.packages'];

		#
		# post operation parameters need to be passed by reference
		#

		$params = array
		(
			WdPModule::OPERATION_KEYS => $this->get('packages')
		);

		$module->handle_operation($module->getConstant('OPERATION_PACKAGES'), $params);

		return true;
	}


	private function tryConfig()
	{
		return false;
	}

	private function catchConfig()
	{
		global $core;
		global $document;

		#
		# add help
		#

		$document->addSideMenu
		(
			'help', t('Help'), $core->locale->getFileContents
			(
				'setup-help-config.html', __DIR__
			)
		);

		#
		# block
		#

		$document->addToBlock
		(
			'<p>' . t('The setup is complete.') . '</p>' .
			'<p>' . t('Please copy the following code in the file %file ' .
				'then press the <em>Connection</em> button:', array('%file' => WDPUBLISHER_URL . 'config.php')) .
			'</p>',

			'main'
		);

		$config = strtr
		(
			file_get_contents('config-template.php', true), array
			(
				'{WDPUBLISHER_URL}' => WDPUBLISHER_URL,
				'{WDPUBLISHER_REPOSITORY_URL}' => WDPUBLISHER_REPOSITORY_URL,
				'{WDPUBLISHER_REPOSITORY_TEMPORARY_URL}' => WDPUBLISHER_REPOSITORY_TEMPORARY_URL,
				'{WDDATABASE_URL}' => $this->get('sql_url')
			)
		);

		#
		# create connection form
		#

		$module = $core->modules['users'];

		$form = new Brickrouge\Form
		(
			array
			(
				Element::CHILDREN => array
				(
					new Element
					(
						'textarea', array
						(
							'value' => $config,
							'style' => 'margin-bottom: 1em',
							'class' => 'code',
							'rows' => 20
						)
					),

					new Element
					(
						'button', array
						(
							'type' => 'submit',
							'class' => 'connect',
							Element::INNER_HTML => t('Connect')
						)
					)
				),

				Brickrouge\Form::HIDDENS => array
				(
					WdOperation::NAME => $module->getConstant('OPERATION_CONNECT'),
					WdOperation::DESTINATION => $module,

					$module->getConstant('USERNAME') => $this->get('user_username'),
					$module->getConstant('PASSWORD') => $this->get('user_password')
				),

				'class' => 'management'
			),

			'div'
		);

		$document->addToBlock((string) $form, 'main');
	}
}

$installer = new WdPInstaller();

$installer->run();