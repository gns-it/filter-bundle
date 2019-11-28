<?php
/**
 * @author Sergey Hashimov <hashimov.sergey@gmail.com>
 */

namespace Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Impl\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Slmder\SlmderFilterBundle\Filtration\Common\EntityInfo;
use Slmder\SlmderFilterBundle\Filtration\Common\Model\PropertyPath;
use Slmder\SlmderFilterBundle\Filtration\Common\PropertyPathProvider\PropertyPathProviderInterface;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\Configuration;
use Slmder\SlmderFilterBundle\Filtration\QueryHandlerStrategy\HandlerStrategyInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IncludeFieldsListExclusionStrategy implements ExclusionStrategyInterface, HandlerStrategyInterface
{
    /**
     * @var ArrayCollection
     */
    protected $fieldsMap;
    /**
     * @var EntityInfo
     */
    private $entityInfo;
    /**
     * @var PropertyPathProviderInterface
     */
    private $provider;

    public function __construct(EntityInfo $entityInfo, PropertyPathProviderInterface $provider)
    {
        $this->fieldsMap = new ArrayCollection();
        $this->entityInfo = $entityInfo;
        $this->provider = $provider;
    }

    /**
     * Whether the class should be skipped.
     * @param ClassMetadata $metadata
     * @param Context $context
     * @return bool
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $context): bool
    {
        return false;
    }

    /**
     * Whether the property should be skipped.
     * @param PropertyMetadata $property
     * @param Context $context
     * @return bool
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context): bool
    {
        $name = $property->serializedName ?: $property->name;
        if ($this->fieldsMap->containsKey($property->class) && in_array(
                $name,
                $this->fieldsMap->get($property->class),
                true
            )) {
            $property->groups = $context->getAttribute('groups');
        }

        return false;
    }

    /**
     * @return ArrayCollection
     */
    public function getFieldsMap(): ?ArrayCollection
    {
        return $this->fieldsMap;
    }

    /**
     * @param string $key
     * @param array $fields
     * @return void
     */
    public function addFields(string $key, array $fields): void
    {
        $this->fieldsMap->set($key, $fields);
    }

    /**
     * @param string $key
     * @return array|null
     */
    public function getFields(string $key): ?array
    {
        return $this->fieldsMap->get($key);
    }

    /**
     * @param ArrayCollection $fieldsMap
     */
    public function setFieldsMap(ArrayCollection $fieldsMap = null): void
    {
        $this->fieldsMap = $fieldsMap;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $query
     * @param Configuration $config
     * @return mixed
     */
    public function handle(QueryBuilder $queryBuilder, array $query, Configuration $config)
    {
        $paths = $this->provider->createPaths($query);
        $rootClass = $this->entityInfo->rootClassName($queryBuilder);
        /** @var PropertyPath $path */
        foreach ($paths as $path) {
            $class = $rootClass;
            if (strtolower($path->getPath()) !== 'self') {
                $class = $this->resolveClass($rootClass, $path->getPath());
            }
            $fields = explode($config->get('value_delimiter'), $path->getValue());
            $fieldNames = $this->entityInfo->getFieldNames($class);
            $relationColumns = $this->entityInfo->getAssociationMappings($class);
            foreach ($fields as $f) {
                if (!in_array($f, $fieldNames, true) && !array_key_exists($f, $relationColumns)) {
                    throw  new BadRequestHttpException("$class does not have field '$f'.");
                }
            }
            $this->addFields($class, $fields);
        }
    }

    /**
     * @param string $class
     * @param string|null $path
     * @return string
     */
    private function resolveClass(string $class, ?string $path): string
    {

        $relationColumns = $this->entityInfo->getAssociationMappings($class);
        $pathParts = explode('.', $path);
        $nextField = array_shift($pathParts);
        if (!count($pathParts)) {
            if (!isset($relationColumns[$nextField])) {
                throw  new BadRequestHttpException("$class does not have association '$nextField'.");
            }

            return $relationColumns[$nextField]['targetEntity'];
        }

        return $this->resolveClass($relationColumns[$nextField]['targetEntity'], implode('.', $pathParts));
    }

    /**
     * @return string
     */
    function getProcessingKeyName(): string
    {
        return 'include';
    }

    /**
     * @return array
     */
    function getDefaultOptions(): array
    {
        return [
            'value_delimiter' => '|'
        ];
    }


}