<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Impl;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Slmder\SlmderFilterBundle\Filtration\Common\{Model\PropertyPath};
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Configuration;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SortQueryHandlerStrategy
 * 
 */
class SortQueryHandlerStrategy extends ConjunctionFilterQueryHandlerStrategy
{
    /**
     * @var string
     */
    const PROCESSING_KEY = 'order';

    /**
     * @return string
     */
    function getProcessingKeyName(): string
    {
        return self::PROCESSING_KEY;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $query
     * @param Configuration $config
     */
    function handle(QueryBuilder $queryBuilder, array $query, Configuration $config)
    {
        $this->builder->setDelimiter($config->get('value_delimiter'));
        $rootAlias = $this->entityInfo->rootAlias($queryBuilder);
        $paths = $this->provider->createPaths($query);
        /** @var PropertyPath $path */
        foreach ($paths as $path) {
            $direction = Criteria::ASC;
            if (!$path->emptyValue()) {
                $direction = strtoupper($path->getValue());
            }
            if (!in_array($direction, [Criteria::ASC, Criteria::DESC], true)) {
                throw new BadRequestHttpException("Invalid order direction '$direction'.");
            }
            $meta = $this->resolver->resolve($queryBuilder, $path, $rootAlias);
            $expression = $this->builder->order($meta->getAliasedPath(), $direction);
            $queryBuilder->addOrderBy($expression);
        }
    }

}