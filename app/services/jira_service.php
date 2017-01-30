<?php

require ROOT_DIR.'vendor/autoload.php';
require ROOT_DIR.'app/common/converters.php';
require ROOT_DIR.'app/services/time_service.php';
require ROOT_DIR . 'app/services/daos/dao_jira_issues.php';

class JIRAServiceException extends Exception {};

class JIRAService
{
    const HISTORY_ITEM_TYPE_STATUS = 'status';

    const RESOURCE_TYPE_DEV = 'dev';
    const RESOURCE_TYPE_QA = 'qa';

    const WORKING_DAY_HOURS = 8;
    const DEFAULT_GANTT_ISSUE_COLOR = '#cccccc';
    const GANTT_ISSUE_COLOR_DELAYED = '#ff0000';

    const DELAY_DAYS_THRESHOLD = 5; //days

    static $projectUsersToResourcesMapping = array(
        "eos Market" => array(
            "lgoncalves"=>"APKDEV1",
            "rlacmane"=>"APKDEV1",
            "mnobrega"=>"APKDEV1",
            "sottaviani"=>"QA1",
            "mmatos"=>"QA2"
        ),
        "eos Mobility" => array(
            "asoares"=>"MOBDEV1",
            "sguerreiro"=>"MOBDEV2",
            "lgoncalves"=>"MOBDEV2",
            "sottaviani"=>"QA1",
            "mmatos"=>"QA2",
            "mnobrega"=>"QA2"
        )
    );

    static $projectResourcesType = array (
        "eos Market" => array(
            "dev" => array("APKDEV1"),
            "qa" => array("QA1","QA2")
        ),
        "eos Mobility" => array(
            "dev" => array("MOBDEV1","MOBDEV2"),
            "qa" => array("QA1","QA2")
        )
    );

    static $epicColorMappings = array (
        "ghx-label-1" => "#815b3a",
        "ghx-label-2" => "#f79232",
        "ghx-label-3" => "#d39c3f",
        "ghx-label-4" => "#3b7fc4",
        "ghx-label-5" => "#4a6785",
        "ghx-label-6" => "#8eb021",
        "ghx-label-7" => "#ac707a",
        "ghx-label-8" => "#654982",
        "ghx-label-9" => "#f15c75"
    );

    private $api;
    private $walker;
    private $timeService;

    private $daoJIRAIssues;

    function __construct()
    {
        $this->api = new \Jira_Api(JIRA_URL, new \Jira_Api_Authentication_Basic(JIRA_USERNAME, JIRA_PASSWORD));
        $this->walker = new Jira_Issues_Walker($this->api);
        $this->timeService = new TimeService();

        $this->daoJIRAIssues = new DAOJIRAIssues();
    }

    /**
     * @param array $status
     * @return JIRAIssue []
     */
    public function getIssuesByStatuses(Array $status)
    {
        $issues = array();
        $statusString = '"' . implode('","', $status) . '"';

        $this->walker->push('status IN (' . $statusString . ')  AND resolution=Unresolved ORDER BY priority ASC');
        foreach ($this->walker as $issue) {
            /**@var $issue Jira_Issue */
            $issues[] = new JIRAIssue($issue);
        }

        return $issues;
    }

    public function getIssuesByTypes(Array $types)
    {
        $issues = array();
        $typesString = '"' . implode('","', $types) . '"';

        $this->walker->push('type IN ('.$typesString.') ORDER BY priority ASC');
        foreach($this->walker as $issue) {
            /**@var $issue Jira_Issue */
            $issues[] = new JIRAIssue($issue);
        }

        return $issues;
    }

    public function deleteAllPersistedIssues()
    {
        $this->daoJIRAIssues->deleteAllJIRAIssues();
    }

    /**
     * @param $issues JIRAIssue []
     */
    public function persistIssues(Array $issues)
    {
        foreach ($issues as $issue)
        {
            $this->daoJIRAIssues->insertJIRAIssue(new JIRAIssueTblTuple($issue->toArray()));
        }
    }

