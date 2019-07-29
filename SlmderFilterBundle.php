<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle;

use Slmder\SlmderFilterBundle\DependencyInjection\Compiler\SlmderFilterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class FilterBundle
 * @package App\Slmder
 */
class SlmderFilterBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SlmderFilterPass());
    }
}