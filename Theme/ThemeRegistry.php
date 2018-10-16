<?php

namespace Tms\Bundle\ThemeBundle\Theme;

use Tms\Bundle\ThemeBundle\Exception\ThemeNotFoundException;
use Tms\Bundle\ThemeBundle\Model\Theme;

class ThemeRegistry implements ThemeRegistryInterface
{
    /**
     * List of all availables themes.
     *
     * @var array<ThemeInterface>
     */
    protected $themes;

    /**
     * Contructor.
     */
    public function __construct()
    {
        $this->themes = array();
    }

    /**
     * {@inheritdoc}
     */
    public function setTheme($id, $theme)
    {
        if (is_array($theme)) {
            $rc = new \ReflectionClass(Theme::class);

            // Check the theme configuration
            foreach ($theme as $key => $value) {
                if (!$rc->hasProperty($key)) {
                    throw new \InvalidArgumentException(sprintf(
                        "Unknown property '%s' for the theme '%s'",
                        $key,
                        $id
                    ), 1);
                }
            }

            // Update the theme id
            if (!isset($theme['id'])) {
                $theme['id'] = $id;
            }

            // Find the parent theme
            try {
                if (isset($theme['parent'])) {
                    $theme['parent'] = $this->getTheme($theme['parent']);
                }
            } catch (ThemeNotFoundException $e) {
                throw new \InvalidArgumentException(sprintf(
                    "The parent theme '%s' must be registered before the '%s' theme.",
                    $theme['parent'],
                    $id
                ), 1);
            }

            // Instanciate the theme
            $theme = $rc->newInstance($theme);
        }

        if (!($theme instanceof ThemeInterface)) {
            throw new \InvalidArgumentException(sprintf(
                "The theme '%s' must be an instance of ThemeInterface or an array",
                $id
            ), 1);
        }

        // Register the theme
        $this->themes[$id] = $theme;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * {@inheritdoc}
     */
    public function getTheme($id)
    {
        if (!isset($this->themes[$id])) {
            throw new ThemeNotFoundException($id);
        }

        return $this->themes[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function hasTheme($id)
    {
        return isset($this->themes[$id]);
    }
}
