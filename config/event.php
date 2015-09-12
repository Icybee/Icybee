<?php

namespace Icybee;

use ICanBoogie;
use Icybee;

$hooks = Hooks::class . '::';

return [

	ICanBoogie\Operation::class . '::get_form' => 'Icybee\Element\Form::on_operation_get_form',
	ICanBoogie\Module\Operation\SaveOperation::class . '::control:before' => $hooks . 'before_save_operation_control',
	ICanBoogie\HTTP\RequestDispatcher::class . '::dispatch' => 'Icybee\StatsDecorator::on_dispatcher_dispatch',
	ICanBoogie\HTTP\NotFound::class . '::rescue' => $hooks . 'on_exception_rescue',
	ICanBoogie\View\View::class . '::alter' => $hooks . 'on_view_alter',
	Icybee\Modules\Pages\PageRenderer::class . '::render:before' => $hooks . 'before_page_renderer_render',
	Icybee\Modules\Pages\PageRenderer::class . '::render' => $hooks . 'on_page_renderer_render',
	Icybee\Modules\Users\Operation\LogoutOperation::class . '::process:before' => $hooks . 'before_user_logout',

];
