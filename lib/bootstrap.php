<?php

$dir = dirname(__DIR__);
while ($dir !== DIRECTORY_SEPARATOR && !file_exists($dir . "/vendor/autoload.php")) {
	$dir = dirname($dir);
}

if ($dir === DIRECTORY_SEPARATOR) {
	throw new \RuntimeException("Cannot find autoloader, did you install with composer?");
}

$autoloader = require $dir . "/vendor/autoload.php";
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
