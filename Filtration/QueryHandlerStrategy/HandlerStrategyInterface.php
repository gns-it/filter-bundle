<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface HandlerStrategyInterface
 */
interface HandlerStrategyInterface
{
    /**
     * @var string
     */
    const OPERATOR_KEY = 'operator';

    /**
     * @var string
     */
    const VALUE_KEY = 'value';

    /**
     * @var array
     */
    const RESERVED_KEYS = [self::OPERATOR_KEY, self::VALUE_KEY];

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $query
     * @param Configuration $config
     * @return mixed
     */
    function handle(QueryBuilder $queryBuilder, array $query, Configuration $config);

    /**
     * @return string
     */
    function getProcessingKeyName(): string;

    /**
     * @return array
     */
    function getDefaultOptions(): array;
}