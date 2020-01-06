<?php

namespace Tms\Bundle\ThemeBundle\Tests\Model;

use Tms\Bundle\ThemeBundle\Model\Theme;

class ThemeTest extends AbstractModelTest
{
    /**
     * {@inheritdoc}
     */
    protected function getClassName()
    {
        return Theme::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getterAndSetterProvider()
    {
        return array(
            array('id', 1234),
            array('name', 'The theme name'),
            array('parent', new Theme()),
            array('zones', array('default')),
            array('options', array()),
        );
    }
}
