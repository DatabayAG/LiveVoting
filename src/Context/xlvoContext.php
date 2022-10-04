<?php

declare(strict_types=1);

namespace LiveVoting\Context;

use ilContext;
use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;
use ilException;

class xlvoContext extends ilContext
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const XLVO_CONTEXT = 'xlvo_context';
    public const CONTEXT_PIN = 1;
    public const CONTEXT_ILIAS = 2;

    public function __construct()
    {
        self::init(xlvoContextLiveVoting::class);
    }

    public static function init(string $context): bool
    {
        ilContext::$class_name = xlvoContextLiveVoting::class;
        ilContext::$type = -1;

        if ($context) {
            self::setContext($context);
        }

        return true;
    }

    /**
     * Sets the xlvo context cookie.
     * This cookie is used to determine the needed bootstrap process.
     * The context constants can be found in the xlvoContext class.
     *
     * @param int $context CONTEXT_ILIAS or CONTEXT_PIN are valid options.
     *
     * @throws ilException Throws exception when the given context is invalid.
     */
    public static function setContext(int $context): void
    {
        if ($context === self::CONTEXT_ILIAS || $context === self::CONTEXT_PIN) {
            $result = setcookie(self::XLVO_CONTEXT, $context, null, '/');
        } else {
            throw new ilException("invalid context received");
        }
        if (!$result) {
            throw new ilException("error setting cookie");
        }
    }

    /**
     * @return int
     */
    public static function getContext(): int
    {
        if (!empty($_COOKIE[self::XLVO_CONTEXT])) {
            return $_COOKIE[self::XLVO_CONTEXT];
        }

        return self::CONTEXT_ILIAS;
    }
}
