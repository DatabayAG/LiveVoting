<?php

declare(strict_types=1);

namespace LiveVoting\Js;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoJsResponse
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected $data;

    protected function __construct($data)
    {
        $this->data = $data;
    }

    public static function getInstance($data): self
    {
        return new self($data);
    }

    public function send(): void
    {
        header('Content-type: application/json');
        echo json_encode($this->data, JSON_THROW_ON_ERROR);
        exit;
    }
}
