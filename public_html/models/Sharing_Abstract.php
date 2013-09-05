<?php
/**
 * User: JDorado
 * Date: 9/4/13
 */
 
abstract class Sharing_Abstract extends _Model
{

    public abstract function addShare($data = array());
    public abstract function deleteShare($params = array());
    public abstract function getShares($params = array());
    public abstract function getSharesCount($params = array());
    public abstract function getTotalShares($params = array());

}

?>