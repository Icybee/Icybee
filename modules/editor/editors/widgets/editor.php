<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Exception;

class widgets_WdEditorElement extends WdEditorElement
{
	static protected $config = array();

	static public function __static_construct()
	{
		global $core;

		self::$config = $core->configs->synthesize('widgets', 'merge');
	}

	static public function to_content(array $params, $content_id, $page_id)
	{
		if (empty($params['contents']))
		{
			return;
		}

		return json_encode(array_keys($params['contents']));
	}

	static public function render($contents)
	{
		$selected = json_decode($contents);

		if ($contents && !$selected)
		{
			throw new Exception('Unable to decode contents: !contents', array('!contents' => $contents));
		}

		$selected = array_flip($selected);
		$availables = self::$config;

		$undefined = array_diff_key($selected, $availables);

		if ($undefined)
		{
			throw new Exception('Undefined widget(s): :list', array(':list' => implode(', ', array_keys($undefined))));
		}

		$list = array_intersect_key(self::$config, $selected);

		if (!$list)
		{
			return;
		}

		$list = array_merge($selected, $list);

		$rc = '';

		foreach ($list as $id => $widget)
		{
			$rc .= '<div id="widget-' . wd_normalize($id) . '" class="widget">' . self::render_widget($widget) . '</div>';
		}

		return $rc;
	}

	static protected function render_widget($widget)
	{
		global $core, $page;

		if (isset($widget['file']))
		{
			$file = $widget['file'];

			if (substr($file, -4, 4) == '.php')
			{
				ob_start();

				require $file;

				return ob_get_clean();
			}
			else if (substr($file, -5, 5) == '.html')
			{
				return Patron(file_get_contents($file), null, array('file' => $file));
			}
			else
			{
				throw new Exception('Unable to process file %file, unsupported type', array('%file' => $file));
			}
		}
		else if (isset($widget['module']) && isset($widget['block']))
		{
			return $core->modules[$widget['module']]->getBlock($widget['block']);
		}
		else
		{
			throw new Exception('Unable to render view %view. The description of the view is invalid', array('%view' => $widget));
		}
	}


	public function __construct($tags, $dummy=null)
	{
		global $core;

		parent::__construct
		(
			'ul', $tags + array
			(
				'class' => 'widgets-selector combo'
			)
		);

		if ($this->get(Element::DESCRIPTION) === null)
		{
			$this->set
			(
				Element::DESCRIPTION, "Sélectionner les widgets à afficher. Vous pouvez
				les ordonner par glissé-déposé."
			);
		}

		$core->document->css->add('editor.css');
		$core->document->js->add('editor.js');
	}

	public function render_inner_html()
	{
		$rc = parent::render_inner_html();

		$value = $this->get('value');
		$name = $this->get('name');

		$value = json_decode($value);
		$value = is_array($value) ? array_flip($value) : array();

		// TODO-20100204: check deprecated widgets ids

		$list = array_merge($value, self::$config);

		//wd_log('value: \1, list: \2 \3', array($value, $list, array_merge($value, $list)));

		foreach ($list as $id => $widget)
		{
			$rc .= '<li>';

			$rc .= new Element
			(
				Element::TYPE_CHECKBOX, array
				(
					Element::LABEL => $widget['title'],

					'name' => $name . '[' . $id . ']',
					'checked' => isset($value[$id])
				)
			);

			$rc .= '</li>';
		}

		return $rc;
	}
}