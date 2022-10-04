<?php

declare(strict_types=1);

namespace LiveVoting\Puk;

use LiveVoting\Pin\xlvoPin;

class Puk extends xlvoPin
{
    public function __construct(string $puk = "")
    {
        $this->pin_length = 10;

        parent::__construct($puk);
    }
}
