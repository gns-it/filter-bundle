<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common\PropertyPathProvider;

/**
 * Interface PropertyPathProviderInterface
 * @package App\Filtration\Common\PropertyPathProvider
 */
interface PropertyPathProviderInterface
{
    public function createPaths(array $query): iterable;
}