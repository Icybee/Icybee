<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Editor;

use Brickrouge\A;

/**
 * "Tabbable" editor content renderer.
 */
class TabbableEditorRenderer
{
	protected $editor;

	public function __construct(TabbableEditor $editor)
	{
		$this->editor = $editor;
	}

	public function __invoke($content)
	{
		$panes = $content;

		$nav = $this->render_nav($panes);
		$content = $this->render_content($panes);

		return <<<EOT
<div class="tabbable">

	$nav
	$content

</div>
EOT;
	}

	protected function render_nav(array $panes)
	{
		$html = '';

		foreach ($panes as $i => $pane)
		{
			$html .= '<li';

			if (!$i)
			{
				$html .= ' class="active"';
			}

			$html .= '>';

			$html .= $this->render_tabbable_tab($pane, $panes);

			$html .= '</li>';
		}

		return '<ul class="nav nav-tabs">' . $html . '</ul>';
	}

	protected function render_tabbable_tab(array $pane, array $panes)
	{
		return new A($pane['title'], '#', array('data-toggle' => 'tab'));
	}

	protected function render_content(array $panes)
	{
		$html = '';

		foreach ($panes as $i => $pane)
		{
			$html .= '<div class="tab-pane';

			if (!$i)
			{
				$html .= ' active';
			}

			$html .= '">';

			$html .= $this->render_pane($pane);

			$html .= '</div>';
		}

		return '<div class="tab-content combo">' . $html . '</div>';
	}

	protected function render_pane(array $pane)
	{
		global $core;

		$editor_id = $pane['editor_id'];
		$serialized_content = $pane['serialized_content'];
		$editor = $core->editors[$editor_id];

		return $editor->render($editor->unserialize($serialized_content));
	}
}