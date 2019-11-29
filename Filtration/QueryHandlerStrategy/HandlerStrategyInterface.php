<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle\Filtration\QueryHandlerStrategy;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface HandlerStrategyInterface
 */
interface HandlerStrategyInterface
{
    /**
     * @var string
     */
    public const OPERATOR_KEY = 'operator';

    /**
     * @var string
     */
    public const VALUE_KEY = 'value';

    /**
     * @var array
     */
    public const RESERVED_KEYS = [self::OPERATOR_KEY, self::VALUE_KEY];

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