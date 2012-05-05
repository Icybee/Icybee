<?php

namespace ICanBoogie;

?><!DOCTYPE>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Icybee &ndash; Exception: <?= escape(shorten(strip_tags($exception->getMessage()), 32, 1)) ?></title>
<?php foreach($css as $url): ?>
<link href="<?= $url ?>" type="text/css" rel="stylesheet" />
<?php endforeach ?>
</head>

<body class="exception">

<div id="quick">←&nbsp;<a href="<?= $site ? $site->url : '/' ?>"><?= $site ? escape($site->title) : 'Home' ?></a></div>

<div class="actionbar">
<div class="pull-left">
	<div class="actionbar-title"><h1>Icybee <small><?= $code . ' ' . $class ?></small></h1></div>
</div>
<?php if ($reported): ?>
<div class="pull-right">
	<span class="btn btn-success">The error has been reported</span>
</div>
<?php endif ?>
</div>

<div id="contents-wrapper">
	<?= $formated_exception ?>

	<footer><a href="http://www.icybee.org/" target="_blank">Icybee</a> v<?= $version ?></footer>
</div>

</body>
</html>