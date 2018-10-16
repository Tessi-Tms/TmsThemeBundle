<?php
namespace Tms\Bundle\ThemeBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\ThemeBundle\Theme\ThemeRegistryInterface;
use Tms\Bundle\ThemeBundle\Theme\ThemeInterface;

class ThemeParamConverter implements ParamConverterInterface
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
    function apply(Request $request, ParamConverter $configuration)
    {
        $param = $configuration->getName();
        if (!$request->attributes->has($param)) {
            return false;
        }

        $value = $request->attributes->get($param);
        if (!$value && $configuration->isOptional()) {
            $request->attributes->set($param, null);

            return true;
        }

        $request->attributes->set($param, $this->themeRegistry->getTheme($value));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        $class = new \ReflectionClass($configuration->getClass());

        return $class->implementsInterface(ThemeInterface::class);
    }
}
