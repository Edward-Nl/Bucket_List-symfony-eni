<?php

namespace App\Util;

class Censurator
{
    const CENSURE_WORDS = ["merde","putain","crotte","con","conne","abruti","débile"];

    public function censureText(string $text): string {
        foreach (self::CENSURE_WORDS as $censure_word) {
            $replace = str_repeat("*", mb_strlen($censure_word));
            $text = str_ireplace($censure_word, $replace, $text);
        }
        return $text;
    }
}