<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Impl;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Slmder\SlmderFilterBundle\Filtration\Common\{EntityInfo, ExpressionBuilder, JoinMaker, Model\PropertyPath};
use Slmder\SlmderFilterBundle\Filtration\Common\PropertyPathProvider\PropertyPathProviderInterface;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\HandlerStrategyInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SortQueryHandlerStrategy
 * @package App\Filtration\QueryHandlerStrategy
 */
class SortQueryHandlerStrategy implements HandlerStrategyInterface
{
    /**
     * @var string
     */
    const PROCESSING_KEY = 'order';

    /**
     * @var EntityInfo
     */
    private $entityInfo;
    /**
     * @var JoinMaker
     */
    private $joinMaker;
    /**
     * @var ExpressionBuilder
     */
    private $builder;
    /**
     * @var PropertyPathProviderInterface
     */
    private $provider;
    /**
     * @var string
     */
    private $defaultDirection;

    public function __construct(
        JoinMaker $joinMaker,
        EntityInfo $entityInfo,
        ExpressionBuilder $builder,
        PropertyPathProviderInterface $provider,
        string $defaultDirection
    ) {
        $this->joinMaker = $joinMaker;
        $this->entityInfo = $entityInfo;
        $this->builder = $builder;
        $this->provider = $provider;
        $this->defaultDirection = $defaultDirection;
    }

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
     */
    function handle(QueryBuilder $queryBuilder, array $query)
    {
        $rootAlias = $this->entityInfo->rootAlias($queryBuilder);
        $paths = $this->provider->createPaths($query);
        /** @var PropertyPath $path */
        foreach ($paths as $path) {
            $direction = $this->defaultDirection;
            if (!$path->emptyValue()) {
                $direction = strtoupper($path->getValue());
            }
            if (!in_array($direction, [Criteria::ASC, Criteria::DESC])) {
                throw new BadRequestHttpException("Invalid order direction '$direction'.");
            }
            $aliasedPath = $this->joinMaker->make($queryBuilder, $path, $rootAlias);
            $expression = $this->builder->order($aliasedPath, $direction);
            $queryBuilder->addOrderBy($expression);
        }
    }

}