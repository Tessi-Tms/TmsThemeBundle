<?php

namespace Tms\Bundle\ThemeBundle\Tests\Theme;

use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistry;

class ThemeRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the setTheme method.
     */
    public function testSetTheme()
    {
        $registry = new ThemeRegistry();

        // Test with a unknown object
        try {
            $registry->setTheme('unknownId', new \stdClass());
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        } finally {
            $this->assertFalse($registry->hasTheme('unknownId'));
        }

        // Test with a string
        try {
            $registry->setTheme('unknownId', 'SomeUnknownValue');
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        } finally {
            $this->assertFalse($registry->hasTheme('unknownId'));
        }

        // Simple test with a configuration
        $registry->setTheme('aThemeId', array(
            'id' => 123,
            'name' => 'The theme name',
        ));
        $this->assertContainsOnlyInstancesOf(ThemeInterface::class, $registry->getThemes());
        $theme = $registry->getTheme('aThemeId');
        $this->assertEquals(123, $theme->getId());
        $this->assertEquals('The theme name', $theme->getName());
        $this->assertNull($theme->getParent());

        // Test without id
        $registry->setTheme('anotherTheme', array(
            'name' => 'Another name',
        ));
        $this->assertContainsOnlyInstancesOf(ThemeInterface::class, $registry->getThemes());
        $theme = $registry->getTheme('anotherTheme');
        $this->assertEquals('anotherTheme', $theme->getId());
        $this->assertEquals('Another name', $theme->getName());
        $this->assertNull($theme->getParent());

        // Test with a parent
        $registry->setTheme('subTheme', array(
            'name' => 'Sub name',
            'parent' => 'anotherTheme',
        ));
        $this->assertContainsOnlyInstancesOf(ThemeInterface::class, $registry->getThemes());
        $theme = $registry->getTheme('subTheme');
        $this->assertEquals('subTheme', $theme->getId());
        $this->assertEquals('Sub name', $theme->getName());
        $parent = $theme->getParent();
        $this->assertInstanceOf(ThemeInterface::class, $parent);
        $this->assertEquals('Another name', $parent->getName());

        // Test with an unknown parent
        try {
            $registry->setTheme('subTheme2', array(
                'name' => 'Sub name',
                'parent' => 'unknownTheme',
            ));
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        } finally {
            $this->assertFalse($registry->hasTheme('subTheme2'));
        }

        // Test with a unknown parameter
        try {
            $registry->setTheme('misconfiguredTheme', array(
                'name' => 'Sub name',
                'someParameter' => 'some value',
            ));
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        } finally {
            $this->assertFalse($registry->hasTheme('misconfiguredTheme'));
        }
    }

    /**
     * Test all the methods of the registry.
     */
    public function testMethods()
    {
        $registry = new ThemeRegistry();

        $this->assertInternalType('array', $registry->getThemes());
        $this->assertEmpty($registry->getThemes());
        $this->assertInternalType('bool', $registry->hasTheme('firstTheme'));
        $this->assertFalse($registry->hasTheme('firstTheme'));

        // Set the first theme
        $firstTheme = $this->createMock(ThemeInterface::class);
        $registry->setTheme('firstTheme', $firstTheme);
        $this->assertInternalType('array', $registry->getThemes());
        $this->assertContainsOnlyInstancesOf(ThemeInterface::class, $registry->getThemes());
        $this->assertCount(1, $registry->getThemes());
        $this->assertArrayHasKey('firstTheme', $registry->getThemes());
        $this->assertContains($firstTheme, $registry->getThemes());
        $this->assertInternalType('bool', $registry->hasTheme('firstTheme'));
        $this->assertInternalType('bool', $registry->hasTheme('secondTheme'));
        $this->assertTrue($registry->hasTheme('firstTheme'));
        $this->assertFalse($registry->hasTheme('secondTheme'));
        $this->assertSame($firstTheme, $registry->getTheme('firstTheme'));

        try {
            $registry->getTheme('secondTheme');
            $this->assertFalse(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // Set the second theme
        $secondTheme = $this->createMock(ThemeInterface::class);
        $registry->setTheme('secondTheme', $secondTheme);
        $this->assertInternalType('array', $registry->getThemes());
        $this->assertContainsOnlyInstancesOf(ThemeInterface::class, $registry->getThemes());
        $this->assertCount(2, $registry->getThemes());
        $this->assertArrayHasKey('firstTheme', $registry->getThemes());
        $this->assertArrayHasKey('secondTheme', $registry->getThemes());
        $this->assertContains($firstTheme, $registry->getThemes());
        $this->assertContains($secondTheme, $registry->getThemes());
        $this->assertInternalType('bool', $registry->hasTheme('firstTheme'));
        $this->assertInternalType('bool', $registry->hasTheme('secondTheme'));
        $this->assertTrue($registry->hasTheme('firstTheme'));
        $this->assertTrue($registry->hasTheme('secondTheme'));
        $this->assertSame($firstTheme, $registry->getTheme('firstTheme'));
        $this->assertSame($secondTheme, $registry->getTheme('secondTheme'));

        // Replace the first theme
        $thirdTheme = $this->createMock(ThemeInterface::class);
        $registry->setTheme('firstTheme', $thirdTheme);
        $this->assertInternalType('array', $registry->getThemes());
        $this->assertContainsOnlyInstancesOf(ThemeInterface::class, $registry->getThemes());
        $this->assertCount(2, $registry->getThemes());
        $this->assertArrayHasKey('firstTheme', $registry->getThemes());
        $this->assertArrayHasKey('secondTheme', $registry->getThemes());
        $this->assertNotContains($firstTheme, $registry->getThemes());
        $this->assertContains($secondTheme, $registry->getThemes());
        $this->assertContains($thirdTheme, $registry->getThemes());
        $this->assertTrue($registry->hasTheme('firstTheme'));
        $this->assertTrue($registry->hasTheme('secondTheme'));
        $this->assertSame($thirdTheme, $registry->getTheme('firstTheme'));
        $this->assertSame($secondTheme, $registry->getTheme('secondTheme'));
    }
}
