<?php

declare(strict_types=1);

namespace LiveVoting\Exceptions;

/**
 * Class xlvoPlayerException
 *
 * @package LiveVoting\Exceptions
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class xlvoPlayerException extends xlvoException
{
    public const OBJ_OFFLINE = 1;
    public const NO_VOTINGS = 2;
    public const CATEGORY_NOT_FOUND = 3;


    /**
     * xlvoPlayerException constructor.
     *
     * @param string $a_message
     * @param int    $a_code
     */
    public function __construct($a_message, $a_code)
    {
        parent::__construct($a_message, $a_code);
    }
}
