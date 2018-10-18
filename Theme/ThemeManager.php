<?php

namespace Tms\Bundle\ThemeBundle\Theme;

use Tms\Bundle\ThemeBundle\Exception\ThemeNotFoundException;

class ThemeManager
{
    /**
     * Instance of ThemeInterface.
     *
     * @var ThemeInterface
     */
    protected $currentTheme;

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
        $this->currentTheme = null;
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
     * Set the current theme.
     *
     * @param mixed $theme An instance of ThemeInterface or an identifier
     *
     * @throws \InvalidArgumentException
     * @throws ThemeNotFoundException
     */
    public function setCurrentTheme($theme)
    {
        // Reset active theme on null
        if (is_null($theme)) {
            $this->currentTheme = null;

            return;
        }

        // Retrieve the theme from his id
        if (is_string($theme)) {
            $theme = $this->themeRegistry->getTheme($theme);
        }

        if (!($theme instanceof ThemeInterface)) {
            throw new \InvalidArgumentException('The theme must be an instance of ThemeInterface or an string', 1);
        }

        $this->currentTheme = $theme;
    }

    /**
     * Return the current theme.
     *
     * @return ThemeInterface|null
     */
    public function getCurrentTheme()
    {
        return $this->currentTheme;
    }
}
