<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\NumberRange;

use LiveVoting\Voting\xlvoVoting;
use LiveVoting\Voting\xlvoVotingFormGUI;
use xlvoVotingGUI;

class xlvoNumberRangeVotingFormGUI extends xlvoVotingFormGUI
{
    public const USE_F_COLUMNS = false;

    public function __construct(xlvoVotingGUI $parent_gui, xlvoVoting $xlvoVoting)
    {
        parent::__construct($parent_gui, $xlvoVoting);
    }
}
