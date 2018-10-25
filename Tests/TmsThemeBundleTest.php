<?php

namespace Tms\Bundle\ThemeBundle\Tests;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Tms\Bundle\ThemeBundle\TmsThemeBundle;

class TmsThemeBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the bundle interface.
     */
    public function testBundleInterface()
    {
        $this->assertInstanceOf(BundleInterface::class, new TmsThemeBundle());
    }

    /**
     * Test the compiler paths are added in the build method.
     */
    public function testCompilerPaths()
    {
        $useCase = $this;

        // Mocks
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(array('addCompilerPass'))
            ->getMock()
        ;
        $container
            ->expects($this->any())
            ->method('addCompilerPass')
            ->will($this->returnCallback(function ($compilerClass) use ($useCase) {
                $class = new \ReflectionClass($compilerClass);

                $useCase->assertTrue($class->implementsInterface(CompilerPassInterface::class));
            }))
        ;

        // Instanciate the bundle
        $bundle = new TmsThemeBundle();

        // Test the build method
        $bundle->build($container);
    }
}
