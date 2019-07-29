<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SlmderFilterPass
 * @package Slmder\SlmderFilterBundle\DependencyInjection\Compiler
 */
class SlmderFilterPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('filter.query_builder_manager')) {
            return;
        }
        $managerDef = $container->findDefinition('filter.query_builder_manager');
        $taggedStrategies = $container->findTaggedServiceIds('filter.handler_strategy');
        foreach ($taggedStrategies as $id => $tags) {
            $managerDef->addMethodCall('addStrategy', [new Reference($id)]);
        }

        $joinMakerDef = $container->findDefinition('filter.join_maker');
        $taggedCheckers = $container->findTaggedServiceIds('filter.availability_checker');
        foreach ($taggedCheckers as $id => $tags) {
            $joinMakerDef->addMethodCall('addChecker', [new Reference($id)]);
        }
    }
}