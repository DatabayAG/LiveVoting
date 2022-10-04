<?php

declare(strict_types=1);

namespace LiveVoting\Voting;

use LiveVoting\Vote\xlvoVote;

interface xlvoVotingInterface
{
    public function vote(xlvoVote $vote): bool;
}
