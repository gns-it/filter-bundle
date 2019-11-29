<?php
/**
 * @author Sergey Hashimov <hashimov.sergey@gmail.com>
 */

namespace Gns\GnsFilterBundle\Filtration\QueryHandlerStrategy\Impl\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\Metadata\PropertyMetadata;

class ExcludeFieldsListExclusionStrategy extends IncludeFieldsListExclusionStrategy
{
    /**
     * Whether the property should be skipped.
     * @param PropertyMetadata $property
     * @param Context $context
     * @return bool
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context): bool
    {
        $name = $property->serializedName ?: $property->name;
        if ($this->fieldsMap->containsKey($property->class) && in_array(
                $name,
                $this->fieldsMap->get($property->class),
                true
            )) {
            $property->groups = [];
        }

        return false;
    }

    /**
     * @return string
     */
    function getProcessingKeyName(): string
    {
        return 'exclude';
    }


}