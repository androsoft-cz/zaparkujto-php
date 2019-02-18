<?php

namespace App\Core\Extensions;

use Nette;

final class ZaparkujtoExtension extends Nette\DI\CompilerExtension
{

    /** @var array */
    public $defaults = [];


    public function afterCompile(Nette\PhpGenerator\ClassType $class) // @codingStandardsIgnoreLine
    {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        // Create dirs
        foreach ($container->parameters['dirs'] as $dir) {
            Nette\Utils\FileSystem::createDir($dir);
        }
    }

}
