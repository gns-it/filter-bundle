<?php


namespace Slmder\SlmderFilterBundle\Filtration\Common;


use Doctrine\ORM\QueryBuilder;
use Slmder\SlmderFilterBundle\Filtration\Common\Model\RelationMeta;

interface RelationResolverInterface
{
    public function resolve(QueryBuilder $queryBuilder, string $associationPath, string $rootAlias): RelationMeta;
}