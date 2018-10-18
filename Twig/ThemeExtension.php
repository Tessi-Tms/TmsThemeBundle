<?php

namespace Tms\Bundle\ThemeBundle\Twig;

use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\Routing\Router;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;

class ThemeExtension extends \Twig_Extension
{
    /**
     * Instance of Router.
     *
     * @var Router
     */
    protected $router;

    /**
     * Instance of ThemeManager.
     *
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * Constructor.
     *
     * @param Router       $router       Instance of Router
     * @param ThemeManager $themeManager Instance of ThemeManager
     */
    public function __construct(Router $router, ThemeManager $themeManager)
    {
        $this->router = $router;
        $this->themeManager = $themeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('templateParent', array($this, 'templateParent')),
            new \Twig_SimpleFunction('themeAsset', array($this, 'themeAsset'), array('needs_context' => true)),
        );
    }

    /**
     * Transform the template name in order to retrieve parent template.
     *
     * @param string $name The template name
     *
     * @return string
     */
    public function templateParent($name)
    {
        return sprintf('#parent#%s', $name);
    }

    /**
     * Transform the asset name in order to search in the theme assets.
     *
     * @param mixed  $context The execution context
     * @param string $name    The asset name
     *
     * @return string
     */
    public function themeAsset($context, $name)
    {
        // Retrieve the current theme
        $theme = $this->themeManager->getCurrentTheme();

        // Use default behavior without theme
        if (null === $theme) {
            return $name;
        }

        // Retrieve the current env
        $env = 'dev';
        if (isset($context['app']) && ($context['app'] instanceof AppVariable)) {
            $env = $context['app']->getEnvironment();
        }

        // Use static files for the prod environment
        if ('prod' == $env) {
            return sprintf(
                '/themes/%s/%s',
                $theme->getId(),
                $name
            );
        }

        // Use the asset controller
        return $this->router->generate('tms_theme_asset', array(
            'theme' => $theme->getId(),
            'asset' => $name,
        ));
    }
}
