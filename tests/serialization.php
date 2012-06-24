<?php

require_once '../startup.php';

$user = $core->site->home;

$serialized_user = serialize($user);
$unserialized_user = unserialize($serialized_user);

echo $serialized_user;

var_dump($unserialized_user, $user);