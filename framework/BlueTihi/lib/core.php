<?php

namespace BlueTihi;

class Core
{
	protected static $engines;
	protected static $contexts;
}

interface Engine
{
	public function __construct(Context $context);

	public function __invoke($source, $this_args, array $options=array());
}