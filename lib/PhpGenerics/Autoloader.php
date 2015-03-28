<?php

namespace PhpGenerics;

use Composer\Autoload\ClassLoader;

class Autoloader extends ClassLoader {
    private $engine;

    public function __construct(ClassLoader $loader, Engine $engine = null) {
        if (!$engine) {
            $engine = new Engine;
        }
        $this->engine = $engine;
        $this->add(null, $loader->getFallbackDirs());
        $this->addPsr4(null, $loader->getFallbackDirsPsr4());
        foreach ($loader->getPrefixes() as $prefix => $path) {
            $this->add($prefix, $path);
        }
        foreach ($loader->getPrefixesPsr4() as $prefix => $path) {
            $this->addPsr4($prefix, $path);
        }
        $this->setUseIncludePath($loader->getUseIncludePath());
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     * @return bool|null True if loaded, null otherwise
     */
    public function loadClass($class)
    {
        if (strpos($class, Engine::CLASS_TOKEN) !== false) {
            $code = $this->engine->implement($class);
            var_dump($code);
            includeCode($code);

            return true;
        }

        if ($file = $this->findFile($class)) {
            $code = $this->engine->process($file);
            var_dump($code);
            includeCode($code);
            return true;
        }
    }
}

function includeCode($code) {
    eval($code);
}
