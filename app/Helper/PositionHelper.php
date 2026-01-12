<?php

namespace App\Helpers;

class PositionHelper
{
    public static function isUnder($position, $targetName)
    {
        while ($position) {
            if (stripos($position->name, $targetName) !== false) {
                return true;
            }
            $position = $position->parent;
        }
        return false;
    }
}
