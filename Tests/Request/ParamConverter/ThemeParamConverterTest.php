<?php

namespace Tms\Bundle\ThemeBundle\Tests\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Tms\Bundle\ThemeBundle\Model\Theme;
use Tms\Bundle\ThemeBundle\Request\ParamConverter\ThemeParamConverter;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistryInterface;
use Tms\Bundle\ThemeBundle\Exception\ThemeNotFoundException;

class ThemeParamConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider fot the supports method test.
     *
     * @return array
     */
    public function supportsProvider()
    {
        return array(
            array(null, false),
            array(ParamConverter::class, false),
            array(ThemeRegistryInterface::class, false),
            array(ThemeInterface::class, true),
            array(Theme::class, true),
            array('test', false),
        );
    }

    /**
     * Test the supports method.
     *
     * @dataProvider supportsProvider
     *
     * @param string $class    This class is supported
     * @param bool   $expected Is the given class is espected to be supported
     */
    public function testSupports($class, $expected)
    {
        // Model
        $paramConverter = new ParamConverter(array());
        if (is_string($class)) {
            $paramConverter->setClass($class);
        }

        // Instance to test
        $themeParamConverter = new ThemeParamConverter($this->createMock(ThemeRegistryInterface::class));

        // Execute the test
        $supports = $themeParamConverter->supports($paramConverter);
        $this->assertInternalType('boolean', $supports);
        $this->assertSame($expected, $supports);
    }

    /**
     * Provider for the applys method test.
     *
     * @return array
     */
    public function applyProvider()
    {
        return array(
            array(new Request(), false),
            array(new Request([], [], array('theme' => null)), true),
            array(new Request([], [], array('theme' => 'test')), true),
            array(new Request([], [], array('theme' => 'unknown')), new ThemeNotFoundException('unknown')),
        );
    }

    /**
     * Test the apply method.
     *
     * @dataProvider applyProvider
     *
     * @param Request $request  Instance of Request
     * @param mixed   $expected The expected result
     */
    public function testApply(Request $request, $expected)
    {
        // Model
        $paramConverter = new ParamConverter(array());
        $paramConverter->setName('theme');
        $paramConverter->setIsOptional(true);

        $themeValue = $request->get('theme', null);

        // Mocks
        $themeRegistry = $this->createMock(ThemeRegistryInterface::class);
        $themeRegistry
            ->expects($this->any())
            ->method('getTheme')
            ->will($this->returnCallback(function ($value) {
                if ('test' !== $value) {
                    throw new ThemeNotFoundException($value);
                }

                return $this->createMock(ThemeInterface::class);
            }))
        ;

        // Instance to test
        $themeParamConverter = new ThemeParamConverter($themeRegistry);

        // Execute the test
        try {
            $result = $themeParamConverter->apply($request, $paramConverter);

            $this->assertEquals($expected, $result);
            if ($result && $themeValue) {
                $this->assertInstanceOf(ThemeInterface::class, $request->get('theme'));
            } else {
                $this->assertNull($request->get('theme'));
            }
        } catch (ThemeNotFoundException $e) {
            $this->assertEquals($expected, $e);
        }
    }
}
