<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use LiveVoting\Option\xlvoOption;
use LiveVoting\Pin\xlvoPin;
use LiveVoting\Player\xlvoPlayer;
use LiveVoting\Puk\Puk;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Vote\xlvoVote;
use LiveVoting\Voting\xlvoVoting;
use LiveVoting\Voting\xlvoVotingConfig;
use srag\DIC\LiveVoting\DICTrait;

/**
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Daniel Aemmer <daniel.aemmer@phbern.ch>
 */
class ilObjLiveVoting extends ilObjectPlugin
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function __construct(int $a_ref_id = 0, bool $by_oid = false)
    {
        parent::__construct($a_ref_id);
        /*if ($a_ref_id != 0) {
            $this->id = $a_ref_id;
            $this->doRead();
        }*/
    }

    protected function doCreate($clone_mode = false): void
    {
        $xlvoPin = new xlvoPin();
        $xlvoPuk = new Puk();
        $config = new xlvoVotingConfig();
        $config->setObjId($this->getId());
        $config->setPin($xlvoPin->getPin());
        $config->setPuk($xlvoPuk->getPin());
        $config->store();
    }

    final protected function initType(): void
    {
        $this->setType(ilLiveVotingPlugin::PLUGIN_ID);
    }

    protected function doDelete(): void
    {
        /**
         * @var xlvoPlayer[] $players
         */
        $players = xlvoPlayer::where(array('obj_id' => $this->getId()))->get();
        foreach ($players as $player) {
            $player->delete();
        }

        /**
         * @var xlvoVoting[] $votings
         */
        $votings = xlvoVoting::where(array('obj_id' => $this->getId()))->get();
        foreach ($votings as $voting) {
            $voting_id = $voting->getId();

            /**
             * @var xlvoVote[] $votes
             */
            $votes = xlvoVote::where(array('voting_id' => $voting_id))->get();
            foreach ($votes as $vote) {
                $vote->delete();
            }

            /**
             * @var xlvoOption[] $options
             */
            $options = xlvoOption::where(array('voting_id' => $voting_id))->get();
            foreach ($options as $option) {
                $option->delete();
            }

            $voting->delete();
        }

        /**
         * @var xlvoVotingConfig $config
         */
        $config = xlvoVotingConfig::find($this->getId());
        if ($config instanceof xlvoVotingConfig) {
            $config->delete();
        }
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, int $a_copy_id = null): void
    {
        /**
         * @var xlvoVotingConfig $config
         */
        $config = xlvoVotingConfig::find($this->getId());
        if ($config instanceof xlvoVotingConfig) {
            /**
             * @var xlvoVotingConfig $config_clone
             */
            $config_clone = $config->copy();
            $config_clone->setObjId($new_obj->getId());
            // set unique pin for cloned object
            $xlvoPin = new xlvoPin();
            $config_clone->setPin($xlvoPin->getPin());
            $xlvoPuk = new Puk();
            $config_clone->setPuk($xlvoPuk->getPin());
            $config_clone->store();
        }

        /**
         * @var xlvoPlayer $player
         * @var xlvoPlayer $player_clone
         */
        $player = xlvoPlayer::where(array('obj_id' => $this->getId()))->first();
        if ($player instanceof xlvoPlayer) {
            $player_clone = $player->copy();
            // reset active Voting in player
            $player_clone->setActiveVoting(0);
            $player_clone->setObjId($new_obj->getId());
            $player_clone->store();
        }

        /**
         * @var xlvoVoting[] $votings
         */
        $votings = xlvoVoting::where(array('obj_id' => $this->getId()))->get();
        $media_object_ids = array();
        foreach ($votings as $voting) {
            /**
             * @var xlvoVoting $voting_clone
             */
            $voting_clone = $voting->fullClone(false, false);
            $voting_clone->setObjId($new_obj->getId());
            $voting_clone->store();

            $voting_id = $voting->getId();
            $voting_id_clone = $voting_clone->getId();
            $media_objects = ilRTE::_getMediaObjects($voting_clone->getQuestion());
            if (count($media_objects) > 0) {
                $media_object_ids = array_merge($media_object_ids, array_values($media_objects));
            }

            /**
             * @var xlvoOption[] $options
             */
            $options = xlvoOption::where(array('voting_id' => $voting_id))->get();
            foreach ($options as $option) {
                /**
                 * @var xlvoOption $option_clone
                 */
                $option_clone = $option->copy();
                $option_clone->setVotingId($voting_id_clone);
                $option_clone->store();

                $option_id_clone = xlvoOption::where(array('voting_id' => $voting_id_clone))->last()->getId();

                /**
                 * @var xlvoVote[] $votes
                 */
                $votes = xlvoVote::where(array('voting_id' => $voting_id))->get();
                foreach ($votes as $vote) {
                    /**
                     * @var xlvoVote $vote_clone
                     */
                    $vote_clone = $vote->copy();
                    $vote_clone->setVotingId($voting_id_clone);
                    $vote_clone->setOptionId($option_id_clone);
                    //					$vote_clone->store(); // CURRENTLY VOTES WILL NOT BE CLONED
                }
            }
        }
        $new_obj->renegerateVotingSorting();
        foreach ($media_object_ids as $media_object_id) {
            ilObjMediaObject::_saveUsage($media_object_id, 'dcl:html', $new_obj->getId());
        }
    }

    public function renegerateVotingSorting(): void
    {
        $i = 1;
        /**
         * @var xlvoVoting[] $votings
         */
        $votings = xlvoVoting::where(array('obj_id' => $this->getId()))->orderBy('position', 'ASC')->get();

        foreach ($votings as $voting) {
            $voting->setPosition($i);
            $voting->store();
            $i++;
        }
    }
}
