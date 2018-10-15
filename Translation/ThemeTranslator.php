<?php
namespace Tms\Bundle\ThemeBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Tms\Bundle\ThemeBundle\Theme\ThemeManager;

class ThemeTranslator extends Translator
{
    /**
     * Registered bundles.
     *
     * @var array
     */
    protected $bundles;

    /**
     * The root path common to all relative paths.
     *
     * @var string
     */
    protected $rootPath;

    /**
     * Instance of ThemeManager.
     *
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * Set the registered bundles.
     *
     * @param array $bundles Registered bundles.
     */
    public function setBundles(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * Return the paths of all the registered bundles.
     *
     * @return array
     */
    protected function getBundlesPath()
    {
        if (!is_array($this->bundles)) {
            return array();
        }

        $bundlesPaths = array();
        foreach ($this->bundles as $key => $value) {
            $rc = new \ReflectionClass($value);
            $bundlesPaths[] = dirname($rc->getFileName());
        }

        return $bundlesPaths;
    }

    /**
     * Set the the root path common to all relative paths.
     *
     * @param string $rootPath The root path.
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * Set the ThemeManager.
     *
     * @param ThemeManager $theme Instance of ThemeManager
     */
    public function setThemeManager(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * Get the translation theme domain or his parent if the translation was not found.
     *
     * @param  string $id     Translation identifier
     * @param  string $domain Translation domain
     * @param  string $locale User locale
     * @return string
     */
    protected function getDomain($id, $domain = null, $locale = null)
    {
        // Default values
        $domain = (null === $domain) ? 'messages' : $domain;
        $locale = (null === $locale) ? $this->getLocale(): $locale;
        $theme = $this->themeManager->getCurrentTheme();

        // Use default behavior without theme
        if (null === $theme) {
            return $domain;
        }

        // Load catalogue
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }
        $catalogue = $this->catalogues[$locale];

        // Search for the translation in the theme and his parents
        do {
            $themeDomain = sprintf('%s.%s', $theme->getId(), $domain);
            $theme = $theme->getParent();
        } while (!$catalogue->has((string) $id, $themeDomain) && $theme);

        // Return the domain
        return $catalogue->has((string) $id, $themeDomain) ? $themeDomain : $domain;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $domain = $this->getDomain($id, $domain, $locale);

        return parent::trans($id, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        $domain = $this->getDomain($id, $domain, $locale);

        return parent::transChoice($id, $number, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    protected function loadCatalogue($locale)
    {
        // Translation file configuration
        $regex = '/^(.*)\.([^.]+)\.(yml|xml|xlf)$/';
        $formats = array(
            'xlf' => 'xliff',
            'xml' => 'xml',
            'yml' => 'yml',
        );

        // Add the themes translations resources
        $themes = $this->themeManager->getThemes();
        $paths = $this->getBundlesPath();
        $paths[] = sprintf('%s/app', $this->rootPath);
        foreach ($themes as $id => $theme) {
            foreach ($paths as $path) {
                $translationsPath = sprintf('%s/Resources/themes/%s/translations', $path, $theme->getId());
                if (!is_dir($translationsPath)) {
                    continue;
                }

                if ($dh = opendir($translationsPath)) {
                    while (($file = readdir($dh)) !== false) {
                        // Ignore unknown files
                        if (!preg_match($regex, $file)) {
                            continue;
                        }

                        // Parse the filename
                        $domain = sprintf('%s.%s', $theme->getId(), preg_replace($regex, "$1", $file));
                        $locale = preg_replace($regex, "$2", $file);
                        $format = $formats[preg_replace($regex, "$3", $file)];
                        
                        // Add the translations files to the translator
                        $translationsFile = sprintf('%s/%s', $translationsPath, $file);
                        $this->addResource($format, $translationsFile, $locale, $theme->getId());
                        $this->addResource($format, $translationsFile, $locale, $domain);
                    }
                    closedir($dh);
                }
            }
        }

        parent::loadCatalogue($locale);
    }
}
