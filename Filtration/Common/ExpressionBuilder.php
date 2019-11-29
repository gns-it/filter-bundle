<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle\Filtration\Common;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\Query\Parameter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ExpressionBuilder
 * 
 */
class ExpressionBuilder
{
    public const DELIMITER = '|';

    public const BETWEEN_PATTERN = '/^.*\|.*$/';

    public const ALIAS = '';

    public const EQ = 'eq';

    public const NEQ = 'neq';

    public const LT = 'lt';

    public const LTE = 'lte';

    public const LTEL = 'ltel';

    public const GT = 'gt';

    public const GTE = 'gte';

    public const GTEF = 'gtef';

    public const IN = 'in';

    public const NIN = 'nin';

    public const LIKE = 'like';

    public const NOT_LIKE = 'not_like';

    public const BETWEEN = 'bwn';

    public const NOT_BETWEEN = 'not_bwn';

    public const HAVING_COUNT_EQ = 'hv_count_eq';

    public const IS_NULL = 'is_null';

    public const IS_NOT_NULL = 'isnt_null';

    public const CONTAINS = 'contains';

    public const NOT_CONTAINS = 'not_contains';

    public const PAGE = 1;

    public const LIMIT = 10;

    public const ORDER_FIELD = 'id';

    public const ORDER = 'desc';

    /**
     * @var string
     */
    private $delimiter;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter ?: self::DELIMITER;
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter(string $delimiter = null): void
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param string $date
     * @return bool
     */
    public static function validDate(string $date): bool
    {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
            throw new BadRequestHttpException("Invalid date format given '$date'.");
        }

        return true;
    }

    /**
     * @param string $param
     * @return string|boolean
     */
    public static function wrapLikeParam(string $param): string
    {
        return "%$param%";
    }

    /**
     * @param string $string
     * @return string|boolean
     * @throws \Exception
     */
    public static function paramName(string $string): string
    {
        return str_replace('.', '', $string) . '_' . bin2hex(random_bytes(1));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function eq(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath = :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function hv_count_eq(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("COUNT($aliasedPath) = :$paramName", new Parameter($paramName, $value), 'having');
    }

    /**
     * @param string $aliasedPath
     * @return Expression
     */
    public function is_null(string $aliasedPath): Expression
    {
        return new Expression("$aliasedPath IS NULL", null, 'where');
    }

    /**
     * @param string $aliasedPath
     * @return Expression
     */
    public function isnt_null(string $aliasedPath): Expression
    {
        return new Expression("$aliasedPath IS NOT NULL", null, 'where');
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function neq(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath <> :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function lt(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath < :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function lte(string $aliasedPath, string $value): Expression
    {
        self::validDate($value);
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath <= :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function ltel(string $aliasedPath, string $value): Expression
    {
        self::validDate($value);
        $value = "$value 23:59:59";
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath <= :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function gt(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath > :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function gte(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath >= :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function gtef(string $aliasedPath, string $value): Expression
    {
        self::validDate($value);
        $value = "$value 00:00:00";
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath >= :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function in(string $aliasedPath, string $value): Expression
    {
        $values = explode($this->getDelimiter(), $value);
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath IN (:$paramName)", new Parameter($paramName, $values));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function nin(string $aliasedPath, string $value): Expression
    {
        $values = explode($this->getDelimiter(), $value);
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath NOT IN (:$paramName)", new Parameter($paramName, $values));

    }

    /**
     * @param string $className
     * @param string $prevAlias
     * @param string $prop
     * @param string $id
     * @param string $idName
     * @return Expression
     * @throws \Exception
     */
    public function contains(
        string $className,
        string $prevAlias,
        string $prop,
        string $id,
        string $idName = 'uuid'
    ): Expression
    {
        $entity = $this->em->getRepository($className)->findOneBy([$idName => $id]);
        if (!$entity) {
            throw  new NotFoundHttpException(
                sprintf('Entity of class %s not found by %s::%s', $className, $idName, $id)
            );
        }
        $paramName = self::paramName("$prevAlias.$prop");

        return new Expression(":$paramName MEMBER OF $prevAlias.$prop", new Parameter($paramName, $entity));
    }

    /**
     * @param string $className
     * @param string $prevAlias
     * @param string $prop
     * @param string $id
     * @param string $idName
     * @return Expression
     * @throws \Exception
     */
    public function notContains(
        string $className,
        string $prevAlias,
        string $prop,
        string $id,
        string $idName = 'uuid'
    ): Expression
    {
        $entity = $this->em->getRepository($className)->findOneBy([$idName => $id]);
        if (!$entity) {
            throw  new NotFoundHttpException(
                sprintf('Entity of class %s not found by %s::%s', $className, $idName, $id)
            );
        }
        $paramName = self::paramName("$prevAlias.$prop");

        return new Expression(":$paramName NOT MEMBER OF $prevAlias.$prop", new Parameter($paramName, $entity));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function like(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath LIKE :$paramName", new Parameter($paramName, self::wrapLikeParam($value)));

    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function not_like(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression(
            "$aliasedPath NOT LIKE :$paramName",
            new Parameter($paramName, self::wrapLikeParam($value))
        );

    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function bwn(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);
        if (!preg_match(self::BETWEEN_PATTERN, $value)) {
            throw new BadRequestHttpException(
                "Between operator requires value to be formatted as <string>{$this->getDelimiter()}<string>."
            );
        }
        $values = explode($this->getDelimiter(), $value);
        $params = new ArrayCollection(
            [
                new Parameter("from$paramName", $values[0]),
                new Parameter("to$paramName", $values[1]),
            ]
        );

        return new Expression(
            "$aliasedPath BETWEEN :from$paramName AND :to$paramName",
            $params
        );

    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     * @throws \Exception
     */
    public function not_bwn(string $aliasedPath, string $value): Expression
    {
        $paramName = self::paramName($aliasedPath);
        if (!preg_match(self::BETWEEN_PATTERN, $value)) {
            throw new BadRequestHttpException(
                "Not between operator requires value to be formatted as <string>{$this->getDelimiter()}<string>."
            );

        }
        $values = explode($this->getDelimiter(), $value);
        $params = new ArrayCollection(
            [
                new Parameter("from$paramName", $values[0]),
                new Parameter("to$paramName", $values[1]),
            ]
        );

        return new Expression(
            "$aliasedPath NOT BETWEEN :from$paramName AND :to$paramName",
            $params
        );
    }

    /**
     * @param string $aliasedPath
     * @param string $direction
     * @return OrderBy
     */
    public function order(string $aliasedPath, string $direction): OrderBy
    {
        return new OrderBy($aliasedPath, $direction);
    }


}