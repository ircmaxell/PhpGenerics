<?php

$autoloader = require __DIR__ . "/../vendor/autoload.php";
$new_autoloader = new PhpGenerics\Autoloader($autoloader);
$autoloader->unregister();
$new_autoloader->register(true);

foreach (get_declared_classes() as $class) {
	if (strpos($class, "ComposerAutoloaderInit") === 0) {
		$r = new ReflectionProperty($class, "loader");
		$r->setAccessible(true);
		$r->setValue($new_autoloader);
	}
}