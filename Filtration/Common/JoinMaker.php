<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle\Filtration\Common;

use Doctrine\ORM\QueryBuilder;
use Gns\GnsFilterBundle\Filtration\Common\Field\FieldAvailabilityCheckerInterface;
use Gns\GnsFilterBundle\Filtration\Common\Model\RelationMeta;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * Class JoinMaker
 */
class JoinMaker implements RelationResolverInterface
{
    /**
     * @var EntityInfo
     */
    private $entityInfo;
    /**
     * @var FieldAvailabilityCheckerInterface[]
     */
    private $checkers;
    /**
     * @var bool
     */
    private $checkersEnabled;

    public function __construct(EntityInfo $entityInfo,  bool $checkersEnabled)
    {
        $this->entityInfo = $entityInfo;
        $this->checkers = [];
        $this->checkersEnabled = $checkersEnabled;
    }

    /**
     * @param FieldAvailabilityCheckerInterface $checkers
     */
    public function addChecker(FieldAvailabilityCheckerInterface $checkers): void
    {
        $this->checkers[] = $checkers;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $associationPath
     * @param string $rootAlias
     * @return RelationMeta
     */
    public function resolve(QueryBuilder $queryBuilder, string $associationPath, string $rootAlias): RelationMeta
    {
        $className = $this->entityInfo->rootClassName($queryBuilder);
        $props = $this->entityInfo->getAllProperties($className);
        $alias = $rootAlias;
        $prevClass = null;
        $prevAlias = null;
        $prop = null;
        $pathParts = explode('.', $associationPath);
        $scalarColumn = 'uuid';
        $isCollectionValuedAssociation = false;
        $isAssociation = false;
        foreach ($pathParts as $prop) {
            if (!isset($props[$prop])) {
                throw  new BadRequestHttpException("$className does not have property '$prop'.");
            }
            $this->availabilityCheck($className, $prop);
            $isCollectionValuedAssociation = $this->entityInfo->isCollectionValuedAssociation($className, $prop);
            $isAssociation = $this->isAssociation($props, $prop);
            if (!$isAssociation) {
                $scalarColumn = $prop;
                break;
            }
            $nextAlias = sprintf('%s_%s', $alias, $prop);
            if (!$this->isAliasPresent($queryBuilder, $nextAlias)) {
                $queryBuilder->leftJoin(sprintf('%s.%s', $alias, $prop), $nextAlias);
            }
            $prevClass = $className;
            $prevAlias = $alias;
            $alias = $nextAlias;
            $className = $props[$prop]['targetEntity'];
            $props = $this->entityInfo->getAllProperties($className);
        }

        return new RelationMeta(
            $className,
            $alias,
            $prop,
            $scalarColumn,
            $prevAlias,
            $prevClass,
            $isAssociation,
            $isCollectionValuedAssociation
        );
    }

    /**
     * @param string $className
     * @param string $field
     */
    public function availabilityCheck(string $className, string $field)
    {
        if ($this->checkersEnabled) {
            foreach ($this->checkers as $checker) {
                if (!$checker->available($className, $field)) {
                    throw new NotAcceptableHttpException(
                        "Operations with field '$className::$field' are not available."
                    );
                }
            }
        }
    }

    /**
     * @param array $props
     * @param string $key
     * @return bool
     */
    public function isAssociation(array $props, string $key)
    {
        return is_array($props[$key]);
    }

    /**
     * @param QueryBuilder $qb
     * @param string $alias
     * @return bool
     */
    public function isAliasPresent(QueryBuilder $qb, string $alias)
    {
        return in_array($alias, $qb->getAllAliases(), true);
    }
}