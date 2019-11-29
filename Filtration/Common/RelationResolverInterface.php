<?php


namespace Gns\GnsFilterBundle\Filtration\Common;


use Doctrine\ORM\QueryBuilder;
use Gns\GnsFilterBundle\Filtration\Common\Model\RelationMeta;

interface RelationResolverInterface
{
    public function resolve(QueryBuilder $queryBuilder, string $associationPath, string $rootAlias): RelationMeta;
}