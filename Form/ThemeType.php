<?php

namespace Tms\Bundle\ThemeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Available themes
        $themes = array();
        foreach ($this->themeRegistry->getThemes() as $theme) {
            $title = $theme->getName();
            $parent = $theme->getParent();
            while (!is_null($parent)) {
                $title = sprintf('%s > %s', $parent->getName(), $title);
                $parent = $parent->getParent();
            }
            $themes[$title] = $theme->getId();
        }
        ksort($themes);

        // The fields
        $builder
            ->add('id', ChoiceType::class, array_merge($options['options'], array('choices' => $themes)))
            ->add('options', ThemeOptionsType::class)
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['id'])) {
                $form->add('options', ThemeOptionsType::class, array('theme' => $data['id']));
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['id'])) {
                $form->add('options', ThemeOptionsType::class, array('theme' => $data['id']));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('options')
            ->setAllowedTypes('options', array('array'))
            ->setDefault('options', array());
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FormType::class;
    }
}
