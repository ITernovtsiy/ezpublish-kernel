<?php
/**
 * File containing the Trash visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Trash value object visitor
 */
class Trash extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param mixed $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'Trash' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Trash' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'trashItems' )
        );
        $generator->endAttribute( 'href' );

        $generator->startList( 'TrashItem' );

        foreach ( $data->trashItems as $trashItem )
        {
            $generator->startObjectElement( 'TrashItem' );
            $generator->startAttribute(
                'href',
                $this->urlHandler->generate( 'trash', array( 'trash' => $trashItem->id ) )
            );
            $generator->endAttribute( 'href' );
            $generator->endObjectElement( 'TrashItem' );
        }

        $generator->endList( 'TrashItem' );

        $generator->endObjectElement( 'Trash' );
    }
}