    /**
     * @param array|null $statuses
     * @return JIRAIssueTblTuple []
     */
    public function getPersistedIssues(Array $statuses=null, Array $types=null)
    {
        return $this->daoJIRAIssues->searchJIRAIssues($statuses, $types);
    }

    /**
     * @param $issues JIRAIssue []
     */
    public function getIssuesHistories(Array $issues, Array $historyTypes)
    {
        $issuesHistories = array();

        foreach ($issues as $issue) {
            $JiraApiResult = $this->api->getIssue($issue->getIssueKey(), "changelog");
            /**@var $JiraApiResult Jira_Api_Result */
            $expandedInformation = $JiraApiResult->getResult();
            $changelog = $expandedInformation['changelog'];

            foreach ($changelog['histories'] as $history) {
                $historyItems = array("historyDatetime"=>$history['created'],"items"=>array());
                foreach ($history['items'] as $item) {
                    if ($item['field']==self::HISTORY_ITEM_TYPE_STATUS)
                    {
                        $historyItems['items'][] = $item;
                    }
                }
                if (count($historyItems)>0)
                {
                    $issuesHistories[$issue->getIssueKey()][] = $historyItems;
                }
            }
        }

        return $issuesHistories;
    }

    public function persistIssuesHistories(Array $issuesHistories)
    {
        $this->daoJIRAIssues->deleteAllJIRAIssuesHistories();
        foreach ($issuesHistories as $issueKey=>$issueHistory) {
            foreach ($issueHistory as $historyItems) {
                $row['issue_key'] = $issueKey;
                $historyDatetime = new DateTime($historyItems['historyDatetime'],new DateTimeZone(INSTANCE_TIMEZONE));
                $row['history_datetime'] = $historyDatetime->format("Y-m-d H:i:s");
                foreach ($historyItems['items'] as $item)
                {
                    $row['field'] = $item['field'];
                    $row['from_string'] = $item['fromString'];
                    $row['to_string'] = $item['toString'];
                    $JIRAIssueHistoryTblTuple = new JIRAIssueHistoryTblTuple($row);
                    $this->daoJIRAIssues->insertJIRAIssueHistory($JIRAIssueHistoryTblTuple);
                }
            }
        }
    }

    /**
     * @param $issues JIRAIssue []
     */
    public function getPersistedIssuesTimeSpent(Array $issues)
    {
        $fields = array(DAOJIRAIssues::HISTORY_ITEM_FIELD_STATUS);
        $fromStrings = array(DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::STATUS_QA_IN_PROGRESS);
        $toStrings = array(DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::STATUS_QA_IN_PROGRESS);

        $issuesTimeSpent = array();
        $now = new DateTime();

        foreach ($issues as $issue){
            $issueHistories = $this->daoJIRAIssues->searchJIRAIssueHistories($issue->getIssueKey(),$fields,
                $fromStrings,$toStrings);
            $timeIntervals = array();
            $timeInterval = null;
            foreach ($issueHistories as $issueHistory) {

                if ($issueHistory->getToString()==DAOJIRAIssues::STATUS_DEV_IN_PROGRESS)
                {
                    if (!is_null($timeInterval)) {
                        $timeIntervals[] = $timeInterval;
                    }
                    $timeInterval = array('start'=>$issueHistory->getHistoryDatetime(),'end'=>$now->format(DATE_ISO8601));
                }

                if ($issueHistory->getFromString()==DAOJIRAIssues::STATUS_DEV_IN_PROGRESS)
                {
                    if (is_array($timeInterval)) {
                        $timeInterval['end'] = $issueHistory->getHistoryDatetime();
                    }
                    else
                    {
                        throw new Exception("Interval start not found for issue:".$issue->getIssueKey());
                    }
                }
            }
            if (!is_null($timeInterval)) {
                $timeIntervals[] = $timeInterval;
            }

            $issuesTimeSpent[$issue->getIssueKey()] = $this->timeService->getWorkingHours($timeIntervals);
        }

        return $issuesTimeSpent;
    }

