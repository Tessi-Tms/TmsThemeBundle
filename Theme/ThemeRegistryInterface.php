<?php
namespace Tms\Bundle\ThemeBundle\Theme;

use Tms\Bundle\ThemeBundle\Exception\ThemeNotFoundException;

interface ThemeRegistryInterface
{
    /**
     * Add a theme identified by id.
     * If an array is provided, it will be converted in an instance of ThemeInterface.
     *
     * @param string               $id    The theme identifier
     * @param ThemeInterface|array $theme Instance of theme
     *
     * @throws \InvalidArgumentException
     */
    public function setTheme($id, $theme);

    /**
     * Get the value of the available themes
     *
     * @return array<ThemeInterface>
     */
    public function getThemes();

    /**
     * Retrieve a theme by his id.
     *
     * @param $id The theme identifier
     *
     * @return ThemeInterface
     *
     * @throws ThemeNotFoundException
     */
    public function getTheme($id);

    /**
     * Returns whether the theme exists.
     *
     * @param $id The theme identifier
     *
     * @return boolean
     */
    public function hasTheme($id);
}
