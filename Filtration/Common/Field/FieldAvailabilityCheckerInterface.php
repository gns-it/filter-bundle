<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle\Filtration\Common\Field;

/**
 * Interface FieldAvailabilityCheckerInterface
 * 
 */
interface FieldAvailabilityCheckerInterface
{
    /**
     * @param string $className
     * @param string $fieldName
     * @return bool
     */
    public function available(string $className, string $fieldName): bool;
}