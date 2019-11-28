<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Impl;

use Doctrine\ORM\QueryBuilder;
use Slmder\SlmderFilterBundle\Filtration\Common\{Expression, ExpressionBuilder, Model\PropertyPath};
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Configuration;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class CrossFieldFilterQueryHandlerStrategy
 */
class DisjunctionQueryHandlerStrategy extends ConjunctionFilterQueryHandlerStrategy
{
    /**
     * @var string
     */
    const PROCESSING_KEY = 'cfFilter';

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
     * @return mixed
     */
    function handle(QueryBuilder $queryBuilder, array $query, Configuration $config)
    {
        $this->builder->setDelimiter($config->get('value_delimiter'));
        $rootAlias = $this->entityInfo->rootAlias($queryBuilder);
        foreach ($query as $i => $item) {
            if (!isset($item)) {
                throw new BadRequestHttpException('Fields not defined.');
            }
            $paths = $this->provider->createPaths($item);
            $expressions = [];
            /** @var PropertyPath $path */
            foreach ($paths as $path) {
                $meta = $this->resolver->resolve($queryBuilder, $path->getPath(), $rootAlias);
                $operator = ExpressionBuilder::LIKE;
                if (!$path->emptyOperator()) {
                    $operator = $path->getOperator();
                }
                $expressions[] = $this->buildExpression($operator, $meta, $path);
            }
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    ...array_map(
                        static function (Expression $e) {
                            return $e->getExpr();
                        },
                        $expressions
                    )
                )
            );
            foreach ($expressions as $expression) {
                $this->bindParameters($queryBuilder, $expression);
            }
        }
    }
}