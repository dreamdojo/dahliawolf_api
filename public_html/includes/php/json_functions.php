<?php
/**
 * User: JDorado
 * Date: 6/27/13
 */


function json_pretty($json, $indent='  ')
{
    is_array($json ) || is_object($json )? $json = json_encode($json) : null;
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = $indent;
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        $char = substr($json, $i, 1);

        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }

        $result .= $char;

        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return trim($result);
}
 

?>