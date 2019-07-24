<?php

namespace Tms\Bundle\ThemeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tms_theme');

        $rootNode
            ->children()
                ->arrayNode('themes')
                    ->useAttributeAsKey('id')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()->then(function ($value) {
                                return array('name' => $value);
                            })
                        ->end()
                        ->children()
                            ->scalarNode('id')->end()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('parent')->defaultValue(null)->end()
                            ->arrayNode('options')
                                ->defaultValue(array())
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('bundles')
                                ->setDeprecated('The TmsTheme "bundles" option have no longer effects.')
                                ->validate()
                                    ->always()
                                    ->thenEmptyArray()
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
