<?php

namespace Tms\Bundle\ThemeBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tms\Bundle\ThemeBundle\DependencyInjection\TmsThemeExtension;

class TmsThemeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider for the load method test.
     *
     * @return array
     */
    public function loadProvider()
    {
        return array(
            array(
                array(),
                array(),
            ),
            array(
                array('nothing'),
                InvalidConfigurationException::class,
            ),
            array(
                array(
                    array(
                        'themes' => array(),
                    ),
                ),
                array(),
            ),
            array(
                array(
                    array(
                        'themes' => array(
                            'theme' => array(),
                        ),
                    ),
                ),
                InvalidConfigurationException::class,
            ),
            array(
                array(
                    array(
                        'themes' => array(
                            'theme' => 'The theme name',
                        ),
                    ),
                ),
                array(
                    'theme' => array(
                        'name' => 'The theme name',
                        'parent' => null,
                    ),
                ),
            ),
            array(
                array(
                    array(
                        'themes' => array(
                            'theme' => array(
                                'name' => 'The theme name',
                                'parent' => 'unknown',
                            ),
                        ),
                    ),
                ),
                InvalidConfigurationException::class,
            ),
            array(
                array(
                    array(
                        'themes' => array(
                            'theme' => 'The theme name',
                            'subTheme' => array(
                                'name' => 'A sub theme',
                                'parent' => 'theme',
                            ),
                        ),
                    ),
                ),
                array(
                    'theme' => array(
                        'name' => 'The theme name',
                        'parent' => null,
                    ),
                    'subTheme' => array(
                        'name' => 'A sub theme',
                        'parent' => 'theme',
                    ),
                ),
            ),
            array(
                array(
                    array(
                        'themes' => array(
                            'theme' => 'The theme name',
                        ),
                    ),
                    array(
                        'themes' => array(
                            array(
                                'id' => 'subTheme',
                                'name' => 'A sub theme',
                                'parent' => 'theme',
                            ),
                        ),
                    ),
                ),
                array(
                    'theme' => array(
                        'name' => 'The theme name',
                        'parent' => null,
                    ),
                    'subTheme' => array(
                        'name' => 'A sub theme',
                        'parent' => 'theme',
                    ),
                ),
            ),
            array(
                array(
                    array(
                        'themes' => array(
                            array(
                                'id' => 'subTheme',
                                'name' => 'A sub theme',
                                'parent' => 'theme',
                            ),
                        ),
                    ),
                    array(
                        'themes' => array(
                            'theme' => 'The theme name',
                        ),
                    ),
                ),
                InvalidConfigurationException::class,
            ),
            array(
                array(
                    array(
                        'themes' => array(
                            'theme' => array(
                                'name' => 'The theme name',
                                'bundles' => array('SomeBundle', 'AnotherBundle'),
                            ),
                        ),
                    ),
                ),
                array(
                    'theme' => array(
                        'name' => 'The theme name',
                        'parent' => null,
                    ),
                ),
            ),
            array(
                array(
                    array(
                        'themes' => array(
                            'theme' => array(
                                'name' => 'The theme name',
                                'something' => 'what??',
                            ),
                        ),
                    ),
                ),
                InvalidConfigurationException::class,
            ),
        );
    }

    /**
     * Test the load method.
     *
     * @dataProvider loadProvider
     *
     * @param array $configs  The bundle configuration
     * @param mixed $expected The expected themes loaded from the configuration
     */
    public function testLoad(array $configs, $expected)
    {
        // Mocks
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(array('setParameter'))
            ->getMock()
        ;
        if (is_array($expected)) {
            $container
                ->expects($this->once())
                ->method('setParameter')
                ->with(
                    $this->equalTo('tms.themes.all'),
                    $this->logicalAnd($this->isType('array'), $this->equalTo($expected))
                )
            ;
        }

        // Instanciate the class
        $extension = new TmsThemeExtension();

        // Execute the method
        try {
            $extension->load($configs, $container);
        } catch (\Exception $e) {
            if (!is_string($expected)) {
                throw $e;
            }

            $this->assertInstanceOf($expected, $e);
        }
    }
}
