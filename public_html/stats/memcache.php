<?php

/**
 * User: JDorado
 * Date: 7/30/13
 */

##########################################
if( isset($_GET['print']) )
{
    echo "MEMCACHE STATS:<br>\n";
     getMemcacheKeys();
}
##########################################

function getMemcacheKeys() {
    $memcache = new Memcache;
    $memcache->connect('127.0.0.1', 11211)
       or die ("Could not connect to memcache server");

    echo "fetching 127.0.0.1:11211 stats:<br>\n";

    $list = array();
    $allSlabs = $memcache->getExtendedStats('slabs');
    $items = $memcache->getExtendedStats('items');
    foreach($allSlabs as $server => $slabs) {
        foreach($slabs AS $slabId => $slabMeta) {
            $cdump = $memcache->getExtendedStats('cachedump',(int)$slabId);
            foreach($cdump AS $keys => $arrVal) {
                if (!is_array($arrVal)) continue;
                foreach($arrVal AS $k => $v) {
                    $val = $memcache->get($k);
                    echo "$k\n \t=>" . var_export($val, true) .'<br>';
                }
            }
        }
    }
}


?>