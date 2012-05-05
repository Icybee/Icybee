<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Module;

use Brickrouge\Element;
use Brickrouge\Text;

class feed_WdEditorElement extends WdEditorElement
{
	private $elements = array();

	public function __construct($tags, $dummy=null)
	{
		global $core;

		$constructors = array();

		foreach ($core->modules->descriptors as $module_id => $descriptor)
		{
			if ($module_id == 'contents' || !Module::is_extending($module_id, 'contents'))
			{
				continue;
			}

			$constructors[$module_id] = $descriptor[Module::T_TITLE];
		}

		uasort($constructors, 'ICanBoogie\unaccent_compare_ci');

		parent::__construct
		(
			'div', $tags + array
			(
				self::CHILDREN => array
				(
					$this->elements['constructor'] = new Element
					(
						'select', array
						(
							Element::LABEL => 'Module',
							Element::LABEL_POSITION => 'above',
							Element::REQUIRED => true,
							Element::OPTIONS => array(null => '<sélectionner un module>') + $constructors
						)
					),

					$this->elements['limit'] = new Text
					(
						array
						(
							Element::LABEL => "Nombre d'entrées dans le flux",
							Element::LABEL_POSITION => 'above',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => 10,

							'size' => 4
						)
					),

					$this->elements['settings'] = new Element
					(
						Element::TYPE_CHECKBOX_GROUP, array
						(
							Element::LABEL => 'Options',
							Element::LABEL_POSITION => 'above',
							Element::OPTIONS => array
							(
								'is_with_author' => "Mentionner l'auteur",
								'is_with_category' => "Mentionner les catégories",
								'is_with_attached' => "Ajouter les pièces jointes"
							),

							'class' => 'list'
						)
					)
				),

				'class' => 'editor feed combo'
			)
		);
	}

	public function offsetSet($offset, $value)
	{
		if ($offset == 'name')
		{
			foreach ($this->elements as $identifier => $element)
			{
				$element['name'] = $value . '[' . $identifier . ']';
			}
		}

		parent::offsetSet($offset, $value);
	}

	public function render_inner_html()
	{
		$value = $this['value'];

		if ($value)
		{
			$values = json_decode($value, true);

			foreach ($values as $identifier => $value)
			{
				$this->elements[$identifier]['value'] = $value;
			}
		}

		return parent::render_inner_html();
	}

	static public function to_content($value, $content_id, $page_id)
	{
		global $core;

		$contents = parent::to_content($value, $content_id, $page_id);

		if (!$contents)
		{
			return;
		}

		// TODO-20101130: there is no cleanup for that, if the content is deleted, the view's target won't be removed

		$constructor = $contents['constructor'];
		$view_target_key = 'views.targets.' . strtr($constructor, '.', '_') . '/feed';

		$core->site->metas[$view_target_key] = $page_id;

		return json_encode($contents);
	}

	// http://tools.ietf.org/html/rfc4287

	static public function render($contents)
	{
		global $core;

		$page = $core->request->context->page;
		$site = $page->site;
		$options = json_decode($contents, true);

		$constructor = $options['constructor'];
		$limit = $options['limit'];
		$with_author = false;

		if (isset($options['settings']))
		{
			$options['settings']['is_with_author'];
		}

		$gmt_offset = '+01:00';

		$fdate = $core->locale->date_formatter;
		$time_pattern = "y-MM-dd'T'HH:mm:ss";

		$host = preg_replace('#^www\.#', '', $_SERVER['SERVER_NAME']);
		$page_created = $fdate->__invoke($page->created, 'y-MM-dd');

		$entries = $core->models[$constructor]->find_by_constructor($constructor)->visible->order('date DESC')->limit($limit)->all;

		ob_start();

?>

	<id>tag:<?php echo $host ?>,<?php echo $page_created ?>:<?php echo $page->slug ?></id>
	<title><?php echo $page->title ?></title>
	<link href="<?php echo $page->absolute_url ?>" rel="self" />
	<link href="<?php echo $page->home->absolute_url ?>" />

	<author>
		<name><?php $user = $page->user; echo ($user->firstname && $user->lastname) ? $user->firstname . ' ' . $user->lastname : $user->name ?></name>
	</author>

	<updated><?php

	$updated = '';

	foreach ($entries as $entry)
	{
		if (strcmp($updated, $entry->modified) < 0)
		{
			$updated = $entry->modified;
		}
	}

	echo $fdate->__invoke($updated, $time_pattern) . $gmt_offset ?></updated>

<?php

		foreach ($entries as $entry)
		{
?>
	<entry>
		<title><?php echo wd_entities($entry->title) ?></title>
		<link href="<?php echo $entry->absolute_url ?>" />
		<id>tag:<?php echo $host ?>,<?php echo $fdate->__invoke($entry->created, 'y-MM-dd') ?>:<?php echo $entry->slug ?></id>
		<updated><?php echo $fdate->__invoke($entry->modified, $time_pattern) . $gmt_offset ?></updated>
		<published><?php echo $fdate->__invoke($entry->date, $time_pattern) . $gmt_offset ?></published>
		<?php if ($with_author): ?>
		<author>
			<name><?php

			$user = $entry->user;

			echo ($user->firstname && $user->lastname) ? $user->firstname . ' ' . $user->lastname : $entry->user->name ?></name>
		</author>
		<?php endif; ?>
		<?php /*
		<category term="<?php echo $entry->category ?>" /> */ ?>
		<content type="html" xml:lang="<?php echo $entry->language ? $entry->language : $site->language  ?>"><![CDATA[<?php echo $entry ?>]]></content>
	</entry>
<?php
		}

		$rc = ob_get_clean();
		$rc = preg_replace('#(href|src)="/#', '$1="http://' . $host .'/', $rc);

		header('Content-Type: application/atom+xml;charset=utf-8');
		//header('Content-Type: text/plain');

		return '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">' . $rc . '</feed>';;
	}
}