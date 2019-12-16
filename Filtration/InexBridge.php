<?php
/**
 *  * Created by PhpStorm.
 * User: sergey_h
 * Date: 16.12.2019
 * Time: 12:39
 */

namespace Gns\GnsFilterBundle\Filtration;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Gns\GnsFilterBundle\Filtration\QueryHandlerStrategy\Configuration;
use Gns\GnsFilterBundle\Filtration\QueryHandlerStrategy\HandlerStrategyInterface;
use Gns\GnsFilterBundle\Filtration\QueryHandlerStrategy\Impl\Serializer\ExcludeFieldsListExclusionStrategy;
use Gns\GnsFilterBundle\Filtration\QueryHandlerStrategy\Impl\Serializer\IncludeFieldsListExclusionStrategy;
use Symfony\Component\HttpFoundation\ParameterBag;

class InexBridge
{
    /**
     * @var HandlerStrategyInterface[]
     */
    private $strategies;
    /**
     * @var IncludeFieldsListExclusionStrategy
     */
    private $inclusionStrategy;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        EntityManagerInterface $em,
        IncludeFieldsListExclusionStrategy $inclusionStrategy,
        ExcludeFieldsListExclusionStrategy $exclusionStrategy
    ) {
        $this->strategies[] = $inclusionStrategy;
        $this->strategies[] = $exclusionStrategy;
        $this->inclusionStrategy = $inclusionStrategy;
        $this->em = $em;
    }

    /**
     * @param ParameterBag $query
     * @param object $entity
     * @return object
     */
    public function handle(ParameterBag $query, object $entity): object
    {
        $class = get_class($entity);
        /** @var EntityRepository $repo */
        $repo = $this->em->getRepository($class);
        /** @var HandlerStrategyInterface $handler */
        foreach ($this->strategies as $handler) {
            if ($query->has($handler->getProcessingKeyName())) {
                $params = $query->get($handler->getProcessingKeyName());
                $defaultOptions = $handler->getDefaultOptions();
                $config = new Configuration($handler, $params['config'] ?? [], $defaultOptions);
                unset($params['config']);
                $handler->handle($repo->createQueryBuilder(substr($class, 0, 2)), $params, $config);
            }
        }

        return $entity;
    }

}