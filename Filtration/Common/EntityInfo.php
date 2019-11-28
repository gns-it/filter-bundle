<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Helps to simply retrieve entity metaData
 * Class EntityInfo
 */
class EntityInfo
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $className
     * @return array|string[]
     */
    public function getFieldNames(string $className)
    {
        return $this->em->getClassMetadata($className)->getFieldNames();
    }

    /**
     * @param string $className
     * @return array
     */
    public function getAssociationMappings(string $className)
    {
        return $this->em->getClassMetadata($className)->getAssociationMappings();
    }

    /**
     * @param string $className
     * @return array
     */
    public function getAllProperties(string $className)
    {
        $relationColumns = $this->getAssociationMappings($className);
        $fieldNames = $this->getFieldNames($className);

        return array_merge($relationColumns, array_combine($fieldNames, $fieldNames));
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return string
     */
    public function rootClassName(QueryBuilder $queryBuilder): string
    {
        return $queryBuilder->getRootEntities()[0];
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return string
     */
    public function rootAlias(QueryBuilder $queryBuilder): string
    {
        return $queryBuilder->getRootAliases()[0];
    }

    /**
     * @param string $className
     * @param $prop
     * @return bool
     */
    public function isCollectionValuedAssociation(string $className, $prop)
    {
        return $this->em->getClassMetadata($className)->isCollectionValuedAssociation($prop);
    }
}