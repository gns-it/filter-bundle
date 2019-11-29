<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle\DependencyInjection\Compiler;

use Gns\GnsFilterBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class GnsFilterPass
 * @package Gns\GnsFilterBundle\DependencyInjection\Compiler
 */
class GnsFilterPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('filter.query_builder_manager')) {
            return;
        }
        $configs = $container->getExtensionConfig('gns_filter');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('gns_filter.checkers_enabled', $config['checkers_enabled']);
        $container->setParameter('gns_filter.default_operator', $config['default_operator']);
        $container->setParameter('gns_filter.default_order_direction', $config['default_order_direction']);
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