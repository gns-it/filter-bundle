<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common\Field;

/**
 * Interface FieldAvailabilityCheckerInterface
 * @package App\Filtration\Common\Field
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