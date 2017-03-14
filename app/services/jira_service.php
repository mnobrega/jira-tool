<?php

require ROOT_DIR.'vendor/autoload.php';
require ROOT_DIR.'app/common/converters.php';
require ROOT_DIR.'app/common/time.php';

require ROOT_DIR . 'app/services/daos/dao_jira.php';

class JIRAServiceException extends Exception {};

class JIRAService
{
    const HISTORY_ITEM_TYPE_STATUS = 'status';

    const RESOURCE_TYPE_DEV = 'dev';
    const RESOURCE_TYPE_QA = 'qa';

    const WORKING_DAY_HOURS = 8;
    const DEFAULT_GANTT_ISSUE_COLOR = '#cccccc';
    const GANTT_ISSUE_COLOR_DELAYED = '#ff0000';

    const DELAY_DAYS_THRESHOLD = -1; //days
    const ISSUE_TOLERANCE_PERCENTAGE = 10; //%

    const EMPARK_PROYECTO_THRESHOLD = 288000; //2 weeks

    static $projects = array (
        "APK"=>"eos Market",
        "MOB"=>"eos Mobility"
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

    private $daoJIRAIssues;

    function __construct()
    {
        $this->api = new \Jira_Api(JIRA_URL, new \Jira_Api_Authentication_Basic(JIRA_USERNAME, JIRA_PASSWORD));
        $this->walker = new Jira_Issues_Walker($this->api);

        $this->daoJIRAIssues = new DAOJIRAIssues();
    }

    /*** JIRA CLIENT ***/
    /**
     * @return Array JIRAVersion []
     */
    public function getProjectsVersions(Array $JIRAProjectKeys)
    {
        $JIRAVersions = array();
        foreach ($JIRAProjectKeys as $JIRAProjectKey) {
            $JIRAVersions[$JIRAProjectKey] = array();
            $versions = $this->api->getVersions($JIRAProjectKey);
            if (is_array($versions))
            {
                foreach ($versions as $version) {
                    $JIRAVersions[$JIRAProjectKey][] = new JIRAVersion($version);
                }
            }

        }
        return $JIRAVersions;
    }
    /**
     * @param array $status
     * @return JIRAIssue []
     */
    public function getIssuesByStatuses(Array $status)
    {
        $issues = array();
        $statusString = '"' . implode('","', $status) . '"';

        $this->walker->push('status IN (' . $statusString . ') ORDER BY priority ASC');
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
    /**
     * @param $issues JIRAIssue []
     * @return Array
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
    /**
     * @return JIRAIssue []
     */
    public function getPreviousWeekCreatedIssues($hasEMPITRequestor=null)
    {
        $issues = array();
        $this->walker->push('created > startOfWeek(-1w) AND created< startOfWeek()
            AND type NOT IN ("'.DAOJIRAIssues::TYPE_EPIC.'")
            '.(!is_null($hasEMPITRequestor)?'AND "EMP IT Requestor" IS '.($hasEMPITRequestor?"NOT":"").' EMPTY':'').'
            ORDER BY "EMP IT Requestor" ASC');
        foreach ($this->walker as $issue) {
            $issues[] = new JIRAIssue($issue);
        }
        return $issues;
    }
    /**
     * @return JIRAIssue []
     */
    public function getPreviousWeekStartedIssues($hasEMPITRequestor=null)
    {
        $issues = array();
        $this->walker->push('((status changed from "'.DAOJIRAIssues::STATUS_TO_DEVELOP.'" TO
                "'.DAOJIRAIssues::STATUS_DEV_IN_PROGRESS.'" after -1w) OR
            (status changed from "'.DAOJIRAIssues::STATUS_TO_QUALITY.'" TO
                 "'.DAOJIRAIssues::STATUS_QA_IN_PROGRESS.'" after -1w))
            '.(!is_null($hasEMPITRequestor)?'AND "EMP IT Requestor" IS '.($hasEMPITRequestor?"NOT":"").' EMPTY':'').'
            AND status NOT IN ("'.DAOJIRAIssues::STATUS_QA_DONE.'",
                "'.DAOJIRAIssues::STATUS_DEV_DONE.'","'.DAOJIRAIssues::STATUS_READY_TO_DEPLOY.'")');
        foreach ($this->walker as $issue) {
            $issues[] = new JIRAIssue($issue);
        }
        return $issues;
    }
    /**
     * @return JIRAIssue []
     */
    public function getPreviousWeekFinishedIssues($hasEMPITRequestor=null)
    {
        $issues = array();
        $this->walker->push('((status changed from "'.DAOJIRAIssues::STATUS_DEV_IN_PROGRESS.'" TO
                "'.DAOJIRAIssues::STATUS_DEV_DONE.'" after -1w) OR
            (status changed from "'.DAOJIRAIssues::STATUS_QA_IN_PROGRESS.'" TO
                "'.DAOJIRAIssues::STATUS_QA_DONE.'" after -1w))
            '.(!is_null($hasEMPITRequestor)?'AND "EMP IT Requestor" IS '.($hasEMPITRequestor?"NOT":"").' EMPTY':'').'
            AND status NOT IN ("'.DAOJIRAIssues::STATUS_TO_DEVELOP.'","'.DAOJIRAIssues::STATUS_TO_QUALITY.'",
                "'.DAOJIRAIssues::STATUS_QA_IN_PROGRESS.'","'.DAOJIRAIssues::STATUS_DEV_IN_PROGRESS.'")
            AND type NOT IN ("'.DAOJIRAIssues::TYPE_EPIC.'")');
        foreach ($this->walker as $issue) {
            $issues[] = new JIRAIssue($issue);
        }
        return $issues;
    }


    /*** JIRA PERSISTED ***/
    public function deleteAllPersistedIssues()
    {
        $this->daoJIRAIssues->deleteAllJIRAIssues();
    }
    /**
     * @param $issues JIRAIssue []
     */
    public function persistIssues(Array $issues)
    {
        $this->daoJIRAIssues->deleteAllJIRAIssues();
        $epics = array();
        foreach ($issues as $issue)
        {
            $this->daoJIRAIssues->insertJIRAIssue(new JIRAIssueTblTuple($issue->toArray()));
            if (is_null($issue->getEpicLink())) {
                continue;
            }
            if (!array_key_exists($issue->getEpicLink(),$epics)) {
                $epics[$issue->getEpicLink()] = 0;
            }
            $epics[$issue->getEpicLink()] += $issue->getOriginalEstimate();
        }

        foreach ($epics as $epicIssueKey=>$originalEstimate) {
            $this->daoJIRAIssues->updateJIRAIssueOriginalEstimate($epicIssueKey,$originalEstimate);
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
     * @param $where
     * @return JIRAIssueTblTuple []
     */
    public function getPersistedIssuesWhere($where, Array $statuses=null)
    {
        return $this->daoJIRAIssues->searchJIRAIssuesWhere($where, $statuses);
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
     * @param $projectVersions []
     */
    public function persistVersions(Array $projectsVersions)
    {
        $this->daoJIRAIssues->deleteAllJIRAVersions();
        foreach ($projectsVersions as $projectKey=>$projectVersions) {
            foreach ($projectVersions as $version) {
                /**@var $version JIRAVersion*/
                $versionArray = $version->toArray();
                $versionArray['project_key'] = $projectKey;
                $versionArray['version_id'] = $version->getId();
                $this->daoJIRAIssues->insertJIRAVersion(new JIRAVersionTblTuple($versionArray));
            }
        }
    }
    /**
     * @param $issues JIRAIssue []
     * @return Array
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

            $issuesTimeSpent[$issue->getIssueKey()] = getWorkingHours($timeIntervals);
        }

        return $issuesTimeSpent;
    }


    /**** SPECIFIC METHODS *****/
    /**** SPECIFIC METHODS *****/
    /**** SPECIFIC METHODS *****/
    /**** SPECIFIC METHODS *****/
    public function getPersistedIssuesByPMProjectName($PMProjectName, $orderBy=null)
    {
        return $this->daoJIRAIssues->searchJIRAIssuesByPMProjectName($PMProjectName,$orderBy);
    }
    public function getEmparkIssuesData($whereSQL)
    {
        return $this->daoJIRAIssues->searchJIRAIssuesWhere($whereSQL);
    }
    /**
     * @param ProjectName $PMProjectName
     * @param float $workingDayHours
     */
    public function updatePMProjectsEstimatedDates(ProjectName $PMProjectName, $workingDayHours)
    {
        $inProgressIssueStatuses = array(
            DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::STATUS_QA_IN_PROGRESS
        );

        $fields = array(DAOJIRAIssues::HISTORY_ITEM_FIELD_STATUS);
        $fromStrings = array(DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::STATUS_QA_IN_PROGRESS);
        $toStrings = array(DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::STATUS_QA_IN_PROGRESS);

        $projectIssues = $this->getPersistedIssuesByPMProjectName($PMProjectName->getName(),null);
        $JIRAIssuesTimeSpent = $this->getPersistedIssuesTimeSpent($projectIssues);

        $JIRAIssuesOrderedByProgress = array();
        foreach ($projectIssues as $issue) {
            /**@var $issue JIRAIssueTblTuple */
            if (in_array($issue->getIssueStatus(),$inProgressIssueStatuses)) {
                array_unshift($JIRAIssuesOrderedByProgress,$issue);
            } else {
                $JIRAIssuesOrderedByProgress[] = $issue;
            }
        }

        $currentStart = null;
        $now = new DateTime();
        $excludedIssueStatuses = array (
            DAOJIRAIssues::STATUS_QA_DONE,
            DAOJIRAIssues::STATUS_DEV_DONE,
            DAOJIRAIssues::STATUS_READY_TO_DEPLOY
        );
        foreach ($JIRAIssuesOrderedByProgress as $issue) {
            /**@var $issue JIRAIssueTblTuple */
            $workingDaysLeft = max(0.00,ceil(($issue->getOriginalEstimate()/3600 -
                    $JIRAIssuesTimeSpent[$issue->getIssueKey()])/$workingDayHours));

            if ($workingDaysLeft>0 && !in_array($issue->getIssueStatus(),$excludedIssueStatuses)) {
                $start = (is_null($currentStart)?$now->format("Y-m-d 00:00:00"):$currentStart->format("Y-m-d H:i:s"));
                $endDate = getEndDateFromWorkingHours(new DateTime($start),
                    $workingDaysLeft*(1+self::ISSUE_TOLERANCE_PERCENTAGE/100));
                $endDate->modify("-1 seconds");
                $end = $endDate->format("Y-m-d H:i:s");

                $this->editIssueDateEstimates($issue->getIssueKey(),$start,$end);

                $currentStart = new DateTime($end);
                $currentStart->modify("+1 second");
            } else {
                $this->editIssueDateEstimates($issue->getIssueKey(),null,null);
            }
        }

        if (count($JIRAIssuesOrderedByProgress))
        {
            $issueHistories = $this->daoJIRAIssues->searchJIRAIssueHistories($JIRAIssuesOrderedByProgress[0]->getIssueKey(),$fields,
                $fromStrings,$toStrings);
            if (count($issueHistories)) {
                $issue = $this->daoJIRAIssues->getJIRAIssueByKey($JIRAIssuesOrderedByProgress[0]->getIssueKey());
                $this->editIssueDateEstimates($JIRAIssuesOrderedByProgress[0]->getIssueKey(),
                    $issueHistories[0]->getHistoryDatetime(),$issue->getEstimatedEndDate());
            }
        }

    }
    /**
     * @param JIRAIssueTblTuple []
     * @param $resourceName
     * @param $workingDayHours
     * @return array
     * @throws Exception
     */
    public function getTeamRoadmapData(Array $JIRAIssues, $resourceName, $workingHours)
    {
        $excludedIssueStatuses = array (
            DAOJIRAIssues::STATUS_QA_DONE,
            DAOJIRAIssues::STATUS_DEV_DONE,
            DAOJIRAIssues::STATUS_READY_TO_DEPLOY
        );

        $epicIssuesMap = array();
        $epicIssues = $this->getPersistedIssues(null,array(DAOJIRAIssues::TYPE_EPIC));
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
        $currentStart = null;
        $JIRAIssuesTimeSpent = $this->getPersistedIssuesTimeSpent($JIRAIssues);
        foreach ($JIRAIssues as $issue) {
            /**@var $issue JIRAIssueTblTuple */
            $workingDaysLeft = max(0.00,ceil(($issue->getOriginalEstimate()/3600 -
                    $JIRAIssuesTimeSpent[$issue->getIssueKey()])/$workingHours));

            if ($workingDaysLeft>0 && !in_array($issue->getIssueStatus(),$excludedIssueStatuses)) {
                $row = array();
                $row['resource'] = $resourceName;
                $row['issueKey'] = $issue->getIssueKey();
                $row['priority'] = $issue->getPriority();
                $row['status'] = $issue->getIssueStatus();
                $row['workingDaysLeft'] = $workingDaysLeft;
                $row['label'] = $issue->getIssueKey()." ".$issue->getSummary();
                $row['epicColor'] = (!is_null($issue->getEpicLink())?$epicIssuesMap[$issue->getEpicLink()]['color']:
                    self::DEFAULT_GANTT_ISSUE_COLOR);
                $row['epicName'] = (!is_null($issue->getEpicLink())?$epicIssuesMap[$issue->getEpicLink()]['name']:"Outros");
                $row['start'] = $issue->getEstimatedStartDate();
                $row['end'] = $issue->getEstimatedEndDate();

                $JIRAGanttIssues[] = new JIRAGanttIssue($row);
            } else {
                //ignore issue
            }
        }

        return $JIRAGanttIssues;
    }
    public function editIssuePriorityDetail($issueKey, $priorityDetail)
    {
        $params = array(
            'notifyUsers'=>false,
            'fields'=>array(
                "customfield_10916" => floatval($priorityDetail)
            )
        );
        $this->api->editIssue($issueKey,$params);
        $this->daoJIRAIssues->updateJIRAIssuePriorityDetail($issueKey,$priorityDetail);
    }
    public function editIssueDateEstimates($issueKey, $estimatedStartDate, $estimatedEndDate)
    {
        $start = !is_null($estimatedStartDate)?(new DateTime($estimatedStartDate, new DateTimeZone(INSTANCE_TIMEZONE))):null;
        $end = !is_null($estimatedEndDate)?(new DateTime($estimatedEndDate,new DateTimeZone(INSTANCE_TIMEZONE))):null;

        $params = array (
            'notifyUsers'=>false,
            'fields'=>array(
                "customfield_10927" => !is_null($start)?$start->format("Y-m-d\TH:i:s.0TZ"):null,
                "customfield_10928" => !is_null($end)?$end->format("Y-m-d\TH:i:s.0TZ"):null,
            )
        );
        $this->api->editIssue($issueKey,$params);
        $this->daoJIRAIssues->updateJIRAIssueDateEstimates($issueKey,$estimatedStartDate,$estimatedEndDate);
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

    public function setStart($start)
    {
        $this->start = $start;
    }
}

class JIRAVersion
{
    private $id;
    private $name;
    private $released;
    private $releaseDate;

    public function __construct($row)
    {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->released = $row['released'];
        $this->releaseDate = $row['releaseDate'];
    }

    public function getId(){ return $this->id;}
    public function getName() { return $this->name;}
    public function getReleased() { return $this->released;}
    public function getReleaseDate(){ return $this->releaseDate;}

    public function toArray()
    {
        $params = get_object_vars($this);
        return convertCamelCaseKeys2camel_case($params);
    }
}

class JIRAIssue
{
    private $issueKey;
    private $summary;
    private $priority;
    private $issueType;
    private $project;
    private $projectKey;
    private $originalEstimate;
    private $remainingEstimate;
    private $fixVersionId;
    private $releaseDate;
    private $dueDate;
    private $labels;
    private $assignee;
    private $assigneeKey;
    private $issueStatus;
    private $epicName;
    private $epicLink;
    private $epicColour;

    private $priorityDetail;
    private $releaseSummary;
    private $shortSummary;
    private $EmpITRequestor;
    private $EmpCustomer;
    private $PMProjectManager;
    private $requestDate;
    private $estimatedStartDate;
    private $estimatedEndDate;
    private $PMProjectName;

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
        $this->projectKey = $fields['Project']['key'];
        $this->originalEstimate = $fields['Original Estimate'];
        $this->remainingEstimate = $fields['Remaining Estimate'];
        $this->fixVersionId = (count($fields['Fix Version/s'])==1 && array_key_exists('releaseDate',$fields['Fix Version/s'][0]))?
            $fields['Fix Version/s'][0]['id']:null;
        $this->releaseDate = (count($fields['Fix Version/s'])==1 && array_key_exists('releaseDate',$fields['Fix Version/s'][0]))?
            $fields['Fix Version/s'][0]['releaseDate']:null;
        $this->dueDate = $fields['Due Date'];
        $this->labels = implode(',',$fields['Labels']);
        $this->assignee = $fields['Assignee']['displayName'];
        $this->assigneeKey = $fields['Assignee']['key'];
        $this->issueStatus = $status['name'];
        $this->epicName = (array_key_exists('Epic Name',$fields)?$fields['Epic Name']:null);
        $this->epicLink = (array_key_exists('Epic Link',$fields)?$fields['Epic Link']:null);
        $this->epicColour = (array_key_exists('Epic Colour',$fields)?$fields['Epic Colour']:null);

        $this->priorityDetail = (!is_null($fields['Priority Detail'])?$fields['Priority Detail']:$priority['id']);
        $this->releaseSummary = $fields['Release Summary (ES)'];
        $this->shortSummary = $fields['Short Summary (ES)'];
        $this->EmpITRequestor = $fields['EMP IT Requestor']['value'];
        $this->EmpCustomer = $fields['EMP Customer']['value'];
        $this->PMProjectManager = $fields['PM Project Manager']['value'];
        $this->requestDate = $fields['Request Date'];
        $this->estimatedStartDate = null;
        $this->estimatedEndDate = null;
        $this->PMProjectName = $fields['PM Project Name']['value'];
    }

    public function getIssueKey() { return $this->issueKey;}
    public function getSummary() { return $this->summary;}
    public function getPriority() { return $this->priority;}
    public function getIssueType() { return $this->issueType;}
    public function getProject() { return $this->project;}
    public function getProjectKey() { return $this->projectKey;}
    public function getOriginalEstimate() { return $this->originalEstimate;}
    public function getRemainingEstimate() { return $this->remainingEstimate;}
    public function getFixVersionId() {return $this->fixVersionId;}
    public function getReleaseDate() { return $this->releaseDate;}
    public function getDueDate() { return $this->dueDate;}
    public function getLabels() { return $this->labels;}
    public function getAssignee() { return $this->assignee;}
    public function getAssigneeKey() { return $this->assigneeKey;}
    public function getIssueStatus() { return $this->issueStatus;}
    public function getEpicName() { return $this->epicName;}
    public function getEpicLink() { return $this->epicLink;}
    public function getEpicColour() { return $this->$this->epicColour;}

    public function getPriorityDetail() { return $this->priorityDetail;}
    public function getReleaseSummary(){ return $this->releaseSummary;}
    public function getShortSummary() { return $this->shortSummary;}
    public function getEmpITRequestor() { return $this->EmpITRequestor;}
    public function getEmpCustomer() { return $this->EmpCustomer;}
    public function getPMProjectManager() { return $this->PMProjectManager;}
    public function getRequestDate() { return $this->requestDate;}
    public function getEstimatedStartDate() { return $this->estimatedStartDate;}
    public function getEstimatedEndDate() { return $this->estimatedEndDate;}
    public function getPMProjectName() { return $this->PMProjectName;}

    public function toArray()
    {
        $params = get_object_vars($this);
        return convertCamelCaseKeys2camel_case($params);
    }
}