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

    public function __construct(EntityInfo $entityInfo)
    {
        $this->entityInfo = $entityInfo;
        $this->checkers = [];
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
                foreach ($this->checkers as $checker) {
                    if (!$checker->available($rootClassName, $column)) {
                        throw new NotAcceptableHttpException(
                            "Operations with relation '$rootClassName::$column' are not available."
                        );
                    }
                }
                $nextAlias = $alias.ucfirst($column);
                if (!\in_array($nextAlias, $queryBuilder->getAllAliases(), true)) {
                    $queryBuilder->leftJoin("$alias.$column", $nextAlias);
                }
                $alias = $nextAlias;
                $rootClassName = $relationColumns[$column]['targetEntity'];
                $relationColumns = $this->entityInfo->getAssociationMappings($rootClassName);
            }
        }
        foreach ($this->checkers as $checker) {
            if (!$checker->available($rootClassName, $scalarColumn)) {
                throw new NotAcceptableHttpException(
                    "Operations with field '$rootClassName::$scalarColumn' are not available."
                );
            }
        }
        if (!in_array($scalarColumn, $this->entityInfo->getFieldNames($rootClassName))) {
            throw  new BadRequestHttpException("$rootClassName does not have field '$scalarColumn'.");
        }

        return "$alias.$scalarColumn";
    }
}