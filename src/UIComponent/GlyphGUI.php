<?php

declare(strict_types=1);

namespace LiveVoting\UIComponent;

use ilGlyphGUI;
use srag\DIC\LiveVoting\DICTrait;

class GlyphGUI extends ilGlyphGUI
{
    use DICTrait;

    public static function get(string $a_glyph, string $a_text = ""): string
    {
        if ($a_glyph === 'remove') {
            self::$map[$a_glyph]['class'] = 'glyphicon glyphicon-' . $a_glyph;
        }
        if (!isset(self::$map[$a_glyph])) {
            self::$map[$a_glyph]['class'] = 'glyphicon glyphicon-' . $a_glyph;
        }

        return parent::get($a_glyph, $a_text) . ' ';
    }

    public static function gets(string $a_glyph): string
    {
        self::$map[$a_glyph]['class'] = 'glyphicons glyphicons-' . $a_glyph;

        return parent::get($a_glyph, '') . ' ';
    }
}
