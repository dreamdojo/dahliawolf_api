<?php
/**
 * User: JDorado
 * Date: 12/18/13
 */


class Timer extends Jk_Base
{

    //// run for seconds
    public function run($time=1)
    {
        $start_time = self::getTime();
        $running_time = self::getTotaltime($start_time, true);

        // in Ms
        $time_limit = $time;
        $loops = 0;

        self::addData('time_limit', sprintf("RUNING WITH TIME LIMIT: %s SECS", $time_limit) );
        self::addData( 'test_start_time', sprintf("START TIME %s", $start_time) );

        self::setMainLog( sprintf('%s.log', strtolower(get_class($this))) );

        while( $time_limit > $running_time )
        {
            $running_time = self::getTotaltime($start_time, true);
            //self::addData( 'running_time', sprintf("%s", $running_time) );


            if(round($running_time, 5) == floor($running_time))self::debug("running time: $running_time");
            $loops++;
        }

        self::addData("TEST RUNNING TIME", self::getTotaltime($start_time));

        return self::getData();
    }
}

?>