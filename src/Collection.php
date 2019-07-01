<?php
declare(strict_types=1);

namespace Tobias\Zend\Paginator\Adapter\Doctrine;

use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Zend\Paginator\Adapter\AdapterInterface;

/**
 * Wrapper around doctrine collection
 */
final class Collection implements AdapterInterface
{
    /**
     * @var DoctrineCollection
     */
    protected $collection;

    /**
     * @param DoctrineCollection $collection
     */
    public function __construct(DoctrineCollection $collection)
    {
        $this->collection = $collection;
    }

    public function getItems($offset, $itemCountPerPage): array
    {
        return array_values($this->collection->slice($offset, $itemCountPerPage));
    }

    public function count(): int
    {
        return count($this->collection);
    }
}
