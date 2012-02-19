<!DOCTYPE>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Publishr &ndash; <?php echo $class ?></title>
<?php foreach($css as $url): ?>
<link href="<?php echo $url ?>" type="text/css" rel="stylesheet" />
<?php endforeach; ?>
</head>

<body class="exception">

<div id="quick">←&nbsp;<a href="<?php echo $site ? $site->url : '/' ?>"><?php echo $site ? wd_entities($site->title) : 'Home' ?></a></div>

<div id="contents-wrapper">
	<h1><?php echo $code . ' ' . $class ?></h1>

	<?php echo $formated_exception ?>

	<footer><a href="http://www.icybee.org/" target="_blank">Icybee</a> v<?php echo $version ?></footer>
</div>

</body>
</html>