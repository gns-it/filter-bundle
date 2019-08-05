<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Impl;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Slmder\SlmderFilterBundle\Filtration\Common\{EntityInfo,
    Expression,
    ExpressionBuilder,
    JoinMaker,
    Model\PropertyPath};
use Slmder\SlmderFilterBundle\Filtration\Common\PropertyPathProvider\PropertyPathProviderInterface;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\HandlerStrategyInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SimpleFilterQueryHandlerStrategy
 * @package App\Filtration\QueryHandlerStrategy
 */
class SimpleFilterQueryHandlerStrategy implements HandlerStrategyInterface
{
    /**
     * @var string
     */
    const PROCESSING_KEY = 'filter';

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
    private $defaultOperator;

    public function __construct(
        EntityInfo $entityInfo,
        JoinMaker $joinMaker,
        ExpressionBuilder $builder,
        PropertyPathProviderInterface $provider,
        string $defaultOperator
    ) {
        $this->entityInfo = $entityInfo;
        $this->joinMaker = $joinMaker;
        $this->provider = $provider;
        $this->builder = $builder;
        $this->defaultOperator = $defaultOperator;
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
     * @return mixed
     */
    function handle(QueryBuilder $queryBuilder, array $query)
    {
        $rootAlias = $this->entityInfo->rootAlias($queryBuilder);
        $paths = $this->provider->createPaths($query);
        /** @var PropertyPath $path */
        foreach ($paths as $path) {
            $aliasedPath = $this->joinMaker->make($queryBuilder, $path->getPath(), $rootAlias);
            $operator = $this->defaultOperator;
            if (!$path->emptyOperator()) {
                $operator = $path->getOperator();
            }
            if (!in_array(strtolower($operator), ExpressionBuilder::SIMPLE_ALL)) {
                throw new BadRequestHttpException("Unknown operator '$operator'.");
            }
            /** @var Expression $expression */
            $expression = $this->builder->$operator($aliasedPath, $path->getValue() ?? '');
            if ($expression->getQueryFunc() === 'having') {
                $queryBuilder->having($expression->getExpr());
                $queryBuilder->groupBy($rootAlias.".id");
            } else {
                $queryBuilder->andWhere($expression->getExpr());
            }
            if ($expression->getParameter() && $expression->getParameter() instanceof ArrayCollection) {
                foreach ($expression->getParameter() as $param) {
                    $queryBuilder->getParameters()->add($param);
                }
                continue;
            }
            if ($expression->getParameter()) {
                $queryBuilder->getParameters()->add($expression->getParameter());
            }
        }
    }
}
