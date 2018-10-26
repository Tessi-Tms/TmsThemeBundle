<?php

namespace Tms\Bundle\ThemeBundle\Tests\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Tms\Bundle\ThemeBundle\DependencyInjection\Compiler\ThemeCompilerPass;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistry;
use Tms\Bundle\ThemeBundle\Translation\ThemeTranslator;

class ThemeCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the process method.
     */
    public function testProcess()
    {
        // Mocks
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(array('hasDefinition', 'getDefinition', 'getParameter'))
            ->getMock()
        ;
        $themeRegistry = $this->createMock(Definition::class);
        $translator = $this->createMock(Definition::class);

        $container
            ->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnCallback(function ($name) {
                $definition = false;
                switch ($name) {
                    case ThemeRegistry::class:
                        $definition = true;
                        break;
                    case 'translator.default':
                        $definition = true;
                        break;
                }

                return $definition;
            }))
        ;
        $container
            ->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnCallback(function ($name) use ($themeRegistry, $translator) {
                $definition = null;
                switch ($name) {
                    case ThemeRegistry::class:
                        $definition = $themeRegistry;
                        break;
                    case 'translator.default':
                        $definition = $translator;
                        break;
                }

                return $definition;
            }))
        ;
        $container
            ->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($name) {
                if ('tms.themes.all' === $name) {
                    return array(
                        'theme' => array(
                            'name' => 'The theme',
                            'parent' => null,
                        ),
                        'subTheme' => array(
                            'name' => 'Another theme',
                            'parent' => 'theme',
                        ),
                    );
                }

                return null;
            }))
        ;
        $themeRegistry
            ->expects($this->exactly(2))
            ->method('addMethodCall')
            ->with($this->equalTo('setTheme'), $this->isType('array'))
        ;
        $translator
            ->expects($this->once())
            ->method('setClass')
            ->with($this->equalTo(ThemeTranslator::class))
            ->will($this->returnSelf())
        ;
        $translator
            ->expects($this->any())
            ->method('addMethodCall')
            ->will($this->returnSelf())
        ;

        // Instanciate the class
        $compilerPass = new ThemeCompilerPass();

        // Execute the test
        $compilerPass->process($container);
    }
}
