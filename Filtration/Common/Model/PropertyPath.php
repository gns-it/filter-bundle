<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common\Model;

/**
 * Class PropertyPath
 * @package App\Filtration\Common\Model
 */
class PropertyPath
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var string
     */
    private $value;
    /**
     * @var string
     */
    private $operator;

    public function __construct(string $path, string $value, string $operator)
    {
        $this->path = $path;
        $this->value = $value;
        $this->operator = $operator;
    }

    /**
     * @return bool
     */
    public function emptyValue():bool
    {
        return trim($this->value === '');
    }

    /**
     * @return bool
     */
    public function emptyOperator():bool
    {
        return trim($this->value === '');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getOperator(): ?string
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     */
    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

}