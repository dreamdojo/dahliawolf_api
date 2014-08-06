<?php

class profileOptions extends db {

    private $table = 'profile_options';

    public function __construct() {
        parent::__construct();
    }

    public function addUser($user_id) {
       $q = 'INSERT INTO profile_options (user_id) VALUES "'.$user_id.'"';

        $data = $this->run($q);

        return resultArray(true, $data);
    }
}
