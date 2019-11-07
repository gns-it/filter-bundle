<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ExpressionBuilder
 * @package App\Filtration
 */
class ExpressionBuilder
{
    /**
     * @var string
     */
    public const IN_DELIMITER = '|';

    /**
     * @var string
     */
    public const BETWEEN_PATTERN = '/^.*@.*$/';

    /**
     * @var string
     */
    public const OR_PATTERN = '/^[^\%].+\%.+(?<!\%)$/';

    /**
     * @var string
     */
    public const ALIAS = '';

    /**
     * @var string
     */
    public const EQ = 'eq';     //'='

    /**
     * @var string
     */
    public const NEQ = 'neq';   //'<>'

    /**
     * @var string
     */
    public const OR_LIKE = 'or_like';   //'or_like'

    /**
     * @var string
     */
    public const OR_EQ = 'or_eq';   //'or_eq'

    /**
     * @var string
     */
    public const LT = 'lt';     //'<'

    /**
     * @var string
     */
    public const LTE = 'lte';   //'<='

    /**
     * @var string
     */
    public const LTEL = 'ltel'; //'<=' with time to 23:59:59

    /**
     * @var string
     */
    public const GT = 'gt';     //'>'

    /**
     * @var string
     */
    public const GTE = 'gte';   //'>='

    /**
     * @var string
     */
    public const GTEF = 'gtef'; //'>=' with time to 00:00:00

    /**
     * @var string
     */
    public const IN = 'in';     //'IN'

    /**
     * @var string
     */
    public const NIN = 'nin';   //'NIN'

    /**
     * @var string
     */
    public const LIKE = 'like';   //'LIKE'

    /**
     * @var string
     */
    public const NOT_LIKE = 'not_like';   //'NOT LIKE'

    /**
     * @var string
     */
    public const BETWEEN = 'bwn';   //'BETWEEN'

    /**
     * @var string
     */
    public const NOT_BETWEEN = 'not_bwn';   //'NOT_BETWEEN'

    /**
     * @var string
     */
    public const HAVING_COUNT_EQ = 'hv_count_eq';   //'HAVING COUNT(f.p) = :val'

    /**
     * @var string
     */
    public const IS_NULL = 'is_null';   //'HAVING COUNT(f.p) = :val'

    /**
     * @var string
     */
    public const IS_NOT_NULL = 'isnt_null';   //'HAVING COUNT(f.p) = :val'

    /**
     * @var string
     */
    public const PAGE = 1;

    /**
     * @var string
     */
    public const LIMIT = 10;

    /**
     * @var string
     */
    public const ORDER_FIELD = 'id';

    /**
     * @var string
     */
    public const ORDER = 'desc';

    /**
     * @var array
     */
    public const ALL = [
        self::EQ,
        self::NEQ,
        self::LT,
        self::LTE,
        self::LTEL,
        self::LIKE,
        self::NOT_LIKE,
        self::GT,
        self::GTE,
        self::GTEF,
        self::IN,
        self::NIN,
        self::BETWEEN,
        self::NOT_BETWEEN,
        self::IS_NOT_NULL,
        self::IS_NULL,
    ];

    /**
     * @var array
     */
    public const SIMPLE_ALL = [
        self::EQ,
        self::NEQ,
        self::LT,
        self::LTE,
        self::LTEL,
        self::LIKE,
        self::NOT_LIKE,
        self::GT,
        self::GTE,
        self::GTEF,
        self::IN,
        self::NIN,
        self::BETWEEN,
        self::NOT_BETWEEN,
        self::HAVING_COUNT_EQ,
        self::IS_NOT_NULL,
        self::IS_NULL,
    ];

    /**
     * @param string $date
     * @return bool
     */
    public static function validDate(string $date)
    {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
            throw new BadRequestHttpException("Invalid date format given '$date'.");
        }

        return true;
    }

    /**
     * @param string $param
     * @return bool
     */
    public static function wrapLikeParam(string $param)
    {
        return "%$param%";
    }

    /**
     * @param string $string
     * @return bool
     */
    public static function paramName(string $string)
    {
        return str_replace('.', '', $string).'_'.bin2hex(random_bytes(1));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function eq(string $aliasedPath, string $value)
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath = :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function hv_count_eq(string $aliasedPath, string $value)
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("COUNT($aliasedPath) = :$paramName", new Parameter($paramName, $value), 'having');
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function is_null(string $aliasedPath, string $value)
    {
        return new Expression("$aliasedPath IS NULL", null, 'where');
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function isnt_null(string $aliasedPath, string $value)
    {
        return new Expression("$aliasedPath IS NOT NULL", null, 'where');
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function neq(string $aliasedPath, string $value)
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath <> :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function lt(string $aliasedPath, string $value)
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath < :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function lte(string $aliasedPath, string $value)
    {
        self::validDate($value);
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath <= :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function ltel(string $aliasedPath, string $value)
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
     */
    public function gt(string $aliasedPath, string $value)
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath > :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function gte(string $aliasedPath, string $value)
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath >= :$paramName", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function gtef(string $aliasedPath, string $value)
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
     */
    public function in(string $aliasedPath, string $value)
    {
        $value = explode(self::IN_DELIMITER, $value);
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath IN (:$paramName)", new Parameter($paramName, $value));
    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function nin(string $aliasedPath, string $value)
    {
        $value = explode(self::IN_DELIMITER, $value);
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath NOT IN (:$paramName)", new Parameter($paramName, $value));

    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function like(string $aliasedPath, string $value)
    {
        $paramName = self::paramName($aliasedPath);

        return new Expression("$aliasedPath LIKE :$paramName", new Parameter($paramName, self::wrapLikeParam($value)));

    }

    /**
     * @param string $aliasedPath
     * @param string $value
     * @return Expression
     */
    public function not_like(string $aliasedPath, string $value)
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
     */
    public function bwn(string $aliasedPath, string $value)
    {
        $paramName = self::paramName($aliasedPath);
        if (!preg_match(self::BETWEEN_PATTERN, $value)) {
            throw new BadRequestHttpException("Between operator requires value to be formatted as <str@str>.");
        }
        $values = explode('@', $value);
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
     */
    public function not_bwn(string $aliasedPath, string $value)
    {
        $paramName = self::paramName($aliasedPath);
        if (!preg_match(self::BETWEEN_PATTERN, $value)) {
            throw new BadRequestHttpException("Not between operator requires value to be formatted as <str@str>.");
        }
        $values = explode('@', $value);
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
     * @return Expr\OrderBy
     */
    public function order(string $aliasedPath, string $direction)
    {
        return new Expr\OrderBy($aliasedPath, $direction);
    }

}