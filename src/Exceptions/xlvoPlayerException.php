<?php

declare(strict_types=1);

namespace LiveVoting\Exceptions;

class xlvoPlayerException extends xlvoException
{
    public const OBJ_OFFLINE = 1;
    public const NO_VOTINGS = 2;
    public const CATEGORY_NOT_FOUND = 3;

    public function __construct(string $a_message, int $a_code)
    {
        parent::__construct($a_message, $a_code);
    }
}
