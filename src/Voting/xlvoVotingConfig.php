<?php

declare(strict_types=1);

namespace LiveVoting\Voting;

use ilLink;
use ilLiveVotingPlugin;
use ilObject2;
use ilObjectActivation;
use LiveVoting\Cache\CachingActiveRecord;
use LiveVoting\Conf\xlvoConf;
use LiveVoting\Context\Param\ParamManager;
use LiveVoting\Pin\xlvoPin;
use LiveVoting\Puk\Puk;

/**
 * Class xlvoVotingConfig
 *
 * @package LiveVoting\Voting
 * @author  Daniel Aemmer <daniel.aemmer@phbern.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlvoVotingConfig extends CachingActiveRecord
{
    public const B_FROZEN_ALWAY_OFF = false;
    public const B_FROZEN_ALWAY_ON = true;
    public const B_FROZEN_REUSE = 2;
    public const B_RESULTS_ALWAY_OFF = 0;
    public const B_RESULTS_ALWAY_ON = 1;
    public const B_RESULTS_REUSE = 2;
    public const F_ANONYMOUS = 'anonymous';
    public const F_FROZEN_BEHAVIOUR = 'frozen_behaviour';
    public const F_RESULTS_BEHAVIOUR = 'results_behaviour';
    public const F_ONLINE = 'online';
    public const F_REUSE_STATUS = 'reuse_status';
    public const F_TERMINABLE = 'terminable';
    public const F_TERMINABLE_SELECT = "terminable_select";
    public const F_VOTING_HISTORY = "voting_history";
    public const F_SHOW_ATTENDEES = "show_attendees";
    public const TABLE_NAME = 'rep_robj_xlvo_config_n';
    /**
     * @db_is_primary       true
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $obj_id;
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected string $pin = '';
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $obj_online = true;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $anonymous = true;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $terminable = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        timestamp
     */
    protected string $start_date;
    /**
     * @db_has_field        true
     * @db_fieldtype        timestamp
     */
    protected string $end_date;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $reuse_status = true;
    /**
     * @db_has_field        true
     * @db_fieldtype        timestamp
     */
    protected string $last_access = '';
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $frozen_behaviour = self::B_FROZEN_ALWAY_OFF;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected int $results_behaviour = self::B_RESULTS_ALWAY_OFF;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $voting_history = false;
    protected bool $full_screen = true;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $show_attendees = false;
    protected bool $self_vote = false;
    protected bool $keyboard_active = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected string $puk = '';

    /**
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    public function isAvailableForUser(): bool
    {
        if (!$this->getObjId()) {
            return false;
        }
        $available = true;
        $ref_ids = ilObject2::_getAllReferences($this->getObjId());
        foreach ($ref_ids as $ref_id) {
            $item_data = ilObjectActivation::getItem($ref_id);
            if ($item_data['timing_type'] === ilObjectActivation::TIMINGS_ACTIVATION) {
                if ($item_data['timing_start'] > time() || $item_data['timing_end'] < time()) {
                    $available = false;
                }
            }
        }

        return $available;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function getShortLinkURL(bool $force_not_format = false, int $ref_id = 0): string
    {
        global $DIC;

        $url = null;

        switch ($this->isAnonymous()) {
            case true:
                $shortLinkEnabled = (bool) xlvoConf::getConfig(xlvoConf::F_ALLOW_SHORTLINK_VOTE);

                if ($shortLinkEnabled) {
                    $url = xlvoConf::getConfig(xlvoConf::F_ALLOW_SHORTLINK_VOTE_LINK);
                    $url = rtrim($url, "/") . "/";
                } else {
                    $url = ILIAS_HTTP_PATH . substr(
                        self::plugin()->directory(),
                        1
                    ) . '/pin.php?' . ParamManager::PARAM_PIN . '=';
                }
                $url .= xlvoPin::formatPin($this->getPin(), $force_not_format);
                break;
            default:
                $url = ilLink::_getStaticLink($ref_id, ilLiveVotingPlugin::PLUGIN_ID, true);
                break;
        }

        return $url;
    }

    public function isAnonymous(): bool
    {
        return $this->anonymous;
    }

    public function setAnonymous(bool $anonymous): void
    {
        $this->anonymous = $anonymous;
    }

    public function getPin(): string
    {
        return $this->pin;
    }

    public function setPin(string $pin): void
    {
        $this->pin = $pin;
    }

    public function getPresenterLink(int $voting_id = null, bool $power_point = false, bool $force_not_format = false, bool $https = true): ?string
    {
        $url = null;

        if (!$this->isAnonymous()) {
            return null;
        }

        $shortLinkEnabled = (bool) xlvoConf::getConfig(xlvoConf::F_ALLOW_SHORTLINK_PRESENTER);

        if ($shortLinkEnabled) {
            $url = xlvoConf::getConfig(xlvoConf::F_ALLOW_SHORTLINK_PRESENTER_LINK);
            $url = rtrim($url, "/") . "/";
            $url .= xlvoPin::formatPin($this->getPin(), $force_not_format) . "/" . Puk::formatPin(
                $this->getPuk(),
                $force_not_format
            );
            if ($voting_id !== null) {
                $url .= "/" . $voting_id;
            }
            if ($power_point) {
                $url .= "/1";
            }
        } else {
            $url = ILIAS_HTTP_PATH . substr(
                self::plugin()->directory(),
                1
            ) . '/presenter.php?' . ParamManager::PARAM_PIN . '='
                . xlvoPin::formatPin($this->getPin(), $force_not_format) . "&" . ParamManager::PARAM_PUK . "="
                . Puk::formatPin($this->getPuk(), $force_not_format);
            if ($voting_id !== null) {
                $url .= "&" . ParamManager::PARAM_VOTING . "=" . $voting_id;
            }
            if ($power_point) {
                $url .= "&" . ParamManager::PARAM_PPT . "=1";
            }
        }

        if (!$https) {
            $url = substr($url, (strpos($url, "://") + 3));
        }

        return $url;
    }

    public function getPuk(): string
    {
        return $this->puk;
    }

    public function setPuk(string $puk): void
    {
        $this->puk = $puk;
    }

    public function isObjOnline(): bool
    {
        return $this->obj_online;
    }

    public function setObjOnline(bool $obj_online): void
    {
        $this->obj_online = $obj_online;
    }

    public function isTerminable(): bool
    {
        return $this->terminable;
    }

    public function setTerminable(bool $terminable): void
    {
        $this->terminable = $terminable;
    }

    public function getStartDate(): string
    {
        return $this->start_date;
    }

    public function setStartDate(string $start_date): void
    {
        $this->start_date = $start_date;
    }

    public function getEndDate(): string
    {
        return $this->end_date;
    }

    public function setEndDate(string $end_date): void
    {
        $this->end_date = $end_date;
    }

    public function isReuseStatus(): bool
    {
        return $this->reuse_status;
    }

    public function setReuseStatus(bool $reuse_status): void
    {
        $this->reuse_status = $reuse_status;
    }

    public function getLastAccess(): string
    {
        return $this->last_access;
    }

    public function setLastAccess(string $last_access): void
    {
        $this->last_access = $last_access;
    }

    public function isFullScreen(): bool
    {
        return $this->full_screen;
    }

    public function setFullScreen(bool $full_screen): void
    {
        $this->full_screen = $full_screen;
    }

    public function isShowAttendees(): bool
    {
        return $this->show_attendees;
    }

    public function setShowAttendees(bool $show_attendees): void
    {
        $this->show_attendees = $show_attendees;
    }

    public function isSelfVote(): bool
    {
        return $this->self_vote;
    }

    public function setSelfVote(bool $self_vote): void
    {
        $this->self_vote = $self_vote;
    }

    public function isKeyboardActive(): bool
    {
        return $this->keyboard_active;
    }

    public function setKeyboardActive(bool $keyboard_active): void
    {
        $this->keyboard_active = $keyboard_active;
    }

    public function getFrozenBehaviour(): bool
    {
        return $this->frozen_behaviour;
    }

    public function setFrozenBehaviour(bool $frozen_behaviour): void
    {
        $this->frozen_behaviour = $frozen_behaviour;
    }

    public function getResultsBehaviour(): int
    {
        return $this->results_behaviour;
    }

    public function setResultsBehaviour(int $results_behaviour): void
    {
        $this->results_behaviour = $results_behaviour;
    }

    public function getVotingHistory(): bool
    {
        return $this->voting_history;
    }

    public function setVotingHistory(bool $voting_history): void
    {
        $this->voting_history = $voting_history;
    }
}
