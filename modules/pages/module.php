<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\ActiveRecord\Page;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Module extends \ICanBoogie\Modules\Nodes\Module
{
	/**
	 * Only the "list" view is available and it is used to create the sitemap.
	 *
	 * @see ICanBoogie\Module.Nodes::__get_views()
	 */
	protected function __get_views()
	{
		return array
		(
			'list' => array
			(
				'title' => 'Sitemap',
				'class' => 'Icybee\Modules\Pages\ListView',
				'assets' => array(),
				'renders' => \Icybee\Views\View::RENDERS_MANY
			)
		);
	}

	// FIXME-20110918: this should be an operation
	protected function operation_query_delete(WdOperation $operation)
	{
		$entries = array();

		foreach ($operation->params['entries'] as $id)
		{
			$record = $this->model[$id];

			if (!$record)
			{
				continue;
			}

			$entries = array_merge(self::get_all_children_ids($record), $entries);
		}

		$entries = array_unique($entries);

		$operation->params['entries'] = $entries;

		return parent::operation_query_delete($operation);
	}

	private function get_all_children_ids($record)
	{
		$ids = array();

		if ($record->children)
		{
			// FIXME-20100504: `children` only returns online children !

			foreach ($record->children as $child)
			{
				$ids = array_merge(self::get_all_children_ids($child), $ids);
			}
		}

		$ids[] = $record->nid;

		return $ids;
	}

	// FIXME-20110918: this should be an operation
	protected function operation_query_copy(WdOperation $operation)
	{
		$entries = $operation->params['entries'];

		return array
		(
			'title' => 'Copy entries',
			'message' => t('Are you sure you want to copy the :count selected entries ?', array(':count' => count($entries))),
			'confirm' => array('Don\'t copy', 'Copy'),
			'params' => array
			(
				'entries' => $entries
			)
		);
	}

	protected function block_manage()
	{
		return new Manager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'title', 'url', 'is_navigation_excluded', 'is_online', 'uid', 'modified'
				),

				Manager::T_ORDER_BY => null
			)
		);
	}

	public function get_contents_section($nid, $template=null)
	{
		list($template, $template_description, $is_inherited) = $this->resolve_template($nid, $template);
		list($elements, $hiddens) = $this->get_contents_section_elements($nid, $template);

		if ($elements)
		{
			$template_description .= ' ' . t("The following elements are editable:");
		}
		else
		{
			$template_description = t("The <q>:template</q> template does not define any editable element.", array(':template' => $template));
		}

		$elements = array_merge
		(
			array
			(
				Page::TEMPLATE => new \WdAdjustTemplateElement
				(
					array
					(
						Element::LABEL => 'template',
						Element::LABEL_POSITION => 'before',
						Element::GROUP => 'contents',
						Element::DESCRIPTION => $template_description
					)
				)
			),

			$elements
		);

		return array
		(
			array
			(
				Form::HIDDENS => $hiddens,

				#
				# If the template is inherited, we remove the value in order to have a clean
				# inheritence, easier to manage.
				#

				Form::VALUES => array
				(
					Page::TEMPLATE => $is_inherited ? null : $template
				),

				Element::GROUPS => array
				(
					'contents' => array
					(
						'title' => 'Contents',
						'weight' => 10
					),

					'contents.inherit' => array
					(
						'weight' => 11,
						'description' => 'contents.inherit'
					)
				),

				Element::CHILDREN => $elements
			),

			array
			(
				'name' => $template,
				'description' => $template_description,
				'inherited' => $is_inherited
			)
		);
	}

	protected function get_contents_section_elements($nid, $template)
	{
		global $core;

		$info = self::get_template_info($template);

		if (!$info)
		{
			return array(array(), array());
		}

		list($editables, $styles) = $info;

		$elements = array();
		$hiddens = array();

		$contents_model = $this->model('contents');
		$context = $core->site->path;

		foreach ($editables as $editable)
		{
			$id = $editable['id'];
			$title = $editable['title'];
			$title = t($id, array(), array('scope' => array('content', 'title'), 'default' => $title));

			$does_inherit = !empty($editable['inherit']);

			$name = 'contents[' . $id . ']';
			$value = null;

			$editor = $editable['editor'];
			$editor_config = json_decode($editable['config'], true);
			$editor_description = $editable['description'];

			#
			#
			#

			$contents = $nid ? $contents_model->where('pageid = ? AND contentid = ?', $nid, $id)->one : null;

			if ($contents)
			{
				$value = $contents->content;
				$editor = $contents->editor;
			}

			if ($does_inherit)
			{
				if (!$contents && $nid)
				{
					$inherited = null;
					$node = $this->model[$nid];

					while ($node)
					{
						$node_contents = $node->contents;

						if (isset($node_contents[$id]))
						{
							$inherited = $node;

							break;
						}

						$node = $node->parent;
					}

					if (!$node)
					{
						$node = $core->site->home;

						if (isset($node->contents[$id]))
						{
							$inherited = $node;
						}
					}

					// TODO-20101214: check home page

					if ($inherited)
					{
						$elements[] = new Element
						(
							'div', array
							(
								Form::LABEL => $title,
								Element::GROUP => 'contents.inherit',
								Element::INNER_HTML => '',
								Element::DESCRIPTION => t
								(
									'This content is currently inherited from the <q><a href="!url">!title</a></q> parent page. <a href="#edit" class="btn">Edit the content</a>', array
									(
										'!url' => $context . '/admin/' . $this->id . '/' . $inherited->nid . '/edit',
										'!title' => $inherited->title
									)
								),

								\Brickrouge\Section::T_PANEL_CLASS => 'inherit-toggle'
							)
						);
					}
					else
					{
						$editor_description .= t('No parent page define this content.');
					}
				}
			}

			/*
			 * each editor as a base name `contents[<editable_id>]` and much at least define two
			 * values :
			 *
			 * - `contents[<editable_id>][editor]`: The editor used to edit the contents
			 * - `contents[<editable_id>][contents]`: The content being edited.
			 *
			 */

			if ($editable['editor'])
			{
				$class = $editable['editor'] . '_WdEditorElement';

				if (!class_exists($class, true))
				{
					$elements[$name . '[contents]'] = new Element
					(
						'div', array
						(
							Form::LABEL => $title,
							Element::INNER_HTML => t('Éditeur inconnu : %editor', array('%editor' => $editable['editor'])),
							Element::GROUP => $does_inherit ? 'contents.inherit' : 'contents',

							'class' => 'danger'
						)
					);

					continue;
				}

				if (empty($editable['multiple']))
				{
					$elements[$name . '[contents]'] = new $class
					(
						array
						(
							Form::LABEL => $title,

							\WdEditorElement::T_STYLESHEETS => $styles,
							\WdEditorElement::T_CONFIG => $editor_config,

							Element::GROUP => $does_inherit ? 'contents.inherit' : 'contents',
							Element::DESCRIPTION => $editor_description,

							'id' => 'editor-' . $id,
// 							'name' => $name,
							'value' => $value
						)
					);
				}
				else
				{
					$n = 3;
					$fragments = array();

					for ($i = 0 ; $i < $n ; $i++)
					{
						$fragments[] = '<div class="excerpt"><em>generating excerpt...</em></div>';

						$fragments[$name . "[contents][$i]"] = new $class
						(
							array
							(
								\WdEditorElement::T_STYLESHEETS => $styles,
								\WdEditorElement::T_CONFIG => $editor_config,

// 								'name' => "$name[$i]",
								'value' => $value
							)
						);
					}

					$fragments[] = '<p><button type="button" class="continue small">Ajouter un nouveau contenu</button></p>';

					$elements[] = new Element
					(
						'div', array
						(
							Form::LABEL => $title,

							Element::GROUP => $does_inherit ? 'contents.inherit' : 'contents',
							Element::DESCRIPTION => $editor_description,
							Element::CHILDREN => $fragments,

							'id' => 'editor-' . $id,
							'class' => 'editor multiple'
						)
					);

					$hiddens[$name . '[is_multiple]'] = true;
				}

				#
				# we add the editor's id as a hidden field
				#

				$hiddens[$name . '[editor]'] = $editable['editor'];
			}
			else
			{
				$elements[$name . '[contents]'] = new \WdMultiEditorElement
				(
					$editor, array
					(
						Form::LABEL => $title,

						\WdMultiEditorElement::T_NOT_SWAPPABLE => isset($editable['editor']),
						\WdMultiEditorElement::T_SELECTOR_NAME => $name . '[editor]',
						\WdMultiEditorElement::T_EDITOR_TAGS => array
						(
							\WdEditorElement::T_STYLESHEETS => $styles,
							\WdEditorElement::T_CONFIG => $editor_config
						),

						Element::GROUP => $does_inherit ? 'contents.inherit' : 'contents',
						Element::DESCRIPTION => $editor_description,

						'id' => 'editor-' . $id,
						'value' => $value
					)
				);
			}
		}

		return array($elements, $hiddens);
	}

	/**
	 * Returns the template to use for a specified page.
	 *
	 * @param int $nid
	 * @return array An array composed of the template name, the description and a boolean
	 * representing wheter or not the template is inherited for the specified page.
	 */

	protected function resolve_template($nid, $request_template=null)
	{
		global $core;

		$inherited = false;
		$is_alone = !$this->model->select('nid')->find_by_siteid($core->site_id)->rc;

		if ($is_alone)
		{
			$template = 'home.html';
		}

		$description = t("The template defines a page model of which some elements are editable.");

		if (!$nid)
		{
			if ($is_alone)
			{
				$description .= " Parce que la page est seule elle utilise le gabarit <q>home.html</q>.";
			}
			else if (!$request_template)
			{
				$template = 'page.html';
			}
			else
			{
				$template = $request_template;
			}

			return array($template, $description, $template == 'page.html');
		}

		$record = $this->model[$nid];
		$definer = null;
		$template = $request_template !== null ? $request_template : $record->template;

//		wd_log_done('template: \1 (requested: \3), is_home: \2', array($template, $record->is_home, $request_template));

		if ($template == 'page.html' && (!$record->parent || ($record->parent && $record->parent->is_home)))
		{
//			wd_log('page parent is home, hence the page.html template');

			$inherited = true;

			// TODO-20100507: à réviser, parce que la page peut ne pas avoir de parent.

			$description .= ' ' . "Parce qu'aucun gabarit n'est défini pour la page, elle utilise
			le gabarit <q>page.html</q>.";
		}
		else if ($template == 'home.html' && (!$record->parent && $record->weight == 0))
		{
			$inherited = true;

			//$template_description .= ' ' . "Cette page utilise le gabarit &laquo;&nbsp;home.html&nbsp;&raquo;.";
		}
		else if (!$request_template)
		{
			$definer = $record->parent;

			if (!$definer)
			{
				$template = 'page.html';
				$inherited = true;

				$description .= ' ' . "Parce qu'aucun gabarit n'est défini pour la page, elle utilise
				le gabarit <q>page.html</q>.";
			}
		}
		else
		{
			$definer = $record;
			$parent = $record->parent;

//			wd_log_done('parent: \1 (\2 ?= \3)', array($definer->title, $definer->template, $template));

			while ($parent)
			{
//				wd_log_done('parent: \1, template: \2', array($parent->title, $parent->template));

				if ($parent->template == $request_template)
				{
					break;
				}

				$parent = $parent->parent;
			}

//			wd_log_done('end parent: \1', array($parent ? $parent->title : 'none'));

			if ($parent && $parent->template == $request_template)
			{
				$definer = $parent;
			}

//			wd_log_done('definer: \1:\3 (\2), record: \4:\5', array($definer->title,  $definer->template, $definer->nid, $record->title, $record->nid));
		}

		if ($definer && $definer != $record)
		{
//			wd_log("entry template: $template ($record->nid), from: $inherited->template ($inherited->nid: $inherited->title)");

			$description .= ' ' . t
			(
				'This page uses the <q>:template</q> template, inherited from the parent page <q><a href="!url">!title</a></q>.', array
				(
					':template' => $template,
					'!url' => $core->site->path . "/admin/{$this->id}/{$definer->nid}/edit",
					'!title' => $definer->title
				)
			);

			$inherited = true;
		}

		return array($template, $description, $inherited);
	}

	static public function get_template_info($name)
	{
		global $core;

		$site = $core->site;
		$path = $site->resolve_path('templates/' . $name);

		if (!$path)
		{
			wd_log_error('Uknown template file %name', array('%name' => $name));

			return array();
		}

		$html = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $path);
		$parser = new \WdHTMLParser();

		return self::get_template_info_callback($html, $parser);
	}

	static protected function get_template_info_callback($html, $parser)
	{
		$styles = array();
		$contents = array();

		#
		# search css files
		#

		preg_match_all('#<link.*type="text/css".*>#', $html, $matches);

		foreach ($matches[0] as $match)
		{
			preg_match_all('#(\S+)="([^"]+)"#', $match, $attributes_matches, PREG_SET_ORDER);

			$attributes = array();

			foreach ($attributes_matches as $attribute_match)
			{
				list(, $attribute, $value) = $attribute_match;

				$attributes[$attribute] = $value;
			}

			if (isset($attributes['media']) && $attributes['media'] != 'screen')
			{
				continue;
			}

			$styles[] = $attributes['href'];
		}

		#
		#
		#

		$tree = $parser->parse($html, 'wdp:');

		//wd_log('tree: \1', array($tree));

		#
		# contents
		#

		$contents_collection = \WdHTMLParser::collectMarkup($tree, 'page:content');

//		wd_log('contents collection: \1', array($contents_collection));

		foreach ($contents_collection as $node)
		{
			if (isset($node['children']))
			{
				foreach ($node['children'] as $child)
				{
					if (!is_array($child))
					{
						continue;
					}

					if ($child['name'] != 'with-param')
					{
						continue;
					}

					$param = $child['args']['name'];

					// TODO: what about arrays ? we should create a tree to string function

					$value = '';

					foreach ($child['children'] as $cv)
					{
						$value .= $cv;
					}

					$node['args'][$param] =	$value;
				}
			}

//			wd_log('found content: \1', array($node));

			$contents[] = $node['args'] + array
			(
				'editor' => null,
				'config' => null,
				'description' => null
			);
		}

		#
		# recurse on templates
		#

		global $core;

		$site = $core->site;
		$root = $_SERVER['DOCUMENT_ROOT'];

		$call_template_collection = \WdHTMLParser::collectMarkup($tree, 'call-template');

		foreach ($call_template_collection as $node)
		{
			$template_name = $node['args']['name'];

			$file = $template_name . '.html';
			$path = $site->resolve_path('templates/partials/' . $file);

			if (!$path)
			{
				wd_log_error('Partial template %name not found', array('%name' => $file));

				continue;
			}

			$template = file_get_contents($root . $path);

			list($partial_contents, $partial_styles) = self::get_template_info_callback($template, $parser);

			$contents = array_merge($contents, $partial_contents);

			if ($partial_styles)
			{
				$styles = array_merge($styles, $partial_styles);
			}
		}

		return array($contents, $styles);
	}

	protected function adjust_loadRange(array $where, array $values, $limit, $page)
	{
		$where[] = 'pattern = ""';

		return parent::adjust_loadRange($where, $values, $limit, $page);
	}

	public function adjust_createEntry($record)
	{
		return parent::adjust_createEntry($record) . ' <span class="small">&ndash; ' . $record->url . '</span>';
	}
}
