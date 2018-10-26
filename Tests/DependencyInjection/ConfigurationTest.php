<?php

namespace Tms\Bundle\ThemeBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Tms\Bundle\ThemeBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the Configuration class.
     */
    public function testClass()
    {
        // Instanciate the class
        $configuration = new Configuration();

        // Test the interface
        $this->assertInstanceof(ConfigurationInterface::class, $configuration);
    }

    /**
     * Test the getConfigTreeBuilder method.
     */
    public function testGetConfigTreeBuilder()
    {
        // Instanciate the class
        $configuration = new Configuration();

        // Test the result is a TreeBuilder
        $this->assertInstanceof(TreeBuilder::class, $configuration->getConfigTreeBuilder());
    }
}
