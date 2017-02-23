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
     * @return Project[]
     */
    public function getProjectsByTeamKey($teamKey)
    {
        return $this->daoApp->getTeamProjects($teamKey);
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