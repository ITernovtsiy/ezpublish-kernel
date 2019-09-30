<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\URL\Query\Criterion;

/**
 * Matches URLs which used by content placed in specified sections.
 */
class Section extends Matcher
{
    /**
     * IDs of related content section.
     *
     * @var int[]
     */
    public $sectionIds;

    /**
     * @param int|int[] $sectionIds
     */
    public function __construct($sectionIds)
    {
        $this->sectionIds = (array)$sectionIds;
    }
}
