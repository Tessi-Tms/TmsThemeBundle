<?php

namespace Tms\Bundle\ThemeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistryInterface;

class ThemeOptionsType extends AbstractType
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Retrieve the Theme
        $theme = $options['theme'];
        if (is_null($theme)) {
            return;
        }

        // Add the options
        foreach ($theme->getOptions() as $key => $value) {
            $builder->add($key, TextType::class, array(
                'required' => false,
                'attr' => array(
                    'placeholder' => $value,
                ),
            ));
        }
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
                    try {
                        return $this->themeRegistry->getTheme($value);
                    } catch (\Exception $e) {
                        return null;
                    }
                }

                return $value;
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FormType::class;
    }
}