    public function getGanttData()
    {
        $selectedTypes = array(
            DAOJIRAIssues::TYPE_STORY,
            DAOJIRAIssues::TYPE_TASK,
            DAOJIRAIssues::TYPE_BUG
        );

        $epicTypes = array (
            DAOJIRAIssues::TYPE_EPIC
        );

        $inProgressIssueStatuses = array(
            DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::STATUS_QA_IN_PROGRESS
        );

        $todoIssueStatuses = array (
            DAOJIRAIssues::STATUS_TO_DEVELOP,
            DAOJIRAIssues::STATUS_TO_QUALITY
        );

        $longTermIssuesStatus = array (
            DAOJIRAIssues::STATUS_ANALYSED
        );

        $fields = array(DAOJIRAIssues::HISTORY_ITEM_FIELD_STATUS);
        $fromStrings = array(DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::STATUS_QA_IN_PROGRESS);
        $toStrings = array(DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::STATUS_QA_IN_PROGRESS);

        $resourcesIssues = array();

        $inProgressIssues = $this->getPersistedIssues($inProgressIssueStatuses, $selectedTypes);
        $resourcesIssuesTimeSpent = $this->getPersistedIssuesTimeSpent($inProgressIssues);
        $this->addToResourcesIssues($resourcesIssues,$inProgressIssues);

        $todoIssues = $this->getPersistedIssues($todoIssueStatuses,$selectedTypes);
        $resourcesIssuesTimeSpent = array_merge($resourcesIssuesTimeSpent,$this->getPersistedIssuesTimeSpent($todoIssues));
        $this->addToResourcesIssues($resourcesIssues,$todoIssues);

        $longTermIssues = $this->getPersistedIssues($longTermIssuesStatus, $selectedTypes);
        $resourcesIssuesTimeSpent = array_merge($resourcesIssuesTimeSpent,$this->getPersistedIssuesTimeSpent($longTermIssues));
        $this->addToResourcesIssues($resourcesIssues, $longTermIssues);

        $epicIssuesMap = array();
        $epicIssues = $this->getPersistedIssues(null,$epicTypes);
        foreach ($epicIssues as $epicIssue)
        {
            if (!array_key_exists($epicIssue->getIssueKey(),$epicIssuesMap))
            {
                $epicIssuesMap[$epicIssue->getIssueKey()] = array (
                    "name" => $epicIssue->getEpicName(),
                    "color" => self::$epicColorMappings[$epicIssue->getEpicColour()]
                );
            }
        }

        $JIRAGanttIssues = array();
        $now = new DateTime();
        foreach ($resourcesIssues as $resource=>$issues)
        {
            $JIRAGanttIssues[$resource] = array();
            $currentStart = null;
            foreach ($issues as $issue) {

                /**@var $issue JIRAIssueTblTuple */
                $workingDaysLeft = max(0.00,ceil(($issue->getOriginalEstimate()/3600 -
                        $resourcesIssuesTimeSpent[$issue->getIssueKey()])/self::WORKING_DAY_HOURS));

                if ($workingDaysLeft>0) {
                    $row = array();
                    $row['resource'] = $resource;
                    $row['issueKey'] = $issue->getIssueKey();
                    $row['priority'] = $issue->getPriority();
                    $row['status'] = $issue->getIssueStatus();
                    $row['workingDaysLeft'] = $workingDaysLeft;
                    $row['label'] = $issue->getIssueKey()." ".$issue->getSummary();
                    $row['epicColor'] = (!is_null($issue->getEpicLink())?$epicIssuesMap[$issue->getEpicLink()]['color']:
                        self::DEFAULT_GANTT_ISSUE_COLOR);
                    $row['epicName'] = (!is_null($issue->getEpicLink())?$epicIssuesMap[$issue->getEpicLink()]['name']:"Outros");
                    $row['start'] = (is_null($currentStart)?$now->format("Y-m-d 00:00:00"):$currentStart->format("Y-m-d H:i:s"));
                    $endDate = $this->timeService->getEndDateFromWorkingHours(new DateTime($row['start']),
                        $row['workingDaysLeft']);
                    $endDate->modify("-1 seconds");
                    $row['end'] = $endDate->format("Y-m-d H:i:s");
                    if (!is_null($issue->getReleaseDate()))
                    {
                        $releaseDate = new DateTime($issue->getReleaseDate()." 20:00:00");
                        $releaseDate->modify("-".self::DELAY_DAYS_THRESHOLD." days");
                        if ($endDate->getTimestamp()>$releaseDate->getTimestamp())
                        {
                            $row['epicColor'] = self::GANTT_ISSUE_COLOR_DELAYED;
                            $row['epicName'] = "DELAYED";
                        }
                    }
                    if (is_null($currentStart) && in_array($issue->getIssueStatus(),$inProgressIssueStatuses))
                    {
                        $issueHistories = $this->daoJIRAIssues->searchJIRAIssueHistories($issue->getIssueKey(),$fields,
                            $fromStrings,$toStrings);

                        $row['start'] = $issueHistories[0]->getHistoryDatetime();
                    }
                    $currentStart = new DateTime($row['end']);
                    $currentStart->modify("+1 second");
                    $JIRAGanttIssues[$resource][] = new JIRAGanttIssue($row);
                } else {
                    //ignore issue
                }
            }
        }

        return $JIRAGanttIssues;
    }

