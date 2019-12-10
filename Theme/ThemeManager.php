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
     * The current theme options.
     *
     * @var array
     */
    protected $currentThemeOptions;

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
        $this->currentThemeOptions = array();
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
     * Return a specific theme.
     *
     * @return ThemeInterface
     */
    public function getTheme($id)
    {
        return $this->themeRegistry->getTheme($id);
    }

    /**
     * Set the current theme.
     *
     * @param mixed $theme   An instance of ThemeInterface or an identifier
     * @param array $options The theme options
     *
     * @throws \InvalidArgumentException
     * @throws ThemeNotFoundException
     */
    public function setCurrentTheme($theme, array $options = array())
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

        // Update the theme options
        $this->currentThemeOptions = $theme->getOptions();
        foreach ($options as $key => $value) {
            if (isset($this->currentThemeOptions[$key])) {
                $this->currentThemeOptions[$key] = $value;
            }
        }
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

    /**
     * Return the current theme options.
     *
     * @return array
     */
    public function getCurrentThemeOptions()
    {
        return $this->currentThemeOptions;
    }
}
