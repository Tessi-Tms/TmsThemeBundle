<?php

namespace Tms\Bundle\ThemeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
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
            ->setDefinition(array(
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'web'),
            ))
            ->setDescription('Installs WebConsumerApp specific assets under a public web directory')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command installs assets into a given
directory (e.g. the web directory).
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check the main directory
        $targetDirectory = rtrim($input->getArgument('target'), '/');
        if (!is_dir($targetDirectory)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $mainDirectory));
        }

        // Check the symlink option
        if (!function_exists('symlink') && $input->getOption('symlink')) {
            throw new \InvalidArgumentException('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }

        // Remove old assets
        $output->writeln('Removing outdated themes assets');
        $target = sprintf('%s/themes', $targetDirectory);
        $this
            ->getContainer()
            ->get('filesystem')
            ->remove($target)
        ;

        // Install assets for each themes
        $themeManager = $this->getContainer()->get(ThemeManager::class);
        foreach ($themeManager->getThemes() as $theme) {
            $output->writeln(sprintf('Installing the <info>%s</info> theme assets', $theme->getName()));
            $this->installThemeAssets($input, $output, $theme, sprintf('%s/%s', $target, $theme->getId()));
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
}
