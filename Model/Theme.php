<?php

namespace Tms\Bundle\ThemeBundle\Model;

use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;

class Theme implements ThemeInterface
{
    /**
     * Theme identifier.
     *
     * @var string
     */
    protected $id;

    /**
     * The theme name.
     *
     * @var string
     */
    protected $name;

    /**
     * The theme available options.
     *
     * @var array
     */
    protected $options;

    /**
     * The theme available zones.
     *
     * @var array
     */
    protected $zones;

    /**
     * The theme parent.
     *
     * @var Theme
     */
    protected $parent;

    /**
     * Contructor.
     *
     * @param array $data the themes properties
     */
    public function __construct(array $data = array())
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->options = (isset($data['options']) && is_array($data['options'])) ? $data['options'] : array();
        $this->zones = (isset($data['zones']) && is_array($data['zones'])) ? $data['zones'] : array();
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
     * Set the value of Theme identifier.
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
     * Set the value of The theme name.
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
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the value of The theme options.
     *
     * @param array $options
     *
     * @return Theme
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getZones()
    {
        return array_unique(array_merge(
            array('default'),
            $this->zones
        ));
    }

    /**
     * Set the value of The theme zones.
     *
     * @param array $zones
     *
     * @return Theme
     */
    public function setZones(array $zones)
    {
        $this->zones = $zones;

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
     * Set the value of The theme parent.
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
