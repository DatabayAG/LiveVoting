<?php

declare(strict_types=1);

namespace LiveVoting\User;

use ilDateTime;
use ilLiveVotingPlugin;
use ilTable2GUI;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoVoteHistoryTableGUI extends ilTable2GUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        $this->setId('xlvo_results');
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setRowTemplate('tpl.history_list.html', self::plugin()->directory());
        $this->setTitle(self::plugin()->translate('results_title'));
        //
        // Columns
        $this->buildColumns();
    }

    protected function buildColumns(): void
    {
        $this->addColumn(self::plugin()->translate('common_answer'), 'answer', '80%');
        $this->addColumn(self::plugin()->translate('common_time'), 'time', '20%');
    }

    public function parseData(int $user_id, string $user_identifier, int $voting_id, int $round_id): void
    {
        $data = xlvoVoteHistoryObject::where(array(
            "user_id" => $user_id ?: null,
            "user_identifier" => $user_identifier ?: null,
            "voting_id" => $voting_id,
            "round_id" => $round_id
        ))->orderBy("timestamp", "DESC")->getArray(null, ["answer", "timestamp"]);
        $this->setData($data);
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("ANSWER", $a_set['answer']);
        $date = new ilDateTime($a_set['timestamp'], IL_CAL_UNIX);
        $this->tpl->setVariable("TIMESTAMP", $date->get(IL_CAL_DATETIME));
    }
}
