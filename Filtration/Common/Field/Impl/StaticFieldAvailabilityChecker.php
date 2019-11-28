<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common\Field\Impl;

use Slmder\SlmderFilterBundle\Filtration\Common\Field\FieldAvailabilityCheckerInterface;

/**
 * Class StaticFieldAvailabilityChecker
 * 
 */
class StaticFieldAvailabilityChecker implements FieldAvailabilityCheckerInterface
{
    /**
     * @param string $className
     * @param string $fieldName
     * @return bool
     */
    public function available(string $className, string $fieldName): bool
    {
        return true;
    }
}