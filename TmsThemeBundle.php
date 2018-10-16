<?php

namespace Tms\Bundle\ThemeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tms\Bundle\ThemeBundle\DependencyInjection\Compiler\ThemeCompilerPass;

class TmsThemeBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ThemeCompilerPass());
    }
}
