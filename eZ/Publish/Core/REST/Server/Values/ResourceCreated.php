<?php
/**
 * File containing the ResourceCreated class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

class ResourceCreated
{
    public function __construct( $redirectUri )
    {
        $this->redirectUri = $redirectUri;
    }
}
