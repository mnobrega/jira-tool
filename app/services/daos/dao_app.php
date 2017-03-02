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
     * @param $hidden Boolean
     * @return Project[]
     */
    public function getTeamProjects($teamKey, $hidden=null)
    {
        $query = "SELECT *
                    FROM ".self::TABLENAME_APP_PROJECTS." p
                    WHERE p.team_key='".$teamKey."'"
                      .(!is_null($hidden)?" AND p.hidden=".($hidden?"TRUE":"FALSE"):"");
        return $this->getObjArray($this->query($query),"Project");
    }

    /**
     * @param $projectName
     * @return ProjectTeamAllocatedTime[]
     */
    public function getProjectTeamAllocatedTime($projectName)
    {
        $query ="SELECT p.name AS project_name,
                    ROUND(SUM(tp.person_allocated_hours_per_day)*(p.team_allocated_percentage/100),2) AS team_allocated_hours_per_day
                    FROM ".self::TABLENAME_APP_PROJECTS." p
                    JOIN ".self::TABLENAME_APP_TEAMS." t ON t.key = p.team_key
                    JOIN ".self::TABLENAME_APP_TEAMS_PERSONS." tp ON tp.team_key = t.key
                    WHERE p.name='".$projectName."'
                    GROUP BY p.name, p.team_allocated_percentage;";

        return $this->getObj($this->query($query),"ProjectTeamAllocatedTime",false);
    }
}

class ProjectTeamAllocatedTime
{
    private $teamName;
    private $teamAllocatedHoursPerDay;

    public function __construct($row)
    {
        $this->teamName=$row['project_name'];
        $this->teamAllocatedHoursPerDay = $row['team_allocated_hours_per_day'];
    }

    public function getTeamName() { return $this->teamName;}
    public function getTeamAllocatedHoursPerDay() {return $this->teamAllocatedHoursPerDay;}
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