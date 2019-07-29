<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration;

use Doctrine\ORM\QueryBuilder;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\HandlerStrategyInterface;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Impl\DisjunctionQueryHandlerStrategy as Disjunction;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Impl\SimpleFilterQueryHandlerStrategy as Simple;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Impl\SortQueryHandlerStrategy as Sort;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class QueryBuilderManager
 * @package App\Filtration
 */
class QueryBuilderManager implements QueryBuilderManagerInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var $handlerStrategies array
     */
    private $handlerStrategies;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->handlerStrategies = [];
    }

    /**
     * @param HandlerStrategyInterface $strategy
     * @param $alias
     */
    public function addStrategy(HandlerStrategyInterface $strategy)
    {
        $this->handlerStrategies[] = $strategy;
    }


    /**
     * @param QueryBuilder $queryBuilder
     */
    function apply(QueryBuilder $queryBuilder): void
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        $query = $masterRequest->query;
        /** @var HandlerStrategyInterface $handler */
        foreach ($this->handlerStrategies as $handler) {
            if ($query->has($handler->getProcessingKeyName())) {
                $handler->handle($queryBuilder, $query->get($handler->getProcessingKeyName()));
            }
        }
    }
}