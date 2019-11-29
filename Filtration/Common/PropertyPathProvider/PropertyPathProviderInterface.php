<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle\Filtration\Common\PropertyPathProvider;

/**
 * Interface PropertyPathProviderInterface
 * 
 */
interface PropertyPathProviderInterface
{
    public function createPaths(array $query): iterable;
}