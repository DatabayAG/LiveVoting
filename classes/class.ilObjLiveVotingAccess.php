<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Voting\xlvoVotingConfig;
use srag\DIC\LiveVoting\DICTrait;

/**
 * Access/Condition checking for LiveVoting object
 *
 * Please do not create instances of large application classes (like ilObjExample)
 * Write small methods within this class to determin the status.
 *
 * @author  Daniel Aemmer <daniel.aemmer@phbern.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version $Id$
 */
class ilObjLiveVotingAccess extends ilObjectPluginAccess
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    /**
     * @param $obj_id
     * @param $user_id
     *
     * @return bool
     */
    public static function hasReadAccessForObject($obj_id, $user_id): bool
    {
        $refs = ilObject2::_getAllReferences($obj_id);
        foreach ($refs as $ref_id) {
            if (self::hasReadAccess($ref_id, $user_id)) {
                return true;
                break;
            }
        }

        return false;
    }

    public static function hasReadAccess(int $ref_id = null, int $user_id = null): bool
    {
        return self::hasAccess('read', $ref_id, $user_id);
    }

    /**
     * @param      $permission
     * @param null $ref_id
     * @param null $user_id
     *
     * @return bool
     */
    protected static function hasAccess(string $permission, int $ref_id = null, int $user_id = null): bool
    {
        $ref_id = $ref_id ?: (int) $_GET['ref_id'];
        $user_id = $user_id ?: self::dic()->user()->getId();
        //		if (!$this->user_id) {
        //			try {
        //				throw new Exception();
        //			} catch (Exception $e) {
        //              $ilLog = $DIC["ilLog"];
        //				$ilLog->write('XLVO ##########################');
        //				$ilLog->write('XLVO ' . xlvoInitialisation::getContext());
        //				$ilLog->write('XLVO ' . $e->getTraceAsString());
        //				$ilLog->write('XLVO ##########################');
        //				return true;
        //			}
        //		}

        //		$ilLog->write('XLVO check permission ' . $permission . ' for user ' .$this->user_id . ' on ref_id ' . $ref_id);

        return self::dic()->access()->checkAccessOfUser($user_id, $permission, '', $ref_id);
    }

    public static function hasWriteAccessForObject(int $obj_id, int $user_id): bool
    {
        $refs = ilObject2::_getAllReferences($obj_id);

        foreach ($refs as $ref_id) {
            if (self::hasWriteAccess($ref_id, $user_id)) {
                return true;
                break;
            }
        }

        return false;
    }

    public static function hasWriteAccess(int $ref_id = null, int $user_id = null): bool
    {
        return self::hasAccess('write', $ref_id, $user_id);
    }

    public static function hasDeleteAccess(int $ref_id = null, int $user_id = null): bool
    {
        return self::hasAccess('delete', $ref_id, $user_id);
    }

    public static function hasCreateAccess(int $ref_id = null, int $user_id = null): bool
    {
        return self::hasAccess('create_xlvo', $ref_id, $user_id);
    }

    /**
     * Checks wether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     *
     * Please do not check any preconditions handled by
     * ilConditionHandler here. Also don't do usual RBAC checks.
     *
     * @param string $a_cmd        command (not permission!)
     * @param string $a_permission permission
     * @param int    $a_ref_id     reference id
     * @param int    $a_obj_id     object id
     * @param string $a_user_id    user id (if not provided, current user is taken)
     *
     * @return    bool        true, if everything is ok
     */
    public function _checkAccess(
        string $a_cmd,
        string $a_permission,
        int $a_ref_id,
        int $a_obj_id,
        int $a_user_id = null
    ): bool {
        if ($a_user_id === null) {
            $a_user_id = self::dic()->user()->getId();
        }

        switch ($a_permission) {
            case "visible":
            case "read":
                if (!self::checkOnline($a_obj_id)
                    && !self::dic()->access()->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)
                ) {
                    return false;
                }
                break;
        }

        return true;
    }

    public static function checkOnline($a_id = null): bool
    {
        /**
         * @var xlvoVotingConfig $config
         */
        $obj_id = $a_id ?: ilObject2::_lookupObjId($_GET['ref_id']);
        $config = xlvoVotingConfig::find($obj_id);
        if ($config instanceof xlvoVotingConfig) {
            return $config->isObjOnline();
        }

        return false;
    }
}
