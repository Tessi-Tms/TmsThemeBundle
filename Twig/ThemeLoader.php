<?php
namespace Tms\Bundle\ThemeBundle\Twig;

use Tms\Bundle\ThemeBundle\Theme\ThemeManager;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;

class ThemeLoader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface, \Twig_SourceContextLoaderInterface
{
    /**
     * Paths of all the available bundles.
     *
     * @var array
     */
    protected $bundles;

    /**
     * Cache the path of the already found templates.
     *
     * @var array
     */
    protected $cache;

    /**
     * Instance of ThemeManager.
     *
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * The root path common to all relative paths.
     *
     * @var string
     */
    protected $rootPath;

    /**
     * Constructor.
     *
     * @param array       $bundles     Paths of all the available bundles
     * @param string      $rootPath    The root path common to all relative paths
     * @param ThemeManager $themeManager Instance of $themeManager
     */
    public function __construct(array $bundles, $rootPath, ThemeManager $themeManager)
    {
        $this->bundles = $bundles;
        $this->cache = array();
        $this->rootPath = $rootPath;
        $this->themeManager = $themeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        // Retrieve the current theme
        $theme = $this->themeManager->getCurrentTheme();

        // Ignore when the theme is disabled
        if (null === $theme) {
            throw new \Twig_Error_Loader($name);
        }

        // Search for the template file.
        $path = $this->findTemplate($theme, $name);
        if (false === $path) {
            throw new \Twig_Error_Loader($name);
        }

        return file_get_contents($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        // Retrieve the current theme
        $theme = $this->themeManager->getCurrentTheme();

        // Ignore when the theme is disabled
        if (null === $theme) {
            throw new \Twig_Error_Loader($name);
        }

        // Search for the template file.
        $path = $this->findTemplate($theme, $name);
        if (false === $path) {
            throw new \Twig_Error_Loader($name);
        }

        return new \Twig_Source(file_get_contents($path), $name, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        // Retrieve the current theme
        $theme = $this->themeManager->getCurrentTheme();

        // Ignore when the theme is disabled
        if (null === $theme) {
            return false;
        }

        // Search the template
        try {
            $path = $this->findTemplate($theme, $name);
        } catch (\Twig_Error_Loader $e) {
            return false;
        }

        return false !== $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        // Retrieve the current theme
        $theme = $this->themeManager->getCurrentTheme();

        // Ignore when the theme is disabled
        if (null === $theme) {
            throw new \Twig_Error_Loader($name);
        }

        // Search for the template file.
        $path = $this->findTemplate($theme, $name);
        if (false === $path) {
            throw new \Twig_Error_Loader($name);
        }

        return sprintf(
            '#%s:%s',
            $theme->getId(),
            $path
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        // Retrieve the current theme
        $theme = $this->themeManager->getCurrentTheme();

        // Ignore when the theme is disabled
        if (null === $theme) {
            return false;
        }

        // Search for the template file.
        $path = $this->findTemplate($theme, $name);

        return filemtime($path) < $time;
    }

    /**
     * Normalize the template name.
     *
     * @param string $name The name of the template to normalize
     *
     * @return string
     */
    protected function normalizeName($name)
    {
        return preg_replace('#/{2,}#', '/', str_replace('\\', '/', (string) $name));
    }

    /**
     * Validate the template name.
     *
     * @param  string $name The name of the template to validate
     *
     * @throws \Twig_Error_Loader
     */
    protected function validateName($name)
    {
        if (false !== strpos($name, "\0")) {
            throw new Twig_Error_Loader('A template name cannot contain NUL bytes.');
        }

        $name = ltrim($name, '/');
        $parts = explode('/', $name);
        $level = 0;
        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new Twig_Error_Loader(sprintf(
                    'Looks like you try to load a template outside configured directories (%s).',
                    $name
                ));
            }
        }
    }

    /**
     * Return the template path.
     * Will return false when the template is not found.
     *
     * @param ThemeInterface $theme Instance of ThemeInterface
     * @param string         $name  The name of the template to find
     *
     * @return string|boolean
     *
     * @throws \Twig_Error_Loader
     */
    public function findTemplate(ThemeInterface $theme, $name)
    {
        // Normalize and validate the template name
        $name = $this->normalizeName($name);
        $this->validateName($name);

        // Search in the cached templates
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        // Search in the main template directory
        $path = sprintf('%s/app/Resources/themes/%s/views/%s', $this->rootPath, $theme->getId(), $name);

        // Force parent template
        if (preg_match('/^#parent#/', $name)){
            $parentName = preg_replace('/^#parent#/', '', $name);
            $path = sprintf('%s/app/Resources/views/%s', $this->rootPath, $parentName);

            if (null !== $theme->getParent()) {
                $path = $this->findTemplate($theme->getParent(), $parentName);
            }

        // Handle '@' notation for bundle templates
        } elseif (isset($name[0]) && '@' == $name[0] && $pos = strpos($name, '/')) {
            $bundle = sprintf('%sBundle', substr($name, 1, $pos - 1));
            $template = substr($name, $pos + 1);

            // The namespace is a bundle name
            if (isset($this->bundles[$bundle])) {
                $rc = new \ReflectionClass($this->bundles[$bundle]);

                $path = sprintf(
                    '%s/Resources/themes/%s/views/%s',
                    dirname($rc->getFileName()),
                    $theme->getId(),
                    $template
                );
            }

        // Handle '::' notation for bundles templates
        } elseif (preg_match('/^[^:]+Bundle:[^:]*:[^:]+$/', $name)) {
            list($bundle, $controller, $template) = explode(':', $name);
            if ($controller) {
                $template = sprintf('%s/%s', $controller, $template);
            }

            // The bundle is found
            if (isset($this->bundles[$bundle])) {
                $rc = new \ReflectionClass($this->bundles[$bundle]);

                $path = sprintf('%s/Resources/themes/%s/views/%s',
                    dirname($rc->getFileName()),
                    $theme->getId(),
                    $template
                );
            }
        }

        // Check if the template file exists
        if (!file_exists($path)) {
            // Search in the parent theme
            if (null === $theme->getParent()){
                throw new \Twig_Error_Loader(sprintf(
                    'Unable to find template "%s" for the %s theme.',
                    $name,
                    $theme->getName()
                ));
            }

            $path = $this->findTemplate($theme->getParent(), $name);
        }

        // Cache the path for the template name
        return $this->cache[$name] = $path;
    }
}
