<?php

namespace Icybee\Routing;

use ICanBoogie\Routing\Controller;

use ICanBoogie\Binding\Routing\ControllerBindings as RoutingBindings;
use ICanBoogie\View\ControllerBindings as ViewBindings;

class ResourceController extends Controller
{
	use Controller\ResourceTrait, RoutingBindings, ViewBindings;
}
