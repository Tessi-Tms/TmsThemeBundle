<?php
namespace Tms\Bundle\ThemeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistry;
use Tms\Bundle\ThemeBundle\Translation\ThemeTranslator;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;

class ThemeCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Register the themes from configuration
        $themeRegistry = $container->getDefinition(ThemeRegistry::class);
        foreach($container->getParameter('tms.themes.all') as $id => $theme) {
            $themeRegistry->addMethodCall('setTheme', array($id, $theme));
        }

        // Redefine default translator class
        if ($container->hasDefinition('translator.default')) {
            $container
                ->getDefinition('translator.default')
                ->setClass(ThemeTranslator::class)
                ->addMethodCall('setBundles', array($container->getParameter('kernel.bundles')))
                ->addMethodCall('setRootPath', array(sprintf('%s/..', $container->getParameter('kernel.root_dir'))))
                ->addMethodCall('setThemeManager', array(new Reference(ThemeManager::class)))
            ;
        }
    }
}
