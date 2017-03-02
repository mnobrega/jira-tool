<?php

require ROOT_DIR.'vendor/autoload.php';
require ROOT_DIR . 'app/services/daos/dao_app.php';

class AppServiceException extends Exception {};

class AppService
{
    const TEAM_MARKETBILITY_KEY = "MM";

    private $daoApp;

    public function __construct()
    {
        $this->daoApp = new DAOApp();
    }

    /**
     * @param $teamKey
     * @param $hidden  Boolean
     * @return Project[]
     */
    public function getProjectsByTeamKey($teamKey, $hidden=null)
    {
        return $this->daoApp->getTeamProjects($teamKey, $hidden);
    }

    /**
     * @param $projectName
     * @return ProjectTeamAllocatedTime
     */
    public function getProjectTeamAllocatedTime($projectName)
    {
        return $this->daoApp->getProjectTeamAllocatedTime($projectName);
    }

}