<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package slmder\SlmderFilterBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('slmder_filter');
        $treeBuilder->getRootNode()
                ->children()
                    ->booleanNode('checkers_enabled')->defaultTrue()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}