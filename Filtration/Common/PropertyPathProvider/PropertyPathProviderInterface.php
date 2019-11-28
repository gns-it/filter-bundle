<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common\PropertyPathProvider;

/**
 * Interface PropertyPathProviderInterface
 * 
 */
interface PropertyPathProviderInterface
{
    public function createPaths(array $query): iterable;
}