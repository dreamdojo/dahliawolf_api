<?php
/**
 * User: JDorado
 * Date: 12/18/13
 */
 
class StringUtils
{

    public static function getRandomString($len = 10)
    {
        $seeds = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $i = 0;
        $rand = "";
        while ($len > $i) {
            $seed_i = rand(0, strlen($seeds));
            $rand .= $seeds{$seed_i};
            //
            $i++;
        }
        return $rand;
    }

}

?>