<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\DependencyInjection\Compiler;

use Slmder\SlmderFilterBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
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
        $configs = $container->getExtensionConfig('slmder_filter');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('slmder_filter.checkers_enabled', $config['checkers_enabled']);
        $container->setParameter('slmder_filter.default_operator', $config['default_operator']);
        $container->setParameter('slmder_filter.default_order_direction', $config['default_order_direction']);
        $container->setParameter('slmder_filter.trigger_on_pagination_items', $config['trigger_on_pagination_items']);
        $managerDef = $container->findDefinition('filter.query_builder_manager');
        $taggedStrategies = $container->findTaggedServiceIds('filter.handler_strategy');
        foreach ($taggedStrategies as $id => $tags) {
            $managerDef->addMethodCall('addStrategy', [new Reference($id)]);
        }
        if ($config['checkers_enabled']) {
            $joinMakerDef = $container->findDefinition('filter.join_maker');
            $taggedCheckers = $container->findTaggedServiceIds('filter.availability_checker');
            foreach ($taggedCheckers as $id => $tags) {
                $joinMakerDef->addMethodCall('addChecker', [new Reference($id)]);
            }
        }
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param array $configs
     * @return mixed
     */
    private function processConfiguration(ConfigurationInterface $configuration, array $configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }

}