<?php

require_once (ROOT_DIR.'app/common/pdo_singleton.php');

class DAOApp extends PDOSingleton
{

    const TABLENAME_APP_PROJECTS = 'app_projects';
    const TABLENAME_APP_TEAMS = 'app_teams';
    const TABLENAME_APP_TEAMS_PERSONS = 'app_teams_persons';
    const TABLENAME_APP_PERSONS = 'app_persons';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $teamKey
     * @return Project []
     */
    public function getTeamProjects($teamKey)
    {
        $query = "SELECT *
                    FROM ".self::TABLENAME_APP_PROJECTS." p
                    WHERE p.team_key='".$teamKey."'";
        return $this->getObjArray($this->query($query),"Project");
    }
}

class Project
{
    private $name;
    private $teamKey;
    private $JIRAProjectKey;
    private $teamAllocatedPercentage;
    private $issuesAllocationCriteriaSQL;

    public function __construct($row)
    {
        $this->name = $row['name'];
        $this->teamKey = $row['team_key'];
        $this->JIRAProjectKey = $row['jira_project_key'];
        $this->teamAllocatedPercentage = $row['team_allocated_percentage'];
        $this->issuesAllocationCriteriaSQL = $row['issues_allocation_criteria_sql'];
    }

    public function getName() { return $this->name;}
    public function getTeamKey() { return $this->teamKey;}
    public function getJIRAProjectKey() { return $this->JIRAProjectKey;}
    public function getTeamAllocatedPercentage() { return $this->teamAllocatedPercentage;}
    public function getIssuesAllocationCriteriaSQL() {return $this->issuesAllocationCriteriaSQL;}

}