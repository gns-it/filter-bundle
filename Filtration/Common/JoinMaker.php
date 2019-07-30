<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common;

use Doctrine\ORM\QueryBuilder;
use Slmder\SlmderFilterBundle\Filtration\Common\Field\FieldAvailabilityCheckerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * Class JoinMaker
 * @package App\Filtration\Common
 */
class JoinMaker
{
    /**
     * @var EntityInfo
     */
    private $entityInfo;
    /**
     * @var <FieldAvailabilityCheckerInterface>[]
     */
    private $checkers;
    /**
     * @var bool
     */
    private $checkersEnabled;

    public function __construct(EntityInfo $entityInfo, bool $checkersEnabled)
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
     * @return string
     */
    public function make(QueryBuilder $queryBuilder, string $associationPath, string $rootAlias)
    {
        $rootClassName = $this->entityInfo->rootClassName($queryBuilder);
        $relationColumns = $this->entityInfo->getAssociationMappings($rootClassName);
        $alias = $rootAlias;
        $columns = explode('.', $associationPath);
        $scalarColumn = array_pop($columns);
        if (count($columns)) {
            foreach ($columns as $column) {
                if (!isset($relationColumns[$column])) {
                    throw  new BadRequestHttpException("$rootClassName does not have association '$column'.");
                }
                $this->availabilityCheck($rootClassName, $column);
                $nextAlias = $alias.ucfirst($column);
                if (!\in_array($nextAlias, $queryBuilder->getAllAliases(), true)) {
                    $queryBuilder->leftJoin("$alias.$column", $nextAlias);
                }
                $alias = $nextAlias;
                $rootClassName = $relationColumns[$column]['targetEntity'];
                $relationColumns = $this->entityInfo->getAssociationMappings($rootClassName);
            }
        }
        $this->availabilityCheck($rootClassName, $scalarColumn);
        if (!in_array($scalarColumn, $this->entityInfo->getFieldNames($rootClassName))) {
            throw  new BadRequestHttpException("$rootClassName does not have field '$scalarColumn'.");
        }

        return "$alias.$scalarColumn";
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
}