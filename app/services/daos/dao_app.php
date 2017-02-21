<?php

require_once (ROOT_DIR.'app/common/pdo_singleton.php');

class DAOApp extends PDOSingleton
{

    function __construct()
    {
        parent::__construct();
    }

    public function getTeamProjects($teamKey)
    {

    }
}