<?php

namespace LiveVoting\Exceptions;

/**
 * Class xlvoVoterException
 *
 * @package LiveVoting\Exceptions
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class xlvoVoterException extends xlvoException
{
    public const VOTING_OFFLINE = 1;
    public const VOTING_NOT_ANONYMOUS = 2;
    public const VOTING_PIN_NOT_FOUND = 3;
    public const VOTING_UNAVAILABLE = 4;
}
