<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface HandlerStrategyInterface
 * @package App\Filtration\QueryHandlerStrategy
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
     * @return mixed
     */
    function handle(QueryBuilder $queryBuilder, array $query);

    /**
     * @return string
     */
    function getProcessingKeyName() :string;
}