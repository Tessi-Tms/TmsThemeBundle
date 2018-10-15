<?php
namespace Tms\Bundle\ThemeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Tms\Bundle\ThemeBundle\Model\Theme;

class TmsThemeExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Ignore outdated configuration
        $themes = array();
        foreach($config['themes'] as $id => $theme) {
            unset($theme['bundles']);

            $themes[$id] = $theme;
        }
        $container->setParameter('tms.themes.all', $themes);
    }
}
