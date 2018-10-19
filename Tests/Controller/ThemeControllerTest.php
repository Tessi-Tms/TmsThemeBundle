<?php

namespace Tms\Bundle\ThemeBundle\Tests\Controller;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tms\Bundle\ThemeBundle\Controller\ThemeController;
use Tms\Bundle\ThemeBundle\Model\Theme;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;

class ThemeControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider for the assetAction test.
     *
     * @return array;
     */
    public function assetActionProvider()
    {
        // Models
        $theme = new Theme();
        $theme->setId('theme');
        $subTheme = new Theme();
        $subTheme
            ->setId('subTheme')
            ->setParent($theme)
        ;

        return array(
            array($theme, 'sheet.css', new NotFoundHttpException(), null),
            array($theme, 'css/sheet.css', "Some css code\n", "text/css"),
            array($subTheme, 'css/sheet.css', "Some updated css code for the subTheme\n", "text/css"),
            array($subTheme, 'images/logo.png', file_get_contents(sprintf(
                '%s/Fixtures/app/Resources/themes/theme/public/images/logo.png',
                dirname(__DIR__)
            )), "image/png"),
        );
    }

    /**
     * Test the assetAction method.
     *
     * @dataProvider assetActionProvider
     *
     * @param ThemeInterface $theme           Instance of ThemeInterface
     * @param string         $asset           The asset path
     * @param string         $expectedContent The expected response content
     * @param string         $expectedType    The expected response type
     */
    public function testAssetAction(ThemeInterface $theme, $asset, $expectedContent, $expectedType)
    {
        // Mocks
        $container = $this->createMock(Container::class);
        $container
            ->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function($name){
                if ('kernel.root_dir' === $name) {
                    return sprintf('%s/Fixtures/app', dirname(__DIR__));
                }

                return null;
            }))
        ;

        // Instance to test
        $themeController = new ThemeController();
        $themeController->setContainer($container);

        try {
            $response = $themeController->assetAction(new Request, $theme, $asset);
            $this->assertInstanceof(Response::class, $response);
            $this->assertEquals($expectedContent, $response->getContent());
            $this->assertEquals($expectedType, $response->headers->get('content-type'));
        } catch (NotFoundHttpException $e) {
            $this->assertInstanceof(NotFoundHttpException::class, $expectedContent);
        }
    }
}
