<?php

namespace Tms\Bundle\ThemeBundle\Composer;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as SensioScriptHandler;

class ScriptHandler extends SensioScriptHandler
{
    /**
     * Installs the themes assets under the web root directory.
     *
     * @param $event CommandEvent A instance
     */
    public static function installThemesAssets(CommandEvent $event)
    {
        $consoleDir = static::getConsoleDir($event, 'install theme assets');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'tms:themes:install');
    }
}
