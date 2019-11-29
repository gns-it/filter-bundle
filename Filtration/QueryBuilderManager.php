<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle\Filtration;

use Doctrine\ORM\QueryBuilder;
use Gns\GnsFilterBundle\Filtration\QueryHandlerStrategy\Configuration;
use Gns\GnsFilterBundle\Filtration\QueryHandlerStrategy\HandlerStrategyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class QueryBuilderManager
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
     */
    public function addStrategy(HandlerStrategyInterface $strategy): void
    {
        $this->handlerStrategies[] = $strategy;
    }


    /**
     * @param QueryBuilder $queryBuilder
     */
    function apply(QueryBuilder $queryBuilder): void
    {
        $query = $this->requestStack->getMasterRequest()->query;
        /** @var HandlerStrategyInterface $handler */
        foreach ($this->handlerStrategies as $handler) {
            if ($query->has($handler->getProcessingKeyName())) {
                $params = $query->get($handler->getProcessingKeyName());
                $defaultOptions = $handler->getDefaultOptions();
                $config = new Configuration($handler, $params['config'] ?? [], $defaultOptions);
                unset($params['config']);
                $handler->handle($queryBuilder, $params, $config);
            }
        }
    }
}