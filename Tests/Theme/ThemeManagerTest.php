<?php

namespace Tms\Bundle\ThemeBundle\Tests\Theme;

use Tms\Bundle\ThemeBundle\Exception\ThemeNotFoundException;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistry;

class ThemeManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the getThemes method.
     */
    public function testGetThemes()
    {
        // Mocks
        $themeRegistry = $this->createMock(ThemeRegistry::class);
        $themeRegistry
            ->expects($this->any())
            ->method('getThemes')
            ->will($this->returnValue(array(
                $this->createMock(ThemeInterface::class),
                $this->createMock(ThemeInterface::class),
            )))
        ;

        // Instance to test
        $themeManager = new ThemeManager($themeRegistry);

        // Test this method is a proxy
        $themes = $themeManager->getThemes();
        $this->assertInternalType('array', $themes);
        $this->assertContainsOnlyInstancesOf(ThemeInterface::class, $themes);
        $this->assertSame($themeRegistry->getThemes(), $themes);
    }

    /**
     * test the setActiveTheme and the getActiveTheme methods.
     */
    public function testGetAndSetCurrent()
    {
        // Models
        $theTheme = $this->createMock(ThemeInterface::class);
        $anotherTheme = $this->createMock(ThemeInterface::class);

        // Mocks
        $themeRegistry = $this->createMock(ThemeRegistry::class);
        $themeRegistry
            ->expects($this->any())
            ->method('getTheme')
            ->will($this->returnCallback(function ($theme) use ($theTheme) {
                if ('theTheme' == $theme) {
                    return $theTheme;
                }

                throw new ThemeNotFoundException($theme);
            }))
        ;

        // Instance to test
        $themeManager = new ThemeManager($themeRegistry);

        // Test initial value
        $this->assertNull($themeManager->getCurrentTheme());

        // Test with an instance of Theme
        $themeManager->setCurrentTheme($anotherTheme);
        $this->assertInstanceOf(ThemeInterface::class, $themeManager->getCurrentTheme());
        $this->assertSame($anotherTheme, $themeManager->getCurrentTheme());

        // Test the null value
        $themeManager->setCurrentTheme(null);
        $this->assertNull($themeManager->getCurrentTheme());

        // Test with an existing theme identifier
        $themeManager->setCurrentTheme('theTheme');
        $this->assertInstanceOf(ThemeInterface::class, $themeManager->getCurrentTheme());
        $this->assertSame($theTheme, $themeManager->getCurrentTheme());

        // Test with an unknown theme identifier
        try {
            $themeManager->setCurrentTheme('unknown');
            $this->assertTrue(false);
        } catch (ThemeNotFoundException $e) {
            $this->assertTrue(true);
        }

        // Test with an unknown object
        try {
            $themeManager->setCurrentTheme(new \stdClass());
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}