    /**
     * @param $resourcesIssues
     * @param $issues JIRAIssueTblTuple []
     * @throws JIRAServiceException
     */
    private function addToResourcesIssues(Array &$resourcesIssues, $issues)
    {
        foreach ($issues as $issue)
        {
            if ($issue->getAssigneeKey()!='')
            {
                $resource = self::$projectUsersToResourcesMapping[$issue->getProject()][$issue->getAssigneeKey()];
            }
            else
            {
                $resource = $this->findIssueBestFitResource($issue,$resourcesIssues);
            }
            if (!array_key_exists($resource,$resourcesIssues))
            {
                $resourcesIssues[$resource] = array();
            }
            $resourcesIssues[$resource][] = $issue;
        }
    }

    private function findIssueBestFitResource(JIRAIssueTblTuple $issue, Array $resourcesIssues=null)
    {
        $resource = null;
        $resourcesIssuesNumber = array();
        $devStatus = array (
            DAOJIRAIssues::STATUS_ANALYSED,
            DAOJIRAIssues::STATUS_TO_DEVELOP,
        );
        $qaStatuses = array (
            DAOJIRAIssues::STATUS_TO_QUALITY
        );


        foreach ($resourcesIssues as $resource=>$resourceIssues)
        {
            $resourcesIssuesNumber[$resource] = count($resourceIssues);
        }

        if (in_array($issue->getIssueStatus(),$devStatus))
        {
            $resourcesList = self::$projectResourcesType[$issue->getProject()]["dev"];
        }
        elseif (in_array($issue->getIssueStatus(),$qaStatuses))
        {
            $resourcesList = self::$projectResourcesType[$issue->getProject()]["qa"];
        }
        else
        {
            throw new JIRAServiceException("Unknown issue ".$issue->getIssueKey()." status ".$issue->getIssueStatus());
        }

        $min = null;
        $selectedResource = null;
        foreach ($resourcesList AS $resource)
        {
            if (!array_key_exists($resource,$resourcesIssuesNumber))
            {
                $selectedResource = $resource;
                break;
            }
            else
            {
                if (is_null($min) || $min > $resourcesIssuesNumber[$resource])
                {
                    $min = $resourcesIssuesNumber[$resource];
                    $selectedResource = $resource;
                }
            }
        }

        return $selectedResource;
    }
}

