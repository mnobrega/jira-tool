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
    public function getProjectNamesByTeamKeys(Array $teamKeys, $hidden=null)
    {
        $query = "SELECT p.name
                    FROM ".self::TABLENAME_APP_PROJECTS." p
                    WHERE p.team_key IN ".$this->inArray($teamKeys)
                        .(!is_null($hidden)?" AND p.hidden=".($hidden?"TRUE":"FALSE"):"")."
                    GROUP BY p.name";
        return $this->getObjArray($this->query($query),"ProjectName");
    }

    /**
     * @param $projectName
     * @return ProjectTeamAllocatedTime[]
     */
    public function getProjectsTeamAllocatedTime(Array $projectNames)
    {
        $query ="SELECT x.project_name, SUM(team_allocated_hours_per_day) AS team_allocated_hours_per_day FROM (
                        SELECT p.name AS project_name,
                          ROUND(SUM(tp.person_allocated_hours_per_day)*(p.team_allocated_percentage/100),2) AS team_allocated_hours_per_day
                        FROM ".self::TABLENAME_APP_PROJECTS." p
                            JOIN ".self::TABLENAME_APP_TEAMS." t ON t.key = p.team_key
                            JOIN ".self::TABLENAME_APP_TEAMS_PERSONS." tp ON tp.team_key = t.key
                        WHERE p.name IN ".$this->inArray($projectNames)."
                        GROUP BY p.name, p.team_allocated_percentage) x
                    GROUP BY x.project_name;";
        return $this->getObjArray($this->query($query),"ProjectTeamAllocatedTime");
    }

    /**
     * @return Project []
     */
    public function getProjects()
    {
        $query = "SELECT *
                    FROM ".self::TABLENAME_APP_PROJECTS." p
                    ORDER BY p.name ASC;";
        return $this->getObjArray($this->query($query),"Project");
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

class ProjectName
{
    private $name;

    public function __construct($row)
    {
        $this->name = $row['name'];
    }

    public function getName() {return $this->name;}
}

class Project extends ProjectName
{
    private $teamKey;
    private $JIRAProjectKey;
    private $teamAllocatedPercentage;

    public function __construct($row)
    {
        parent::__construct($row);
        $this->teamKey = $row['team_key'];
        $this->JIRAProjectKey = $row['jira_project_key'];
        $this->teamAllocatedPercentage = $row['team_allocated_percentage'];
    }

    public function getTeamKey() { return $this->teamKey;}
    public function getJIRAProjectKey() { return $this->JIRAProjectKey;}
    public function getTeamAllocatedPercentage() { return $this->teamAllocatedPercentage;}

}

