<?php
declare(strict_types=1);

namespace TobiasTest\Zend\Paginator\Adapter\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use PHPUnit\Framework\TestCase;
use Tobias\Zend\Paginator\Adapter\Doctrine\Selectable as SelectableAdapter;

final class SelectableTest extends TestCase
{
    /**
     * @covers \Tobias\Zend\Paginator\Adapter\Doctrine\Selectable::getItems
     */
    public function testGetItemsAtOffsetZeroWithEmptyCriteria(): void
    {
        $selectable = $this->createMock(Selectable::class);
        $adapter = new SelectableAdapter($selectable);
        $me = $this;
        $selectable
            ->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $criteria) use ($me) {
                        $me->assertEquals(0, $criteria->getFirstResult());
                        $me->assertEquals(10, $criteria->getMaxResults());
                        return true;
                    }
                )
            )
            ->willReturn(new ArrayCollection(range(1, 10)));
        $expected = range(1, 10);
        $actual = $adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \Tobias\Zend\Paginator\Adapter\Doctrine\Selectable::getItems
     */
    public function testGetItemsAtOffsetZeroWithNonEmptyCriteria(): void
    {
        $selectable = $this->createMock(Selectable::class);
        $criteria = new Criteria(Criteria::expr()->eq('foo', 'bar'));
        $adapter = new SelectableAdapter($selectable, $criteria);
        $me = $this;
        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $innerCriteria) use ($criteria, $me) {
                        // Criteria are cloned internally
                        $me->assertNotEquals($innerCriteria, $criteria);
                        $me->assertEquals(0, $innerCriteria->getFirstResult());
                        $me->assertEquals(10, $innerCriteria->getMaxResults());
                        $me->assertEquals($innerCriteria->getWhereExpression(), $criteria->getWhereExpression());
                        return true;
                    }
                )
            )
            ->willReturn(new ArrayCollection(range(1, 10)));
        $expected = range(1, 10);
        $actual = $adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \Tobias\Zend\Paginator\Adapter\Doctrine\Selectable::getItems
     */
    public function testGetItemsAtOffsetTenWithEmptyCriteria(): void
    {
        $selectable = $this->createMock(Selectable::class);
        $adapter = new SelectableAdapter($selectable);
        $me = $this;
        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $criteria) use ($me) {
                        $me->assertEquals(10, $criteria->getFirstResult());
                        $me->assertEquals(10, $criteria->getMaxResults());
                        return true;
                    }
                )
            )
            ->willReturn(new ArrayCollection(range(11, 20)));
        $expected = range(11, 20);
        $actual = $adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \Tobias\Zend\Paginator\Adapter\Doctrine\Selectable::getItems
     */
    public function testGetItemsAtOffsetTenWithNonEmptyCriteria(): void
    {
        $selectable = $this->createMock(Selectable::class);
        $criteria = new Criteria(Criteria::expr()->eq('foo', 'bar'));
        $adapter = new SelectableAdapter($selectable, $criteria);
        $me = $this;
        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $innerCriteria) use ($criteria, $me) {
                        // Criteria are cloned internally
                        $me->assertNotEquals($innerCriteria, $criteria);
                        $me->assertEquals(10, $innerCriteria->getFirstResult());
                        $me->assertEquals(10, $innerCriteria->getMaxResults());
                        $me->assertEquals($innerCriteria->getWhereExpression(), $criteria->getWhereExpression());
                        return true;
                    }
                )
            )
            ->willReturn(new ArrayCollection(range(11, 20)));
        $expected = range(11, 20);
        $actual = $adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers \Tobias\Zend\Paginator\Adapter\Doctrine\Selectable::count
     */
    public function testReturnsCorrectCount(): void
    {
        $selectable = $this->createMock(Selectable::class);
        $expression = Criteria::expr()->eq('foo', 'bar');
        $criteria = new Criteria($expression, ['baz' => Criteria::DESC], 10, 20);
        $adapter = new SelectableAdapter($selectable, $criteria);
        $selectable->expects($this->once())
            ->method('matching')
            ->with(
                $this->callback(
                    static function (Criteria $criteria) use ($expression) {
                        return $criteria->getWhereExpression() === $expression
                            && (['baz' => Criteria::DESC] === $criteria->getOrderings())
                            && null === $criteria->getFirstResult()
                            && null === $criteria->getMaxResults();
                    }
                )
            )
            ->willReturn(new ArrayCollection(range(1, 101)));
        $this->assertEquals(101, $adapter->count());
        $this->assertSame(10, $criteria->getFirstResult(), 'Original criteria was not modified');
        $this->assertSame(20, $criteria->getMaxResults(), 'Original criteria was not modified');
    }
}
