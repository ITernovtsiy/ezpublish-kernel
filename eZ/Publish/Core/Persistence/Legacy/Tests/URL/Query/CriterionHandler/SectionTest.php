<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\URL\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion\Section;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\Expression;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler\Section as SectionHandler;

class SectionTest extends CriterionHandlerTest
{
    /**
     * {@inheritdoc}
     */
    public function testAccept()
    {
        $handler = new SectionHandler();

        $this->assertHandlerAcceptsCriterion($handler, Section::class);
        $this->assertHandlerRejectsCriterion($handler, Criterion::class);
    }

    /**
     * {@inheritdoc}
     */
    public function testHandle()
    {
        $expected = 'ezcontentobject.section_id IN (1)';

        $converter = $this->createMock(CriteriaConverter::class);
        $handler = new SectionHandler();
        $criterion = new Section(1);

        // tables not joined yet - handle should join them
        $query = $this->createMock(SelectQuery::class);
        $query->expr = $this->getMockExpression(true, true, true);
        $query->method('getQuery')->willReturn('SELECT');
        $actual = $handler->handle($converter, $query, $criterion);
        $this->assertEquals($expected, $actual);

        // table link already joined, should not join again
        $query = $this->createMock(SelectQuery::class);
        $query->expr = $this->getMockExpression(false, true, true);
        $query->method('getQuery')->willReturn('(SELECT) INNER JOIN ezurl_object_link (ON)');
        $actual = $handler->handle($converter, $query, $criterion);
        $this->assertEquals($expected, $actual);

        // table link and attribute already joined, should not join again
        $query = $this->createMock(SelectQuery::class);
        $query->expr = $this->getMockExpression(false, false, true);
        $query->method('getQuery')->willReturn(
            '(SELECT) INNER JOIN ezurl_object_link (ON) '
            . 'INNER JOIN ezcontentobject_attribute (ON)'
        );
        $actual = $handler->handle($converter, $query, $criterion);
        $this->assertEquals($expected, $actual);

        // table link, attribute, and tree already joined, should not join again
        $query = $this->createMock(SelectQuery::class);
        $query->expr = $this->getMockExpression(false, false, false);
        $query->method('getQuery')->willReturn(
            '(SELECT) INNER JOIN ezurl_object_link (ON) ' .
            'INNER JOIN ezcontentobject_attribute (ON) ' .
            'INNER JOIN ezcontentobject (ON)'
        );
        $actual = $handler->handle($converter, $query, $criterion);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param bool $includeLinkJoin
     * @param bool $includeAttributeJoin
     * @param bool $includeContentJoin
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockExpression(bool $includeLinkJoin, bool $includeAttributeJoin, bool $includeContentJoin)
    {
        $execAt = 0;
        $expr = $this->createMock(Expression::class);
        if ($includeLinkJoin) {
            $expr
                ->expects($this->at($execAt++))
                ->method('eq')
                ->with('ezurl.id', 'ezurl_object_link.url_id')
                ->willReturn('ezurl.id=ezurl_object_link.url_id');
        }
        if ($includeAttributeJoin) {
            $expr
                ->expects($this->at($execAt++))
                ->method('eq')
                ->with('ezurl_object_link.contentobject_attribute_id', 'ezcontentobject_attribute.id')
                ->willReturn('ezurl_object_link.contentobject_attribute_id = ezcontentobject_attribute.id');
            $expr
                ->expects($this->at($execAt++))
                ->method('eq')
                ->with('ezurl_object_link.contentobject_attribute_version', 'ezcontentobject_attribute.version')
                ->willReturn('ezurl_object_link.contentobject_attribute_version = ezcontentobject_attribute.version');
            $expr
                ->expects($this->at($execAt++))
                ->method('lAnd')
                ->with(
                    'ezurl_object_link.contentobject_attribute_id = ezcontentobject_attribute.id',
                    'ezurl_object_link.contentobject_attribute_version = ezcontentobject_attribute.version'
                )->willReturn('ezurl_object_link.contentobject_attribute_id = ezcontentobject_attribute.id AND ezurl_object_link.contentobject_attribute_version = ezcontentobject_attribute.version');
        }

        if ($includeContentJoin) {
            $expr
                ->expects($this->at($execAt++))
                ->method('eq')
                ->with('ezcontentobject.id', 'ezcontentobject_attribute.contentobject_id')
                ->willReturn('ezcontentobject.id = ezcontentobject_attribute.contentobject_id');
        }

        $expr
            ->expects($this->at($execAt))
            ->method('in')
            ->with('ezcontentobject.section_id', [1])
            ->willReturn('ezcontentobject.section_id IN (1)');

        return $expr;
    }
}
