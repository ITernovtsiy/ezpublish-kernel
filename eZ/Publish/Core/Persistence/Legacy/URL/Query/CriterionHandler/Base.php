<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;

use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;

abstract class Base implements CriterionHandler
{
    /**
     * Inner join `ezurl_object_link` table if not joined yet.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     */
    protected function joinObjectLink(SelectQuery $query): void
    {
        if (!strpos($query->getQuery(), 'INNER JOIN ezurl_object_link ')) {
            $query->innerJoin(
                'ezurl_object_link',
                $query->expr->eq('ezurl.id', 'ezurl_object_link.url_id')
            );
        }
    }

    /**
     * Inner join `ezcontentobject_attribute` table if not joined yet.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     */
    protected function joinObjectAttribute(SelectQuery $query): void
    {
        if (!strpos($query->getQuery(), 'INNER JOIN ezcontentobject_attribute ')) {
            $query->innerJoin('ezcontentobject_attribute', $query->expr->lAnd(
                $query->expr->eq(
                    'ezurl_object_link.contentobject_attribute_id',
                    'ezcontentobject_attribute.id'
                ),
                $query->expr->eq(
                    'ezurl_object_link.contentobject_attribute_version',
                    'ezcontentobject_attribute.version'
                )
            ));
        }
    }
}
