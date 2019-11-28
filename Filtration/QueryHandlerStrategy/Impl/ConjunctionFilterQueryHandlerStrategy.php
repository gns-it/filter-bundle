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
    Model\PropertyPath,
    Model\RelationMeta,
    RelationResolverInterface};
use Slmder\SlmderFilterBundle\Filtration\Common\PropertyPathProvider\PropertyPathProviderInterface;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Configuration;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\HandlerStrategyInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ConjunctionFilterQueryHandlerStrategy
 */
class ConjunctionFilterQueryHandlerStrategy implements HandlerStrategyInterface
{
    /**
     * @var string
     */
    const PROCESSING_KEY = 'filter';

    /**
     * @var EntityInfo
     */
    protected $entityInfo;
    /**
     * @var RelationResolverInterface
     */
    protected $resolver;
    /**
     * @var ExpressionBuilder
     */
    protected $builder;
    /**
     * @var PropertyPathProviderInterface
     */
    protected $provider;

    public function __construct(
        EntityInfo $entityInfo,
        RelationResolverInterface $resolver,
        ExpressionBuilder $builder,
        PropertyPathProviderInterface $provider
    ) {
        $this->entityInfo = $entityInfo;
        $this->resolver = $resolver;
        $this->provider = $provider;
        $this->builder = $builder;
    }

    /**
     * @return string
     */
    function getProcessingKeyName(): string
    {
        return self::PROCESSING_KEY;
    }

    /**
     * @return array
     */
    function getDefaultOptions(): array
    {
        return [
            'value_delimiter' => '|',
        ];
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
        $paths = $this->provider->createPaths($query);
        /** @var PropertyPath $path */
        foreach ($paths as $path) {
            $meta = $this->resolver->resolve($queryBuilder, $path->getPath(), $rootAlias);
            $operator = ExpressionBuilder::LIKE;
            if (!$path->emptyOperator()) {
                $operator = $path->getOperator();
            }
            $expression = $this->buildExpression($operator, $meta, $path);
            if ($expression->getQueryFunc() === 'having') {
                $queryBuilder->having($expression->getExpr());
                $queryBuilder->groupBy($rootAlias.'.id');
            } else {
                $queryBuilder->andWhere($expression->getExpr());
            }
            $this->bindParameters($queryBuilder, $expression);
        }
    }

    /**
     * @param string $operator
     * @param RelationMeta $meta
     * @param PropertyPath $path
     * @return Expression
     */
    protected function buildExpression(string $operator, RelationMeta $meta, PropertyPath $path): Expression
    {
        $expression = null;
        switch (strtolower($operator)) {
            case ExpressionBuilder::LIKE:
                $expression = $this->builder->like($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::NOT_LIKE:
                $expression = $this->builder->not_like($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::EQ:
                $expression = $this->builder->eq($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::NEQ:
                $expression = $this->builder->neq($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::IN:
                $expression = $this->builder->in($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::NIN:
                $expression = $this->builder->nin($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::BETWEEN :
                $expression = $this->builder->bwn($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::NOT_BETWEEN:
                $expression = $this->builder->not_bwn($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::LT:
                $expression = $this->builder->lt($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::LTE:
                $expression = $this->builder->lte($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::LTEL:
                $expression = $this->builder->ltel($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::GT:
                $expression = $this->builder->gt($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::GTE:
                $expression = $this->builder->gte($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::GTEF:
                $expression = $this->builder->gtef($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::IS_NULL:
                $expression = $this->builder->is_null($meta->getAliasedPath());
                break;
            case ExpressionBuilder::IS_NOT_NULL:
                $expression = $this->builder->isnt_null($meta->getAliasedPath());
                break;
            case ExpressionBuilder::HAVING_COUNT_EQ:
                $expression = $this->builder->hv_count_eq($meta->getAliasedPath(), $path->getValue());
                break;
            case ExpressionBuilder::CONTAINS:
                if (!$meta->isCollectionValuedAssociation()) {
                    $class = $meta->isAssociation() ? $meta->getPrevClass() : $meta->getClassName();
                    throw new BadRequestHttpException(
                        "Operator 'contains' works only with collection associated properties, but '{$class}::{$meta->getProp()}' is not."
                    );
                }
                $expression = $this->builder->contains(
                    $meta->getClassName(),
                    $meta->getPrevAlias(),
                    $meta->getProp(),
                    $path->getValue()
                );
                break;
            case ExpressionBuilder::NOT_CONTAINS:
                if (!$meta->isCollectionValuedAssociation()) {
                    $class = $meta->isAssociation() ? $meta->getPrevClass() : $meta->getClassName();
                    throw new BadRequestHttpException(
                        "Operator 'not_contains' works only with collection associated properties, but '{$class}::{$meta->getProp()}' is not."
                    );
                }
                $expression = $this->builder->notContains(
                    $meta->getClassName(),
                    $meta->getPrevAlias(),
                    $meta->getProp(),
                    $path->getValue()
                );
                break;
            default:
                throw new BadRequestHttpException("Unknown operator '$operator'.");
        }

        return $expression;
    }

    /**
     * @param QueryBuilder $qb
     * @param Expression $expr
     */
    protected function bindParameters(QueryBuilder $qb, Expression $expr)
    {
        if ($expr->getParameter()) {
            if ($expr->getParameter() instanceof ArrayCollection) {
                foreach ($expr->getParameter() as $param) {
                    $qb->getParameters()->add($param);
                }

                return;
            }
            $qb->getParameters()->add($expr->getParameter());
        }
    }

}
