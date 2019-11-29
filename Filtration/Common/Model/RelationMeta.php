<?php

namespace Gns\GnsFilterBundle\Filtration\Common\Model;

class RelationMeta
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $alias;
    /**
     * @var string
     */
    private $scalarColumn;
    /**
     * @var string
     */
    private $prop;
    /**
     * @var string|null
     */
    private $prevAlias;
    /**
     * @var bool
     */
    private $isCollectionValuedAssociation;
    /**
     * @var bool
     */
    private $isAssociation;
    /**
     * @var string
     */
    private $prevClass;

    /**
     * RelationMeta constructor.
     * @param string $className
     * @param string $alias
     * @param string $prop
     * @param string|null $scalarColumn
     * @param string|null $prevAlias
     * @param string|null $prevClass
     * @param bool $isAssociation
     * @param bool $isCollectionValuedAssociation
     */
    public function __construct(
        string $className,
        string $alias,
        string $prop,
        string $scalarColumn = null,
        string $prevAlias = null,
        string $prevClass = null,
        bool $isAssociation = false,
        bool $isCollectionValuedAssociation = false
    ) {
        $this->className = $className;
        $this->alias = $alias;
        $this->scalarColumn = $scalarColumn;
        $this->prop = $prop;
        $this->prevAlias = $prevAlias;
        $this->isCollectionValuedAssociation = $isCollectionValuedAssociation;
        $this->isAssociation = $isAssociation;
        $this->prevClass = $prevClass;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getAliasedPath(): string
    {
        if ($this->scalarColumn) {
            return sprintf('%s.%s', $this->alias, $this->scalarColumn);
        }

        return $this->alias;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getScalarColumn(): ?string
    {
        return $this->scalarColumn;
    }

    /**
     * @return string
     */
    public function getProp(): ?string
    {
        return $this->prop;
    }

    /**
     * @return string|null
     */
    public function getPrevAlias(): ?string
    {
        return $this->prevAlias;
    }

    /**
     * @return bool
     */
    public function isCollectionValuedAssociation(): ?bool
    {
        return $this->isCollectionValuedAssociation;
    }

    /**
     * @return bool
     */
    public function isAssociation(): ?bool
    {
        return $this->isAssociation;
    }

    /**
     * @return string
     */
    public function getPrevClass(): ?string
    {
        return $this->prevClass;
    }

}
