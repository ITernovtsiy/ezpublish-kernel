<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Values\URL\Query\Criterion;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion\Section;
use PHPUnit\Framework\TestCase;

class SectionTest extends TestCase
{
    /**
     * Test a new class and default values on properties.
     *
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__construct
     */
    public function testNewClass()
    {
        $section = new Section(1);
        $this->assertEquals($section->sectionIds, [1]);

        $section = new Section([1]);
        $this->assertEquals($section->sectionIds, [1]);

        $section = new Section([1, 2, 3]);
        $this->assertEquals($section->sectionIds, [1, 2, 3]);
    }
}
