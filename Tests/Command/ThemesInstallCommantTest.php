<?php

namespace Tms\Bundle\ThemeBundle\Tests\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Tms\Bundle\ThemeBundle\Command\ThemesInstallCommand;
use Tms\Bundle\ThemeBundle\Model\Theme;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;
use Tms\Bundle\ThemeBundle\Tests\Fixtures\somebundle\SomeBundle;

class ThemesInstallCommantTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        // Remove existing test directory
        $path = sprintf('%s/tmp', $this->getTmpRootDir());
        if (file_exists($path)) {
            $this->rrmdir($path);
        }

        // Copy the fixtures to the tmp path
        $this->rcopy(sprintf('%s/Fixtures', dirname(__DIR__)), $path);

        // Create a temporary theme path and an outdated file
        mkdir(sprintf('%s/web/themes', $path), 0777);
        touch(sprintf('%s/web/themes/outdated.css', $path));
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $path = sprintf('%s/tmp', $this->getTmpRootDir());
        if (file_exists($path)) {
            $this->rrmdir($path);
        }
    }

    /**
     * Root dir for the current test.
     *
     * @return string
     */
    protected function getTmpRootDir()
    {
        return __DIR__;
    }

    /**
     * Test standard behavior of the execute method.
     */
    public function testExecute()
    {
        $testCase = $this;

        // Models
        $theme = new Theme();
        $theme->setId('theme');
        $subTheme = new Theme();
        $subTheme
            ->setId('subTheme')
            ->setParent($theme)
        ;

        // Mocks
        $container = $this->createMock(ContainerInterface::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $themeManager = $this->createMock(ThemeManager::class);

        $container
            ->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($name) {
                $value = null;
                switch ($name) {
                    case 'kernel.root_dir':
                        $value = sprintf('%s/tmp/app', $this->getTmpRootDir());
                        break;
                    case 'kernel.bundles':
                        $value = array(
                            'SomeBundle' => SomeBundle::class,
                        );
                        break;
                }

                return $value;
            }))
        ;
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) use ($themeManager) {
                $service = null;
                switch ($name) {
                    case 'filesystem':
                        $service = new Filesystem();
                        break;
                    case ThemeManager::class:
                        $service = $themeManager;
                        break;
                    default:
                        $service = $testCase->createMock($name);
                        break;
                }

                return $service;
            }))
        ;
        $input
            ->expects($this->any())
            ->method('getArgument')
            ->will($this->returnCallback(function ($name, $default = null) {
                if ('target' === $name) {
                    return 'web';
                }

                return $default;
            }))
        ;
        $themeManager
            ->expects($this->any())
            ->method('getThemes')
            ->will($this->returnValue(array($theme, $subTheme)))
        ;

        // Instanciate the class
        $command = new ThemesInstallCommand();
        $command->setContainer($container);

        // Execute the command
        $command->run($input, $output);

        // paths
        $fixturesPath = sprintf('%s/Fixtures', dirname(__DIR__));
        $assetPath = sprintf('%s/tmp/web/themes', $this->getTmpRootDir());

        // Test the previous assets are deleted
        $this->assertFileNotExists(sprintf('%s/outdated.css', $assetPath));

        // Test a simple theme...
        $this->assertFileExists(sprintf('%s/theme/css/sheet.css', $assetPath));
        $this->assertFileEquals(
            sprintf('%s/web/css/sheet.css', $fixturesPath),
            sprintf('%s/theme/css/sheet.css', $assetPath)
        );
        $this->assertFileExists(sprintf('%s/theme/images/logo.png', $assetPath));
        $this->assertFileEquals(
            sprintf('%s/app/Resources/themes/theme/public/images/logo.png', $fixturesPath),
            sprintf('%s/theme/images/logo.png', $assetPath)
        );

        // Test a sub theme
        $this->assertFileExists(sprintf('%s/subTheme/css/sheet.css', $assetPath));
        $this->assertFileEquals(
            sprintf('%s/app/Resources/themes/subTheme/public/css/sheet.css', $fixturesPath),
            sprintf('%s/subTheme/css/sheet.css', $assetPath)
        );
        $this->assertFileExists(sprintf('%s/subTheme/images/logo.png', $assetPath));
        $this->assertFileEquals(
            sprintf('%s/app/Resources/themes/theme/public/images/logo.png', $fixturesPath),
            sprintf('%s/subTheme/images/logo.png', $assetPath)
        );
    }

    /**
     * Recursivly copy a directory.
     *
     * @param string $src The source
     * @param string $dst The destination
     */
    protected function rcopy($src, $dst)
    {
        if (file_exists($dst)) {
            rrmdir($dst);
        }

        if (is_dir($src)) {
            mkdir($dst);
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $this->rcopy("$src/$file", "$dst/$file");
                }
            }
        } elseif (file_exists($src)) {
            copy($src, $dst);
        }
    }

    /**
     * Recursivly remove a directory.
     *
     * @param string $dir Directory name
     */
    protected function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir.'/'.$object) == 'dir') {
                        $this->rrmdir($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
