<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use LiveVoting\Pin\xlvoPin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

/**
 * ListGUI implementation for LiveVoting object plugin. This one
 * handles the presentation in container items (categories, courses, ...)
 * together with the corresponfing ...Access class.
 *
 * PLEASE do not create instances of larger classes here. Use the
 * ...Access class to get DB data and keep it small.
 *
 */
class ilObjLiveVotingListGUI extends ilObjectPluginListGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    private bool $payment_enabled;
    protected array $commands = [];

    public function initType(): void
    {
        $this->setType(ilLiveVotingPlugin::PLUGIN_ID);
    }

    public function initCommands(): array
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->payment_enabled = false;
        $this->info_screen_enabled = true;
        $this->timings_enabled = false;

        $this->gui_class_name = $this->getGuiClass();

        // general commands array
        $this->commands = array(
            array(
                "permission" => "read",
                "cmd" => ilObjLiveVotingGUI::CMD_SHOW_CONTENT,
                "default" => true
            ),
            array(
                "permission" => "write",
                "cmd" => ilObjLiveVotingGUI::CMD_EDIT,
                "txt" => $this->txt("xlvo_edit"),
                "default" => false
            ),
        );

        return $this->commands;
    }

    public function getGuiClass(): string
    {
        return ilObjLiveVotingGUI::class;
    }

    public function getCommandFrame(string $cmd): string
    {
        if (!$this->checkCommandAccess("write", $cmd, $this->ref_id, $this->type)) {
            return '_blank';
        }

        return parent::getCommandFrame($cmd);
    }

    /**
     * Get item properties
     *
     * @return    array        array of property arrays:
     *                        "alert" (boolean) => display as an alert property (usually in red)
     *                        "property" (string) => property name
     *                        "value" (string) => property value
     */
    public function getProperties(): array
    {
        $props = [];

        $props[] = [
            "alert" => false,
            "property" => $this->txt("voter_pin_input"),
            "value" => xlvoPin::formatPin(xlvoPin::lookupPin($this->obj_id)) // TODO: default.css not loaded
        ];

        if (!ilObjLiveVotingAccess::checkOnline($this->obj_id)) {
            $props[] = [
                "alert" => true,
                "property" => $this->txt("obj_status"),
                "value" => $this->txt("obj_offline")
            ];
        }

        return $props;
    }
}
