<?php

namespace Tms\Bundle\ThemeBundle\Tests\Translation;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Tms\Bundle\ThemeBundle\Model\Theme;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;
use Tms\Bundle\ThemeBundle\Translation\ThemeTranslator;
use Tms\Bundle\ThemeBundle\Tests\Fixtures\somebundle\SomeBundle;
use Tms\Bundle\ThemeBundle\Tests\Fixtures\anotherbundle\AnotherBundle;

class ThemeTranslatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider for the ThemeTranslator class test.
     *
     * @return array
     */
    public function themeTranslatorProvider()
    {
        return array(
            array(null, 'test', null, array(), null, null, 'test'),
            array('theme', 'test', null, array(), null, null, 'Message from the tessi theme'),
            array('subTheme', 'test', null, array(), null, null, 'Message from a sub theme'),
            array('theme', 'test', null, array(), null, 'fr', 'Message du theme tessi'),
            array('subTheme', 'test', null, array(), null, 'fr', 'Message du theme tessi'),
            array('theme', 'test', null, array(), 'errors', null, 'This is a test error!'),
            array('subTheme', 'test', null, array(), 'errors', null, 'This is a test error!'),
            array('theme', 'apple', 0, array(), 'messages', null, 'There are no apples'),
            array('subTheme', 'apple', 0, array(), 'messages', null, 'There are no pears'),
            array('theme', 'apple', 0, array(), 'messages', 'en', 'There are no apples'),
            array('subTheme', 'apple', 0, array(), 'messages', 'en', 'There are no pears'),
            array('theme', 'apple', 1, array(), 'messages', null, 'There is one apple'),
            array('subTheme', 'apple', 1, array(), 'messages', null, 'There is one pear'),
            array('theme', 'apple', 99, array(), 'messages', null, 'There are 99 apples'),
            array('subTheme', 'apple', 99, array(), 'messages', null, 'There are 99 pears'),
            array('theme', 'apple', 99, array(), 'messages', 'fr', 'apple'),
            array('subTheme', 'apple', 99, array(), 'messages', 'fr', 'apple'),
            array('theme', 'param', 0, array('%name%' => 'toto'), 'messages', 'fr', 'Bonjour toto'),
            array('theme', 'param', 0, array('%name%' => 'toto'), 'messages', null, 'Hello toto'),
            array('theme', 'somebundle', 0, array(), null, null, 'Message from some bundle'),
            array('theme', 'somebundle', 0, array(), null, 'en', 'Message from some bundle'),
            array('theme', 'somebundle', 0, array(), 'messages', null, 'Message from some bundle'),
            array('theme', 'somebundle', 0, array(), 'messages', 'en', 'Message from some bundle'),
            array('subTheme', 'somebundle', 0, array(), null, null, 'Surcharged bundle message'),
        );
    }

    /**
     * Test the ThemeTranslator class.
     *
     * @dataProvider themeTranslatorProvider
     *
     * @param ThemeInterface $theme      Instance of ThemeInterface
     * @param string         $id         The template name
     * @param string         $number     The pluralization option
     * @param string         $parameters The default domain
     * @param string         $domain     The default domain
     * @param string         $locale     The default local
     * @param string         $expected   The expected result
     */
    public function testThemeTranslator(
        $themeId = null,
        $id = '',
        $number = null,
        array $parameters = null,
        $domain = null,
        $locale = null,
        $expected = ''
    ) {
        // Models
        $theme = new Theme();
        $theme->setId('theme');
        $subTheme = new Theme();
        $subTheme
            ->setId('subTheme')
            ->setParent($theme)
        ;

        // Available themes
        $themes = array(
            'theme' => $theme,
            'subTheme' => $subTheme,
        );

        // Mocks
        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects($this->any())
            ->method('getCurrentTheme')
            ->will($this->returnCallback(function () use ($themeId, $themes) {
                return isset($themes[$themeId]) ? $themes[$themeId] : null;
            }))
        ;
        $themeManager
            ->expects($this->any())
            ->method('getThemes')
            ->will($this->returnValue($themes))
        ;

        // Instance to test
        $themeTranslator = new ThemeTranslator(
            $this->createMock(ContainerInterface::class),
            new MessageSelector(),
            'en',
            array(),
            array()
        );

        $themeTranslator->addLoader('php', new PhpFileLoader());
        $themeTranslator->addLoader('xliff', new XliffFileLoader());
        $themeTranslator->addLoader('yml', new YamlFileLoader());
        $themeTranslator->setBundles(array(
            'SomeBundle' => SomeBundle::class,
            'AnotherBundle' => AnotherBundle::class,
        ));
        $themeTranslator->setRootPath(sprintf('%s/Fixtures', dirname(__DIR__)));
        $themeTranslator->setThemeManager($themeManager);

        if (is_null($number)) {
            $this->assertEquals($expected, $themeTranslator->trans($id, $parameters, $domain, $locale));
        } else {
            $this->assertEquals($expected, $themeTranslator->transChoice($id, $number, $parameters, $domain, $locale));
        }
    }

    /**
     * Test the ThemeTranslator with no bundles.
     */
    public function testThemeTranslatorWithNoBundles()
    {
        // Models
        $theme = new Theme();
        $theme->setId('theme');
        $subTheme = new Theme();
        $subTheme
            ->setId('subTheme')
            ->setParent($theme)
        ;

        // Available themes
        $themes = array(
            'theme' => $theme,
            'subTheme' => $subTheme,
        );

        // Mocks
        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects($this->any())
            ->method('getCurrentTheme')
            ->will($this->returnValue($theme))
        ;
        $themeManager
            ->expects($this->any())
            ->method('getThemes')
            ->will($this->returnValue($themes))
        ;

        // Instance to test
        $themeTranslator = new ThemeTranslator(
            $this->createMock(ContainerInterface::class),
            new MessageSelector(),
            'en',
            array(),
            array()
        );

        $themeTranslator->addLoader('php', new PhpFileLoader());
        $themeTranslator->addLoader('xliff', new XliffFileLoader());
        $themeTranslator->addLoader('yml', new YamlFileLoader());
        $themeTranslator->setRootPath(sprintf('%s/Fixtures', dirname(__DIR__)));
        $themeTranslator->setThemeManager($themeManager);

        $this->assertEquals('Message from the tessi theme', $themeTranslator->trans('test'));
    }
}
