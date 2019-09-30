<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;

class Section extends Base
{
    /**
     * {@inheritdoc}
     */
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\Section;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CriteriaConverter $converter, SelectQuery $query, Criterion $criterion)
    {
        $this->joinObjectLink($query);
        $this->joinObjectAttribute($query);

        $currentQuery = $query->getQuery();
        if (!strpos($currentQuery, 'INNER JOIN ezcontentobject ')) {
            $query->innerJoin(
                'ezcontentobject',
                $query->expr->eq('ezcontentobject.id', 'ezcontentobject_attribute.contentobject_id')
            );
        }

        return $query->expr->in('ezcontentobject.section_id', $criterion->sectionIds);
    }
}
