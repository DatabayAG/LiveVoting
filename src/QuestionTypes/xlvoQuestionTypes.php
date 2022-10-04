<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes;

use ilLiveVotingPlugin;
use LiveVoting\Exceptions\xlvoVotingManagerException;
use LiveVoting\Utils\LiveVotingTrait;
use ReflectionClass;
use srag\DIC\LiveVoting\DICTrait;

/**
 * Class xlvoQuestionTypes
 *
 * @package LiveVoting\QuestionTypes
 * @author  Daniel Aemmer <daniel.aemmer@phbern.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlvoQuestionTypes
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const TYPE_SINGLE_VOTE = 1;
    public const TYPE_FREE_INPUT = 2;
    public const TYPE_RANGE = 3;
    public const TYPE_CORRECT_ORDER = 4;
    public const TYPE_FREE_ORDER = 5;
    public const TYPE_NUMBER_RANGE = 6;
    public const SINGLE_VOTE = 'SingleVote';
    public const FREE_INPUT = 'FreeInput';
    public const CORRECT_ORDER = 'CorrectOrder';
    public const FREE_ORDER = 'FreeOrder';
    public const NUMBER_RANGE = 'NumberRange';
    /** @var int[]  */
    protected static array $active_types
        = [
            self::TYPE_FREE_INPUT,
            self::TYPE_SINGLE_VOTE,
            self::TYPE_CORRECT_ORDER,
            self::TYPE_FREE_ORDER,
            self::TYPE_NUMBER_RANGE
        ];
    /** @var string[]  */
    protected static array $class_map
        = [
            self::TYPE_SINGLE_VOTE => self::SINGLE_VOTE,
            self::TYPE_FREE_INPUT => self::FREE_INPUT,
            self::TYPE_CORRECT_ORDER => self::CORRECT_ORDER,
            self::TYPE_FREE_ORDER => self::FREE_ORDER,
            self::TYPE_NUMBER_RANGE => self::NUMBER_RANGE
        ];

    public static function getActiveTypes(): array
    {
        // TODO: Just return self::$active_types;

        $f = new ReflectionClass(self::class);
        $types = array();
        foreach ($f->getConstants() as $constant_name => $constant) {
            if (strpos($constant_name, 'TYPE_') === 0 && in_array($constant, self::$active_types, true)) {
                $types[] = $constant;
            }
        }

        return $types;
    }

    /**
     * @throws xlvoVotingManagerException
     */
    public static function getClassName(int $type): string
    {
        if (!isset(self::$class_map[$type])) {
            //			throw new xlvoVotingManagerException('Type not available');
        }

        return self::$class_map[$type];
    }
}
