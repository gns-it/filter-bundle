<?php
/**
 * @author Sergey Hashimov
 */

namespace Slmder\SlmderFilterBundle\Filtration\Common\PropertyPathProvider\Impl;

use Doctrine\Common\Collections\ArrayCollection;
use Slmder\SlmderFilterBundle\Filtration\Common\Model\PropertyPath;
use Slmder\SlmderFilterBundle\Filtration\Common\PropertyPathProvider\PropertyPathProviderInterface;

/**
 * Class PropertyPathMaker
 * 
 */
class PropertyPathProvider implements PropertyPathProviderInterface
{

    /**
     * @var string
     */
    private $defaultOperator;

    public function __construct(string $defaultOperator)
    {
        $this->defaultOperator = $defaultOperator;
    }

    /**
     * @param array $query
     * @return null|string|string[]
     */
    public function encodeQuery(array $query)
    {
        $q = preg_replace(
            '/(\]\[)|\]|\[/',
            '.',
            explode('&', urldecode(http_build_query($query, false, '&', PHP_QUERY_RFC3986)))
        );

        return preg_replace('/\.\=/', '=', $q);
    }

    /**
     * @param array $query
     * @return array
     */
    public function createEntries(array $query)
    {
        $entries = [];
        foreach ($query as $path) {
            preg_match_all('/^(.*?)(\.value|\.operator)*(\=)(.*)$/', $path, $matches);
            $entries[$matches[1][0]][trim($matches[2][0] ?: 'value', '.')] = $matches[4][0];
        }

        return $entries;
    }

    /**
     * @param array $query
     * @return iterable
     */
    public function createPaths(array $query): iterable
    {
        $paths = new ArrayCollection();
        $entries = $this->createEntries($this->encodeQuery($query));
        foreach ($entries as $path => $vars) {
            $paths->add(new PropertyPath($path, $vars['value'] ?? '', $vars['operator'] ?? $this->defaultOperator));
        }

        return $paths;
    }
}