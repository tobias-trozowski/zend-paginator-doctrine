<?php
declare(strict_types=1);

namespace Tobias\Zend\Paginator\Adapter\Doctrine;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable as DoctrineSelectable;
use Zend\Paginator\Adapter\AdapterInterface;

/**
 * Provides a wrapper around a Selectable object
 */
final class Selectable implements AdapterInterface
{
    /**
     * @var DoctrineSelectable
     */
    protected $selectable;

    /**
     * @var Criteria
     */
    protected $criteria;

    /**
     * Create a paginator around a Selectable object. You can also provide an optional Criteria object with
     * some predefined filters
     *
     * @param DoctrineSelectable $selectable
     * @param Criteria|null      $criteria
     */
    public function __construct(DoctrineSelectable $selectable, Criteria $criteria = null)
    {
        $this->selectable = $selectable;
        $this->criteria = $criteria ? clone $criteria : new Criteria();
    }

    public function getItems($offset, $itemCountPerPage): array
    {
        $this->criteria->setFirstResult($offset)->setMaxResults($itemCountPerPage);

        return $this->selectable->matching($this->criteria)->toArray();
    }

    public function count(): int
    {
        $criteria = clone $this->criteria;
        $criteria->setFirstResult(null);
        $criteria->setMaxResults(null);

        return count($this->selectable->matching($criteria));
    }
}