class JIRAGanttIssue
{
    private $resource;
    private $issueKey;
    private $priority;
    private $status;
    private $workingDaysLeft;
    private $label;
    private $epicColor;
    private $epicName;
    private $start;
    private $end;

    public function __construct($row)
    {
        $this->resource = $row['resource'];
        $this->issueKey = $row['issueKey'];
        $this->priority = $row['priority'];
        $this->status = $row['status'];
        $this->workingDaysLeft = $row['workingDaysLeft'];
        $this->start = $row['start'];
        $this->end = $row['end'];
        $this->label = $row['label'];
        $this->epicColor = $row['epicColor'];
        $this->epicName = $row['epicName'];
    }

    public function getResource() { return $this->resource;}
    public function getIssueKey() { return $this->issueKey;}
    public function getPriority() { return $this->priority;}
    public function getStatus() { return $this->status;}
    public function getWorkingDaysLeft() { return $this->workingDaysLeft;}
    public function getStart() { return $this->start;}
    public function getEnd() { return $this->end;}
    public function getLabel() { return $this->label;}
    public function getEpicColor() { return $this->epicColor;}
    public function getEpicName() { return $this->epicName;}
}

class JIRAIssue
{
    private $issueKey;
    private $summary;
    private $issueType;
    private $project;
    private $originalEstimate;
    private $remainingEstimate;
    private $releaseDate;
    private $labels;
    private $assignee;
    private $assigneeKey;
    private $releaseSummary;
    private $issueStatus;
    private $requestor;
    private $epicName;
    private $epicLink;
    private $epicColour;

    public function __construct(Jira_Issue $issue)
    {
        $fields = $issue->getFields();
        $priority = $issue->getPriority();
        $status = $issue->getStatus();

        $this->issueKey = $issue->getKey();
        $this->summary = $issue->getSummary();
        $this->priority = $priority['id'];
        $this->issueType = $fields['Issue Type']['name'];
        $this->project = $fields['Project']['name'];
        $this->originalEstimate = $fields['Original Estimate'];
        $this->remainingEstimate = $fields['Remaining Estimate'];
        $this->releaseDate = (count($fields['Fix Version/s'])==1 && array_key_exists('releaseDate',$fields['Fix Version/s'][0]))?
            $fields['Fix Version/s'][0]['releaseDate']:null;
        $this->labels = implode(',',$fields['Labels']);
        $this->assignee = $fields['Assignee']['displayName'];
        $this->assigneeKey = $fields['Assignee']['key'];
        $this->releaseSummary = $fields['Release Summary'];
        $this->issueStatus = $status['name'];
        $this->requestor = $fields['Requestor']['value'];
        $this->epicName = (array_key_exists('Epic Name',$fields)?$fields['Epic Name']:null);
        $this->epicLink = (array_key_exists('Epic Link',$fields)?$fields['Epic Link']:null);
        $this->epicColour = (array_key_exists('Epic Colour',$fields)?$fields['Epic Colour']:null);
    }

    public function getIssueKey() { return $this->issueKey;}
    public function getSummary() { return $this->summary;}
    public function getPriority() { return $this->priority;}
    public function getIssueType() { return $this->issueType;}
    public function getProject() { return $this->project;}
    public function getOriginalEstimate() { return $this->originalEstimate;}
    public function getRemainingEstimate() { return $this->remainingEstimate;}
    public function getReleaseDate() { return $this->releaseDate;}
    public function getLabels() { return $this->labels;}
    public function getAssignee() { return $this->assignee;}
    public function getAssigneeKey() { return $this->assigneeKey;}
    public function getReleaseSummary(){ return $this->releaseSummary;}
    public function getIssueStatus() { return $this->issueStatus;}
    public function getRequestor() { return $this->requestor;}
    public function getEpicName() { return $this->epicName;}
    public function getEpicLink() { return $this->epicLink;}
    public function getEpicColour() { return $this->$this->epicColour;}

    public function toArray()
    {
        $params = get_object_vars($this);
        return convertCamelCaseKeys2camel_case($params);
    }
}