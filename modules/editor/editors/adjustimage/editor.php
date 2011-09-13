<?php

class adjustimage_WdEditorElement extends adjustnode_WdEditorElement
{
	public function __construct($tags, $dummy=null)
	{
		$tags = wd_array_merge_recursive
		(
			$tags, array
			(
				self::T_CONFIG => array
				(
					'scope' => 'images'
				)
			)
		);

		parent::__construct($tags, $dummy);
	}
}