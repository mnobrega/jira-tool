<?php

require ROOT_DIR.'vendor/autoload.php';
require ROOT_DIR.'app/services/daos/dao_app.php';

class AppServiceException extends Exception {};

class AppService
{
    const TEAM_MM_Empark_DEV = "MM_EMP_DEV";
    const TEAM_MM_Empark_QA = "MM_EMP_QA";
    const TEAM_MM_Premium_DEV = "MM_PM_DEV";
    const TEAM_MM_Premium_QA = "MM_PM_QA";
    const TEAM_MM_Empark_PMA = "MM_EMP_PMA";

    private $daoApp;

    public function __construct()
    {
        $this->daoApp = new DAOApp();
    }

    /**
     * @param $teamKeys
     * @param $hidden  Boolean
     * @return Project[]
     */
    public function getProjectNamesByTeamKeys(Array $teamKeys, $hidden=null)
    {
        return $this->daoApp->getProjectNamesByTeamKeys($teamKeys, $hidden);
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