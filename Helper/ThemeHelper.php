<?php
namespace Tms\Bundle\ThemeBundle\Helper;

use Tms\Bundle\ThemeBundle\Exception\ThemeNotFoundException;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistry;

class ThemeHelper
{
    /**
     * Instance of ThemeInterface.
     *
     * @var ThemeInterface
     */
    protected $activeTheme;

    /**
     * Instance of ThemeRegistry.
     *
     * @var ThemeRegistry
     */
    protected $themeRegistry;

    /**
     * Constructor.
     *
     * @param ThemeRegistry $themeRegistry Instance of ThemeRegistry
     */
    public function __construct(ThemeRegistry $themeRegistry)
    {
        $this->activeTheme = null;
        $this->themeRegistry = $themeRegistry;
    }

    /**
     * Return all the available themes.
     *
     * @return array<ThemeInterface>
     */
    public function getThemes()
    {
        return $this->themeRegistry->getThemes();
    }

    /**
     * Set the active theme.
     *
     * @param mixed $theme An instance of ThemeInterface or an identifier
     *
     * @throws \InvalidArgumentException
     * @throws ThemeNotFoundException
     */
    public function setActiveTheme($theme)
    {
        // Reset active theme on null
        if (is_null($theme)) {
            $this->activeTheme = null;

            return;
        }

        // Retrieve the theme from his id
        if(is_string($theme))
        {
            $theme = $this->themeRegistry->getTheme($theme);
        }

        if (! ($theme instanceof ThemeInterface)) {
            throw new \InvalidArgumentException("The theme must be an instance of ThemeInterface or an string", 1);
        }

        $this->activeTheme = $theme;
    }

    /**
     * Return the current theme.
     *
     * @return ThemeInterface|null
     */
    public function getActiveTheme()
    {
        return $this->activeTheme;
    }
}
