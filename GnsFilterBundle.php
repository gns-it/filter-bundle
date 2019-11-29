<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle;

use Gns\GnsFilterBundle\DependencyInjection\Compiler\GnsFilterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class FilterBundle
 * @package App\Gns
 */
class GnsFilterBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new GnsFilterPass());
    }
}