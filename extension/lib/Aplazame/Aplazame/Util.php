<?php

class Aplazame_Util {

    public static function formatDecimals($amount = 0) {
        $negative = false;
        $str = sprintf("%.2f", $amount);

        if (strcmp($str[0], "-") === 0) {
            $str = substr($str, 1);
            $negative = true;
        }

        $parts = explode(".", $str, 2);
        if ($parts === false) {
            return 0;
        }

        if (empty($parts)) {
            return 0;
        }

        if (strcmp($parts[0], 0) === 0 && strcmp($parts[1], "00") === 0) {
            return 0;
        }

        $retVal = "";
        if ($negative) {
            $retVal .= "-";
        }
        $retVal .= ltrim( $parts[0] . substr($parts[1], 0, 2), "0");
        return intval($retVal);
    }

}
