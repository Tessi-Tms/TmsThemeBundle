<?php

namespace Tms\Bundle\ThemeBundle\Tests\Twig;

use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\Routing\Router;
use Tms\Bundle\ThemeBundle\Model\Theme;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;
use Tms\Bundle\ThemeBundle\Twig\ThemeExtension;

class ThemeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Return an instance of Router.
     *
     * @return Router
     */
    protected function getRouter()
    {
        return $this
            ->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock();
    }

    /**
     * Test the getFunctions method.
     */
    public function testGetFunctions()
    {
        // Instance to test
        $themeExtension = new ThemeExtension(
            $this->getRouter(),
            $this->createMock(ThemeManager::class)
        );

        // Test that get function always return a not empty array
        $functions = $themeExtension->getFunctions();
        $this->assertInternalType('array', $functions);
        $this->assertNotEmpty($functions);

        // test that the method return only instance of Twig_SimpleFunction
        foreach ($functions as $function) {
            $this->assertInstanceOf(\Twig_SimpleFunction::class, $function);
        }
    }

    /**
     * Test the templateParent method.
     */
    public function testTemplateParent()
    {
        // Instance to test
        $themeExtension = new ThemeExtension($this->getRouter(), $this->createMock(ThemeManager::class));

        // Test a simple use
        $result = $themeExtension->templateParent('template.html.twig');
        $this->assertInternalType('string', $result);
        $this->assertStringStartsWith('#parent#', $result);
        $this->assertStringEndsWith('template.html.twig', $result);
        $this->assertEquals(1, substr_count($result, '#parent#'));
        $this->assertEquals(1, substr_count($result, 'template.html.twig'));

        // Test a double execution
        $result = $themeExtension->templateParent($themeExtension->templateParent('template.html.twig'));
        $this->assertInternalType('string', $result);
        $this->assertStringStartsWith('#parent#', $result);
        $this->assertStringEndsWith('template.html.twig', $result);
        $this->assertEquals(2, substr_count($result, '#parent#'));
        $this->assertEquals(1, substr_count($result, 'template.html.twig'));
    }

    /**
     * Provider for the themeAsset test.
     *
     * @return array
     */
    public function themeAssetProvider()
    {
        // Model
        $theme = new Theme();
        $theme->setId('test');

        return array(
            array(null, 'dev', 'template.html.twig', 'template.html.twig'),
            array(null, 'prod', 'template.html.twig', 'template.html.twig'),
            array($theme, 'dev', 'template.html.twig', '/someroute/test/template.html.twig'),
            array($theme, 'prod', 'template.html.twig', '/themes/test/template.html.twig'),
            array($theme, null, 'template.html.twig', '/someroute/test/template.html.twig'),
            array($theme, 'other', 'template.html.twig', '/someroute/test/template.html.twig'),
        );
    }

    /**
     * Test the themeAsset method.
     *
     * @dataProvider themeAssetProvider
     *
     * @param ThemeInterface $theme    Instance of ThemeInterface
     * @param string         $env      The context environment (prod|dev)
     * @param string         $template The template name
     * @param string         $expected The expected result
     */
    public function testThemeAsset(ThemeInterface $theme = null, $env, $template, $expected)
    {
        // Models
        $context = array();
        if (null !== $env) {
            $app = null;
            if (in_array($env, array('dev', 'prod'))) {
                $app = new AppVariable();
                $app->setEnvironment($env);
            }

            $context = array(
                'app' => $app,
            );
        }

        // Mocks
        $router = $this->getRouter();
        $router
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(function ($route, $parameters) {
                return sprintf('/someroute/%s/%s', $parameters['theme'], $parameters['asset']);
            }))
        ;
        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects($this->any())
            ->method('getCurrentTheme')
            ->will($this->returnValue($theme))
        ;

        // Instance to test
        $themeExtension = new ThemeExtension($router, $themeManager);

        // Do the test
        $result = $themeExtension->themeAsset($context, $template);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }
}
