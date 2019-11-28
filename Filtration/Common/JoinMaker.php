<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common;

use Doctrine\ORM\QueryBuilder;
use Slmder\SlmderFilterBundle\Filtration\Common\Field\FieldAvailabilityCheckerInterface;
use Slmder\SlmderFilterBundle\Filtration\Common\Model\RelationMeta;
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
     * @var FieldAvailabilityCheckerInterface
     */
    private $checker;

    public function __construct(EntityInfo $entityInfo, FieldAvailabilityCheckerInterface $checker)
    {
        $this->entityInfo = $entityInfo;
        $this->checker = $checker;
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
            if (!$this->checker->available($className, $prop)) {
                throw new NotAcceptableHttpException(
                    "Operations with relation '$className::$prop' are not available."
                );
            }
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