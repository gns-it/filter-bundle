<?php
/**
 * @author Sergey Hashimov
 */

namespace Gns\GnsFilterBundle\Filtration\Common;

use Doctrine\ORM\Query\Parameter;

/**
 * Class Expression
 * 
 */
class Expression
{
    /**
     * @var mixed
     */
    private $expr;
    /**
     * @var Parameter|array
     */
    private $parameter;
    /**
     * @var string
     */
    private $queryFunc;

    public function __construct(string $expr, $parameter, string $queryFunc = 'where')
    {
        $this->expr = $expr;
        $this->parameter = $parameter;
        $this->queryFunc = $queryFunc;
    }

    /**
     * @return mixed
     */
    public function getExpr()
    {
        return $this->expr;
    }

    /**
     * @param mixed $expr
     */
    public function setExpr($expr): void
    {
        $this->expr = $expr;
    }

    /**
     * @return array|Parameter
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param array|Parameter $parameter
     */
    public function setParameter($parameter): void
    {
        $this->parameter = $parameter;
    }

    /**
     * @return string
     */
    public function getQueryFunc(): ?string
    {
        return $this->queryFunc;
    }

    /**
     * @param string $queryFunc
     */
    public function setQueryFunc(string $queryFunc): void
    {
        $this->queryFunc = $queryFunc;
    }

}