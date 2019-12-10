<?php

namespace Tms\Bundle\ThemeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistryInterface;

class ThemeZoneChoiceType extends AbstractType
{
    /**
     * Instance of ThemeRegistryInterface.
     *
     * @var ThemeRegistryInterface
     */
    protected $themeRegistry;

    /**
     * Constructor.
     *
     * @param ThemeRegistryInterface $themeRegistry Instance of ThemeRegistryInterface
     */
    public function __construct(ThemeRegistryInterface $themeRegistry)
    {
        $this->themeRegistry = $themeRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('theme')
            ->setDefault('theme', null)
            ->setAllowedTypes('theme', array('null', 'string', 'Tms\Bundle\ThemeBundle\Model\Theme'))
            ->setNormalizer('theme', function(Options $options, $value) {
                if (is_string($value)) {
                    return $this->themeRegistry->getTheme($value);
                }

                return $value;
            })
            ->setNormalizer('choices', function(Options $options, $value) {
                $theme = $options['theme'];
                if (is_string($theme)) {
                    $theme = $this->themeRegistry->getTheme($theme);
                }

                $zones = $theme->getZones();

                return array_combine($zones, $zones);
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
