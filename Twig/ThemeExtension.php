<?php
namespace Tms\Bundle\ThemeBundle\Twig;

class ThemeExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('templateParent', array($this, 'templateParent')),
        );
    }

    /**
     * Transform the template name for retrieve parent template.
     *
     * @param string $name The template name
     *
     * @return string
     */
    public function templateParent($name)
    {
        return sprintf('#parent#%s', $name);
    }
}
