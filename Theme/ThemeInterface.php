<?php

namespace Tms\Bundle\ThemeBundle\Theme;

interface ThemeInterface
{
    /**
     * Get the value of Theme identifier.
     *
     * @return string
     */
    public function getId();

    /**
     * Get the value of The theme name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get the value of The theme options.
     *
     * @return string
     */
    public function getOptions();

    /**
     * Get the value of The theme zones.
     *
     * @return string
     */
    public function getZones();

    /**
     * Get the value of The theme parent.
     *
     * @return Theme|null
     */
    public function getParent();
}
