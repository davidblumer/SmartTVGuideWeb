<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class AppExtension
 * @package AppBundle\DependencyInjection
 */
class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
//        $this->loadApi($container);
//        $this->loadEventListener($container);
        $this->loadServices($container);
    }

//    private function loadApi(ContainerBuilder $container)
//    {
//        $path = __DIR__ . '/../Resources/config/api';
//
//        $loader = new YamlFileLoader($container, new FileLocator($path));
//
//        $finder = new Finder();
//        $finder->files()->in($path);
//
//        /** @var SplFileInfo $file */
//        foreach ($finder as $file) {
//            $loader->load($file->getRelativePathname());
//        }
//    }

    private function loadServices (ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $files = array(
            'services.yml'
        );

        foreach ($files as $file)
        {
            $loader->load($file);
        }
    }

}
