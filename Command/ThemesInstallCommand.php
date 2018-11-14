<?php

namespace Tms\Bundle\ThemeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;

class ThemesInstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tms:themes:install')
            ->setDescription('Installs assets under a public web directory')
            ->setHelp("The <info>%command.name%</info> command installs assets under a public web directory'.")
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Remove old assets
        $output->writeln('Removing outdated themes assets');
        $target = sprintf('%s/web/themes', dirname($this->getContainer()->getParameter('kernel.root_dir')));

        $this
            ->getContainer()
            ->get('filesystem')
            ->remove($target)
        ;

        // Install assets for each themes
        $themeManager = $this->getContainer()->get(ThemeManager::class);
        foreach ($themeManager->getThemes() as $theme) {
            $output->writeln(sprintf('Installing the <info>%s</info> theme assets', $theme->getName()));
            $this->installThemeAssets($input, $output, $theme, sprintf('web/themes/%s', $theme->getId()));
        }
    }

    /**
     * Install all the assets for a theme.
     *
     * @param InputInterface  $input  Instance of InputInterface
     * @param OutputInterface $output Instance of OutputInterface
     * @param ThemeInterface  $theme  Instance of ThemeInterface
     * @param string          $path   The target path
     */
    protected function installThemeAssets(InputInterface $input, OutputInterface $output, ThemeInterface $theme, $path)
    {
        $rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));
        $filesystem = $this->getContainer()->get('filesystem');

        if ($theme->getParent()) {
            $this->installThemeAssets($input, $output, $theme->getParent(), $path);
        } else {
            $from = sprintf('%s/web', $rootDir);
            $to = sprintf('%s/%s', $rootDir, $path);
            $finder = new Finder();
            $finder
                ->directories()
                ->in($from)
                ->exclude('bundles')
                ->exclude('themes')
            ;
            foreach ($finder as $dir) {
                $this->dump(
                    $input,
                    $output,
                    sprintf('%s/%s', $from, $dir->getFileName()),
                    sprintf('%s/%s', $to, $dir->getFileName())
                );
            }
        }

        // Dump bundles assets
        foreach ($this->getContainer()->getParameter('kernel.bundles') as $name => $bundle) {
            $rc = new \ReflectionClass($bundle);

            $from = sprintf('%s/Resources/themes/%s/public', dirname($rc->getFileName()), $theme->getId());
            $to = sprintf('%s/%s/bundles/%s', $rootDir, $path, strtolower($name));
            if ($filesystem->exists($from)) {
                $this->dump($input, $output, $from, $to);
            }
        }

        // Dump global assets
        $from = sprintf('%s/app/Resources/themes/%s/public', $rootDir, $theme->getId());
        $to = sprintf('%s/%s', $rootDir, $path);
        if ($filesystem->exists($from)) {
            foreach (Finder::create()->directories()->depth(0)->in($from) as $dir) {
                $this->dump(
                    $input,
                    $output,
                    sprintf('%s/%s', $from, $dir->getFileName()),
                    sprintf('%s/%s', $to, $dir->getFileName())
                );
            }
        }

        // Convert less files
        if (class_exists('lessc')) {
            $this->convertLessFiles($input, $output, $path);
        }
    }

    /**
     * Dump an asset directory.
     *
     * @param InputInterface  $input  Instance of InputInterface
     * @param OutputInterface $output Instance of OutputInterface
     * @param string          $from   The asset source
     * @param string          $to     The asset destination
     */
    protected function dump(InputInterface $input, OutputInterface $output, $from, $to)
    {
        $rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));
        $output->writeln(sprintf(
            'Dumping <comment>%s</comment> to <comment>%s</comment>',
            str_replace("$rootDir/", '', $from),
            str_replace("$rootDir/", '', $to)
        ));
        $this
            ->getContainer()
            ->get('filesystem')
            ->mirror($from, $to, Finder::create()->in($from), array(
                'override' => true,
            ))
        ;
    }

    /**
     * Convert all the less file in the path.
     *
     * @param InputInterface  $input  Instance of InputInterface
     * @param OutputInterface $output Instance of OutputInterface
     * @param string          $path   The file path
     */
    protected function convertLessFiles(InputInterface $input, OutputInterface $output, $path)
    {
        $lessCompiler = new \lessc();
        $rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));
        $output->writeln(sprintf(
            'Converting less files from <comment>%s</comment>',
            str_replace("$rootDir/", '', $path)
        ));

        $finder = Finder::create()
            ->in($path)
            ->name('*.less')
            ->files()
        ;
        foreach ($finder as $file) {
            $this
                ->getContainer()
                ->get('filesystem')
                ->dumpFile(
                    sprintf('%s.css',$file->getRealPath()),
                    $lessCompiler->compileFile($file->getRealPath())
                )
            ;
        }
    }
}
