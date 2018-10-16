<?php

namespace Tms\Bundle\ThemeBundle\Exception;

class ThemeNotFoundException extends \Exception
{
    public function __construct($theme)
    {
        parent::__construct(sprintf('The theme %s was not found.', $theme));
    }
}
