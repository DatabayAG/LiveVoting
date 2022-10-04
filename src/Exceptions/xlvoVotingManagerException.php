<?php

declare(strict_types=1);

namespace LiveVoting\Exceptions;

class xlvoVotingManagerException extends xlvoException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
