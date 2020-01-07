<?php

namespace Tms\Bundle\ThemeBundle\Twig;

use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\Routing\Router;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;

class ThemeExtension extends \Twig_Extension
{
    /**
     * Instance of lessc.
     *
     * @var \lessc
     */
    protected $lessCompiler = null;

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

        if (class_exists('lessc')) {
            $this->lessCompiler = new \lessc();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('templateParent', array($this, 'templateParent')),
            new \Twig_SimpleFunction('themeAsset', array($this, 'themeAsset'), array('needs_context' => true)),
            new \Twig_SimpleFunction('themeOptions', array($this, 'themeOptions')),
        );
    }

    /**
     * Transform the template name in order to retrieve parent template.
     *
     * @param string $name    The template name
     * @param string $themeId Identifier of the template where the function is called
     *
     * @return string
     */
    public function templateParent($name, $themeId = null)
    {
        // Retrieve the template theme
        $theme = null;
        if ($themeId) {
            $theme = $this->themeManager->getTheme($themeId);
        }

        // Get the current theme
        $currentTheme = $this->themeManager->getCurrentTheme();

        // Set the parent template name
        do {
            $name = sprintf('#parent#%s', $name);

            $currentTheme = ($currentTheme === $theme) ? null : $currentTheme->getParent();
        } while ($currentTheme);

        return $name;
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
            // Is less compiler available
            if ((null !== $this->lessCompiler) && preg_match('/[.]less$/', $name)) {
                $name = sprintf('%s.css', $name);
            }

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

    /**
     * Retrieve a theme option.
     * Without name, it will return all the theme options.
     *
     * @param string $name The option name
     *
     * @return mixed
     */
    public function themeOptions($name = null)
    {
        // Retrieve the current theme options
        $options = $this->themeManager->getCurrentThemeOptions();

        // Remove null values
        foreach ($options as $key => $value) {
            if (is_null($value)) {
                unset($options[$key]);
            }
        }

        // merge options with default
        $options = array_merge(
            $this->themeManager->getCurrentTheme()->getOptions(),
            $options
        );

        // Search for a specific option
        if (! is_null($name)) {
            if (!isset($options[$name])) {
                throw new \Exception(sprintf('Unable to find the "%s" option.', $name));
            }

            return $options[$name];
        }

        // Return all options
        return $options;
    }
}
