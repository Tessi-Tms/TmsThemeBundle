<?php

namespace Tms\Bundle\ThemeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistryInterface;

class ThemeType extends AbstractType
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
            ->setDefaults(array(
                'required' => false,
            ))
            ->setNormalizer('choices', function(Options $options, $value) {
                $choices = array();
                foreach ($this->themeRegistry->getThemes() as $theme) {
                    $title = $theme->getName();
                    $parent = $theme->getParent();
                    while (!is_null($parent)) {
                        $title = sprintf('%s > %s', $parent->getName(), $title);
                        $parent = $parent->getParent();
                    }
                    $choices[$title] = $theme->getId();
                }

                ksort($choices);

                return $choices;
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
