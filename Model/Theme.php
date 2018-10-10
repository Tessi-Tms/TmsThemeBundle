<?php
namespace Tms\Bundle\ThemeBundle\Model;

use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;

class Theme implements ThemeInterface
{
    /**
     * Theme identifier
     *
     * @var string
     */
    protected $id;

    /**
     * The theme name
     *
     * @var string
     */
    protected $name;

    /**
     * Bundles where this theme is available
     *
     * @var array<string>
     */
    protected $bundles;

    /**
     * The theme parent
     *
     * @var Theme
     */
    protected $parent;

    /**
     * Contructor.
     *
     * @param array $data The themes properties.
     */
    public function __construct(array $data = array())
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->bundles = (isset($data['bundles']) && is_array($data['bundles'])) ? $data['bundles'] : null;;
        $this->parent = (isset($data['parent']) && $data['parent'] instanceof ThemeInterface) ? $data['parent'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Theme identifier
     *
     * @param string id
     *
     * @return Theme
     */
    public function setId($value)
    {
        $this->id = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of The theme name
     *
     * @param string name
     *
     * @return Theme
     */
    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundles()
    {
        return $this->bundles ? $this->bundles : array();
    }

    /**
     * Set the value of Bundles where this theme is available
     *
     * @param array bundles
     *
     * @return Theme
     */
    public function setBundles(array $value)
    {
        $this->bundles = $value;

        return $this;
    }

    /**
     * Add a new bundle to the theme
     *
     * @param string The bundle name
     * @param string The bundle path
     *
     * @return Theme
     */
    public function addBundle($name, $path)
    {
        $this->bundles[$name] = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the value of The theme parent
     *
     * @param Theme parent
     *
     * @return Theme
     */
    public function setParent(Theme $value)
    {
        $this->parent = $value;

        return $this;
    }
}
