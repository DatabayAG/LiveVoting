<?php

declare(strict_types=1);

namespace LiveVoting\Exceptions;

/**
 * Class xlvoSubFormGUIHandleFieldException
 *
 * @package LiveVoting\Exceptions
 */
class xlvoSubFormGUIHandleFieldException extends xlvoException
{
    /**
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
