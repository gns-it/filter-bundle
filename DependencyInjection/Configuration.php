<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Gns\GnsFilterBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('gns_filter');
        $treeBuilder->getRootNode()
                ->children()
                    ->booleanNode('checkers_enabled')->defaultTrue()->end()
                    ->scalarNode('default_operator')->defaultValue('like')->end()
                    ->scalarNode('default_order_direction')->defaultValue('ASC')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}