<?php

namespace Tms\Bundle\ThemeBundle\Tests\Twig;

use Tms\Bundle\ThemeBundle\Model\Theme;
use Tms\Bundle\ThemeBundle\Tests\Fixtures\somebundle\SomeBundle;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;
use Tms\Bundle\ThemeBundle\Twig\ThemeLoader;

class ThemeLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Return an instance of ThemeManager.
     *
     * @return ThemeManager
     */
    protected function getThemeManager(ThemeInterface $theme = null)
    {
        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects($this->any())
            ->method('getCurrentTheme')
            ->will($this->returnValue($theme))
        ;

        return $themeManager;
    }

    /**
     * Provider for the findTemplate test.
     *
     * @return array
     */
    public function findTemplateProvider()
    {
        // Models
        $theme = new Theme();
        $theme
            ->setId('theme')
            ->setName('The theme template')
        ;
        $subTheme = new Theme();
        $subTheme
            ->setId('subTheme')
            ->setName('The sub theme template')
            ->setParent($theme)
        ;

        return array(
            array($theme, 'unkwnon.html.twig', new \Twig_Error_Loader('')),
            array($theme, 'template.html.twig', "Some twig code from the test template\n"),
            array($theme, '#parent#template.html.twig', "Some twig code\n"),
            array($theme, '@Some/template.html.twig', "Some twig code from a bundle\n"),
            array($theme, 'SomeBundle::template.html.twig', "Some twig code from a bundle\n"),
            array($theme, '@Some/somecontroller/template.html.twig', "Some twig code from a controller\n"),
            array($theme, '@Some\\somecontroller////template.html.twig', "Some twig code from a controller\n"),
            array($theme, 'SomeBundle:somecontroller:template.html.twig', "Some twig code from a controller\n"),
            array($theme, '../template.html.twig', new \Twig_Error_Loader('')),
            array($theme, "template\0.html.twig", new \Twig_Error_Loader('')),
            array($subTheme, 'unkwnon.html.twig', new \Twig_Error_Loader('')),
            array($subTheme, 'template.html.twig', "Some twig code from the test template\n"),
            array($subTheme, '#parent#template.html.twig', "Some twig code from the test template\n"),
            array($subTheme, '#parent##parent#template.html.twig', "Some twig code\n"),
            array($subTheme, 'template2.html.twig', "Another template from sub theme\n"),
            array($subTheme, '#parent#template2.html.twig', new \Twig_Error_Loader('')),
            array($subTheme, '@Some/template.html.twig', "Some twig code from a bundle\n"),
            array($subTheme, 'SomeBundle::template.html.twig', "Some twig code from a bundle\n"),
            array($subTheme, '@Some/somecontroller/template.html.twig', "Some twig code from a controller\n"),
            array($subTheme, 'SomeBundle:somecontroller:template.html.twig', "Some twig code from a controller\n"),
        );
    }

    /**
     * Test the findTemplate method.
     *
     * @dataProvider findTemplateProvider
     *
     * @param ThemeInterface $theme    Instance of ThemeInterface
     * @param string         $template The template name
     * @param mixed          $expected The expected response
     */
    public function testFindTemplate(ThemeInterface $theme, $template, $expected)
    {
        // Instance to test
        $themeLoader = new ThemeLoader(
            array(
                'SomeBundle' => SomeBundle::class,
            ),
            sprintf('%s/Fixtures', dirname(__DIR__)),
            $this->getThemeManager()
        );

        try {
            $result = $themeLoader->findTemplate($theme, $template);

            // Test the result is as expected
            $this->assertInternalType('string', $result);
            $this->assertFileExists($result);
            $this->assertEquals($expected, file_get_contents($result));

            // Test the result is the same
            $this->assertEquals($result, $themeLoader->findTemplate($theme, $template));
        } catch (\Twig_Error_Loader $e) {
            // Test that an error is expected
            $this->assertInstanceOf(\Twig_Error_Loader::class, $expected);
        }
    }

    /**
     * Provider for the getSource.
     *
     * @return array
     */
    public function getSourceProvider()
    {
        return array_merge(
            array(
                array(null, 'unkwnon.html.twig', new \Twig_Error_Loader('')),
                array(null, 'template.html.twig', new \Twig_Error_Loader('')),
            ),
            $this->findTemplateProvider()
        );
    }

    /**
     * Test the getSource method.
     *
     * @dataProvider getSourceProvider
     *
     * @param ThemeInterface $theme    Instance of ThemeInterface
     * @param string         $template The template name
     * @param mixed          $expected The expected response
     */
    public function testGetSource(ThemeInterface $theme = null, $template, $expected)
    {
        // Instance to test
        $themeLoader = new ThemeLoader(
            array(
                'SomeBundle' => SomeBundle::class,
            ),
            sprintf('%s/Fixtures', dirname(__DIR__)),
            $this->getThemeManager($theme)
        );

        try {
            $result = $themeLoader->getSource($template);

            // Test the result is as expected
            $this->assertEquals($expected, $result);
        } catch (\Twig_Error_Loader $e) {
            // Test that an error is expected
            $this->assertInstanceOf(\Twig_Error_Loader::class, $expected);
        }
    }

    /**
     * Test the getSourceContext method.
     *
     * @dataProvider getSourceProvider
     *
     * @param ThemeInterface $theme    Instance of ThemeInterface
     * @param string         $template The template name
     * @param mixed          $expected The expected response
     */
    public function testGetSourceContext(ThemeInterface $theme = null, $template, $expected)
    {
        // Instance to test
        $themeLoader = new ThemeLoader(
            array(
                'SomeBundle' => SomeBundle::class,
            ),
            sprintf('%s/Fixtures', dirname(__DIR__)),
            $this->getThemeManager($theme)
        );

        try {
            $result = $themeLoader->getSourceContext($template);

            // Test the result is as expected
            $this->assertInstanceOf(\Twig_Source::class, $result);
            $this->assertEquals($expected, $result->getCode());
        } catch (\Twig_Error_Loader $e) {
            // Test that an error is expected
            $this->assertInstanceOf(\Twig_Error_Loader::class, $expected);
        }
    }

    /**
     * Provider for the getSource.
     *
     * @return array
     */
    public function existsProvider()
    {
        // Models
        $theme = new Theme();
        $theme->setId('theme');

        return array(
            array(null, 'unkwnon.html.twig', false),
            array(null, 'template.html.twig', false),
            array($theme, 'unkwnon.html.twig', false),
            array($theme, 'template.html.twig', true),
            array($theme, '#parent#template.html.twig', true),
            array($theme, '../template.html.twig', false),
            array($theme, '@Some/template.html.twig', true),
            array($theme, 'SomeBundle::template.html.twig', true),
            array($theme, '@Some/somecontroller/template.html.twig', true),
            array($theme, '@Some\\somecontroller////template.html.twig', true),
            array($theme, 'SomeBundle:somecontroller:template.html.twig', true),
        );
    }

    /**
     * Test the exists method.
     *
     * @dataProvider existsProvider
     *
     * @param ThemeInterface $theme    Instance of ThemeInterface
     * @param string         $template The template name
     * @param mixed          $expected The expected response
     */
    public function testExists(ThemeInterface $theme = null, $template, $expected)
    {
        // Instance to test
        $themeLoader = new ThemeLoader(
            array(
                'SomeBundle' => SomeBundle::class,
            ),
            sprintf('%s/Fixtures', dirname(__DIR__)),
            $this->getThemeManager($theme)
        );

        $result = $themeLoader->exists($template);

        // Test the result is as expected
        $this->assertInternalType('boolean', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test the getCacheKey method.
     */
    public function testGetCacheKey()
    {
        // Models
        $theme = new Theme();
        $theme
            ->setId('theme')
            ->setName('The test template')
        ;
        $subTheme = new Theme();
        $subTheme
            ->setId('subTheme')
            ->setName('The sub test template')
            ->setParent($theme)
        ;

        // Test without theme
        try {
            $themeLoader = new ThemeLoader(array(), sprintf('%s/Fixtures', dirname(__DIR__)), $this->getThemeManager());
            $themeLoader->getCacheKey('template.html.twig');

            $this->assertTrue(false);
        } catch (\Twig_Error_Loader $e) {
            $this->assertTrue(true);
        }

        // Test theme with unknown template
        try {
            $themeLoader = new ThemeLoader(
                array(),
                sprintf('%s/Fixtures', dirname(__DIR__)),
                $this->getThemeManager($theme)
            );
            $themeLoader->getCacheKey('unkown.html.twig');

            $this->assertTrue(false);
        } catch (\Twig_Error_Loader $e) {
            $this->assertTrue(true);
        }

        // Test standard behavior
        $themeLoader1 = new ThemeLoader(
            array(),
            sprintf('%s/Fixtures', dirname(__DIR__)),
            $this->getThemeManager($theme)
        );
        $cacheKey1 = $themeLoader1->getCacheKey('template.html.twig');
        $this->assertInternalType('string', $cacheKey1);

        // Test same template with different themes
        $themeLoader2 = new ThemeLoader(
            array(),
            sprintf('%s/Fixtures', dirname(__DIR__)),
            $this->getThemeManager($subTheme)
        );
        $cacheKey2 = $themeLoader2->getCacheKey('template.html.twig');
        $this->assertInternalType('string', $cacheKey2);

        $this->assertNotEquals($cacheKey1, $cacheKey2);

        // Test different template with same theme
        $cacheKey3 = $themeLoader2->getCacheKey('template2.html.twig');
        $this->assertInternalType('string', $cacheKey3);

        $this->assertNotEquals($cacheKey2, $cacheKey3);
    }

    /**
     * Provider for the getSource.
     *
     * @return array
     */
    public function isFreshProvider()
    {
        // Models
        $theme = new Theme();
        $theme->setId('theme');

        // ThemeLoader
        $themeLoader = new ThemeLoader(
            array(
                'SomeBundle' => SomeBundle::class,
            ),
            sprintf('%s/Fixtures', dirname(__DIR__)),
            $this->getThemeManager($theme)
        );

        // Template filemtime
        $time = filemtime($themeLoader->findTemplate($theme, 'template.html.twig'));

        return array(
            array(null, 'unkwnon.html.twig', 0, new \Twig_Error_Loader('')),
            array(null, 'template.html.twig', 0, new \Twig_Error_Loader('')),
            array($theme, 'unkwnon.html.twig', 0, new \Twig_Error_Loader('')),
            array($theme, 'template.html.twig', $time + 20000, true),
            array($theme, 'template.html.twig', $time - 20000, false),
        );
    }

    /**
     * Test the isFresh method.
     *
     * @dataProvider isFreshProvider
     *
     * @param ThemeInterface $theme    Instance of ThemeInterface
     * @param string         $template The template name
     * @param string         $time     The testing time
     * @param mixed          $expected The expected response
     */
    public function testIsFresh(ThemeInterface $theme = null, $template, $time, $expected)
    {
        // Instance to test
        $themeLoader = new ThemeLoader(
            array(
                'SomeBundle' => SomeBundle::class,
            ),
            sprintf('%s/Fixtures', dirname(__DIR__)),
            $this->getThemeManager($theme)
        );

        try {
            $result = $themeLoader->isFresh($template, $time);

            // Test the result is as expected
            $this->assertEquals($expected, $result);
        } catch (\Twig_Error_Loader $e) {
            // Test that an error is expected
            $this->assertInstanceOf(\Twig_Error_Loader::class, $expected);
        }
    }
}
