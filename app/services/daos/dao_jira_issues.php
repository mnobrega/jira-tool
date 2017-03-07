<?php
/**
 * Created by PhpStorm.
 * User: mnobrega
 * Date: 21-01-2017
 * Time: 11:07
 */

require_once (ROOT_DIR.'app/common/pdo_singleton.php');

class DAOJIRAIssues extends PDOSingleton
{
    const TABLENAME_JIRA_ISSUES = 'jira_issues';
    const TABLENAME_JIRA_ISSUES_HISTORIES = 'jira_issues_histories';

    const HISTORY_ITEM_FIELD_STATUS = 'status';

    const STATUS_RAW_REQUEST = 'Raw Request';
    const STATUS_ANALYSING = 'Analysing';
    const STATUS_ANALYSED = 'Analysed';
    const STATUS_TO_DEVELOP = 'To Develop';
    const STATUS_TO_QUALITY = 'To Quality';
    const STATUS_DEV_IN_PROGRESS = 'Dev In Progress';
    const STATUS_QA_IN_PROGRESS = 'QA In Progress';
    const STATUS_DEV_DONE = 'Dev Done';
    const STATUS_QA_DONE = 'QA Done';
    const STATUS_READY_TO_DEPLOY = 'Ready to Deploy';

    const TYPE_EPIC = 'Epic';
    const TYPE_STORY = 'Story';
    const TYPE_TASK = 'Task';
    const TYPE_BUG = 'Bug';
    const TYPE_SUB_TASK = 'Sub-task';
    const TYPE_IMPROVEMENT = 'Improvement';
    const TYPE_SPIKE = 'Spike';

    const DAO_PROJECT_MOBILITY = 'MOB';
    const DAO_PROJECT_MARKET = 'APK';

    public function __construct()
    {
        parent::__construct();
    }

    /** JIRAIssueTblTuples */
    /**
     * @param $statuses array
     * @param $types array
     * @return JIRAIssueTblTuple []
     */
    public function searchJIRAIssues(Array $statuses=null, Array $types=null)
    {
        $query = "SELECT ji.*
                    FROM ".self::TABLENAME_JIRA_ISSUES." ji
                    WHERE ji.id=ji.id
                        ".(!is_null($statuses)?" AND ji.issue_status IN ".$this->inArray($statuses):"")."
                        ".(!is_null($types)?" AND ji.issue_type IN ".$this->inArray($types):"")."
                    ORDER BY ji.priority_detail ASC";
        return $this->getObjArray($this->query($query),"JIRAIssueTblTuple");
    }
    public function searchJIRAIssuesByPMProjectName($PMProjectName, $orderBy=null)
    {
        $query = "SELECT * FROM
                    (SELECT ji.*
                        FROM ".self::TABLENAME_JIRA_ISSUES." ji
                        WHERE ji.pm_project_name='".$PMProjectName."') AS x
                  ORDER BY ".(!is_null($orderBy)?$orderBy:"priority_detail ASC");

        return $this->getObjArray($this->query($query),"JIRAIssueTblTuple");
    }
    private function getJIRAIssues(Array $issueKeys=null)
    {
        $query = "SELECT ji.*
                    FROM ".self::TABLENAME_JIRA_ISSUES." ji
                    WHERE ji.id=ji.id".
                        (!is_null($issueKeys)?" AND ji.issue_key IN ".$this->inArray($issueKeys):"");

        return $this->query($query);
    }
    public function getJIRAIssueByKey($issueKey)
    {
        return $this->getObj($this->getJIRAIssues(array($issueKey)),"JIRAIssueTblTuple",false);
    }
    /**
     * @param JIRAIssueTblTuple $tuple
     */
    public function insertJIRAIssue(JIRAIssueTblTuple $tuple)
    {
        $query = "INSERT INTO ".self::TABLENAME_JIRA_ISSUES." (issue_key, issue_status, summary, release_summary,
            priority, issue_type, project, original_estimate, remaining_estimate, release_date, labels, assignee,
            assignee_key, emp_it_requestor, epic_name, epic_link, epic_colour, priority_detail, project_key,
            short_summary, emp_customer, pm_project_manager, request_date, estimated_start_date,
            estimated_end_date, due_date,pm_project_name)
            VALUES (
                '".$tuple->getIssueKey()."',
                '".$tuple->getIssueStatus()."',
                '".$tuple->getSummary()."',
                '".$tuple->getReleaseSummary()."',
                '".$tuple->getPriority()."',
                '".$tuple->getIssueType()."',
                '".$tuple->getProject()."',
                ".(!is_null($tuple->getOriginalEstimate())?$tuple->getOriginalEstimate():"NULL").",
                ".(!is_null($tuple->getRemainingEstimate())?$tuple->getRemainingEstimate():"NULL").",
                ".(!is_null($tuple->getReleaseDate())?"'".$tuple->getReleaseDate()."'":"NULL").",
                '".$tuple->getLabels()."',
                '".$tuple->getAssignee()."',
                '".$tuple->getAssigneeKey()."',
                ".(!is_null($tuple->getEMPITRequestor())?"'".$tuple->getEMPITRequestor()."'":"NULL").",
                ".(!is_null($tuple->getEpicName())?"'".$tuple->getEpicName()."'":"NULL").",
                ".(!is_null($tuple->getEpicLink())?"'".$tuple->getEpicLink()."'":"NULL").",
                ".(!is_null($tuple->getEpicColour())?"'".$tuple->getEpicColour()."'":"NULL").",
                '".$tuple->getPriorityDetail()."',
                '".$tuple->getProjectKey()."',
                '".$tuple->getShortSummary()."',
                '".$tuple->getEMPCustomer()."',
                '".$tuple->getPMProjectManager()."',
                ".(!is_null($tuple->getRequestDate())?"'".$tuple->getRequestDate()."'":"NULL").",
                ".(!is_null($tuple->getEstimatedStartDate())?"'".$tuple->getEstimatedStartDate()."'":"NULL").",
                ".(!is_null($tuple->getEstimatedEndDate())?"'".$tuple->getEstimatedEndDate()."'":"NULL").",
                ".(!is_null($tuple->getDueDate())?"'".$tuple->getDueDate()."'":"NULL").",
                '".$tuple->getPMProjectName()."')";

        $this->query($query);
    }
    private function updateJIRAIssue($issueKey, $priorityDetail=null, $originalEstimate=null,
                                     $estimatedStartDate=null, $estimatedEndDate=null)
    {
        $query = "UPDATE ".self::TABLENAME_JIRA_ISSUES." SET
                        issue_key = issue_key
                        ".(!is_null($priorityDetail)?" ,priority_detail='".$priorityDetail."'":"")."
                        ".(!is_null($originalEstimate)?" ,original_estimate='".$originalEstimate."'":"")."
                        ".(!is_null($estimatedStartDate)?" ,estimated_start_date='".$estimatedStartDate."'":"")."
                        ".(!is_null($estimatedEndDate)?" ,estimated_end_date='".$estimatedEndDate."'":"")."
                    WHERE issue_key='".$issueKey."';";
        $this->query($query);
    }
    public function updateJIRAIssuePriorityDetail($issueKey, $priorityDetail)
    {
        $this->updateJIRAIssue($issueKey,$priorityDetail,null);
    }
    public function updateJIRAIssueOriginalEstimate($issueKey, $originalEstimate)
    {
        $this->updateJIRAIssue($issueKey,null,$originalEstimate);
    }
    public function updateJIRAIssueDateEstimates($issueKey, $PMEstimatedStartDate, $PMEstimatedEndDate)
    {
        $this->updateJIRAIssue($issueKey,null,null,$PMEstimatedStartDate, $PMEstimatedEndDate);
    }
    public function deleteAllJIRAIssues()
    {
        $query = "DELETE FROM ".self::TABLENAME_JIRA_ISSUES;
        $this->query($query);
    }

    /** JIRAIssueHistoryTblTuples */
    /**
     * @param $issueKey
     * @param array|null $fields
     * @param array|null $fromStrings
     * @param array|null $toStrings
     * @return JIRAIssueHistoryTblTuple []
     */
    public function searchJIRAIssueHistories($issueKey, Array $fields=null, Array $fromStrings=null, Array $toStrings=null)
    {
        $query = "SELECT *
                    FROM ".self::TABLENAME_JIRA_ISSUES_HISTORIES." jih
                    WHERE jih.issue_key='".$issueKey."'
                        ".(!is_null($fields)?" AND jih.field IN ".$this->inArray($fields):"")."
                        ".(!is_null($fromStrings) && is_null($toStrings)?" AND jih.from_string IN ".$this->inArray($fromStrings):"")."
                        ".(!is_null($toStrings) &&  is_null($fromStrings)?" AND jih.to_string IN ".$this->inArray($toStrings):"")."
                        ".(!is_null($fromStrings) && !is_null($toStrings)?" AND (jih.from_string IN ".$this->inArray($fromStrings)." OR jih.to_string IN ".$this->inArray($toStrings).")":"")."
                    ORDER BY jih.history_datetime ASC;";

        return $this->getObjArray($this->query($query),"JIRAIssueHistoryTblTuple");
    }
    /**
     * @param JIRAIssueHistoryTblTuple $tuple
     */
    public function insertJIRAIssueHistory(JIRAIssueHistoryTblTuple $tuple)
    {
        $query = "INSERT INTO ".self::TABLENAME_JIRA_ISSUES_HISTORIES." (issue_key, history_datetime, field,
            from_string, to_string) VALUES (
                '".$tuple->getIssueKey()."',
                '".$tuple->getHistoryDatetime()."',
                '".$tuple->getField()."',
                '".$tuple->getFromString()."',
                '".$tuple->getToString()."')";
        $this->query($query);
    }
    public function deleteAllJIRAIssuesHistories()
    {
        $query = "DELETE FROM ".self::TABLENAME_JIRA_ISSUES_HISTORIES;
        $this->query($query);
    }

    /** JIRAIssueTblTupleExtended */
    public function searchJIRAIssuesWhere($where=null, Array $statuses=null)
    {
        $query = "SELECT * FROM
                    (SELECT ji.*,
                          IFNULL(epic.original_estimate,0) AS epic_original_estimate,
                          IFNULL(epic.short_summary,'') AS epic_short_summary
                        FROM ".self::TABLENAME_JIRA_ISSUES." ji
                            JOIN ".self::TABLENAME_JIRA_ISSUES." epic ON epic.issue_key=ji.epic_link
                        WHERE ji.id = ji.id
                            ".(!is_null($statuses)?" AND ji.issue_status IN ".$this->inArray($statuses):"").") AS x
                    WHERE x.id=x.id
                        AND ".$where."
                    ORDER BY x.priority ASC;";
        return $this->getObjArray($this->query($query),"JIRAIssueTblTupleExtended");
    }
}

class JIRAIssueTblTuple
{
    private $issueKey;
    private $issueStatus;
    private $summary;
    private $priority;
    private $issueType;
    private $project;
    private $projectKey;
    private $originalEstimate;
    private $remainingEstimate;
    private $releaseDate;
    private $dueDate;
    private $labels;
    private $assignee;
    private $assigneeKey;
    private $epicName;
    private $epicLink;
    private $epicColour;

    private $priorityDetail;
    private $releaseSummary;
    private $shortSummary;
    private $EMPITRequestor;
    private $EMPCustomer;
    private $PMProjectManager;
    private $requestDate;
    private $estimatedStartDate;
    private $estimatedEndDate;
    private $PMProjectName;

    public function __construct($row)
    {
        $this->issueKey = $row['issue_key'];
        $this->issueStatus = $row['issue_status'];
        $this->summary = $row['summary'];
        $this->priority = $row['priority'];
        $this->issueType = $row['issue_type'];
        $this->project = $row['project'];
        $this->projectKey = $row['project_key'];
        $this->originalEstimate = $row['original_estimate'];
        $this->remainingEstimate = $row['remaining_estimate'];
        $this->releaseDate = $row['release_date'];
        $this->dueDate = $row['due_date'];
        $this->labels = $row['labels'];
        $this->assignee = $row['assignee'];
        $this->assigneeKey = $row['assignee_key'];
        $this->epicName = $row['epic_name'];
        $this->epicLink = $row['epic_link'];
        $this->epicColour = $row['epic_colour'];

        $this->priorityDetail = $row['priority_detail'];
        $this->releaseSummary = $row['release_summary'];
        $this->shortSummary = $row['short_summary'];
        $this->EMPITRequestor = $row['emp_it_requestor'];
        $this->EMPCustomer = $row['emp_customer'];
        $this->PMProjectManager = $row['pm_project_manager'];
        $this->requestDate = $row['request_date'];
        $this->estimatedStartDate = $row['estimated_start_date'];
        $this->estimatedEndDate = $row['estimated_end_date'];
        $this->PMProjectName = $row['pm_project_name'];
    }

    public function getIssueKey() { return $this->issueKey;}
    public function getIssueStatus(){ return $this->issueStatus;}
    public function getSummary() { return $this->summary;}
    public function getPriority() { return $this->priority;}
    public function getIssueType() { return $this->issueType;}
    public function getProject() { return $this->project;}
    public function getProjectKey() { return $this->projectKey;}
    public function getOriginalEstimate() { return $this->originalEstimate;}
    public function getRemainingEstimate() { return $this->remainingEstimate;}
    public function getReleaseDate() { return $this->releaseDate;}
    public function getDueDate() {return $this->dueDate;}
    public function getLabels() { return $this->labels;}
    public function getAssignee() { return $this->assignee;}
    public function getAssigneeKey() { return $this->assigneeKey;}
    public function getEpicName() { return $this->epicName;}
    public function getEpicLink() { return $this->epicLink;}
    public function getEpicColour() { return $this->epicColour;}

    public function getPriorityDetail() {return $this->priorityDetail;}
    public function getReleaseSummary() { return $this->releaseSummary;}
    public function getShortSummary() { return $this->shortSummary;}
    public function getEMPITRequestor() { return $this->EMPITRequestor;}
    public function getEMPCustomer() { return $this->EMPCustomer;}
    public function getPMProjectManager() { return $this->PMProjectManager;}
    public function getRequestDate() { return $this->requestDate;}
    public function getEstimatedStartDate() { return $this->estimatedStartDate;}
    public function getEstimatedEndDate() {return $this->estimatedEndDate;}
    public function getPMProjectName() { return $this->PMProjectName;}
}

class JIRAIssueTblTupleExtended extends JIRAIssueTblTuple
{
    private $epicOriginalEstimate;
    private $epicShortSummary;

    public function __construct($row)
    {
        parent::__construct($row);

        $this->epicOriginalEstimate = $row['epic_original_estimate'];
        $this->epicShortSummary = $row['epic_short_summary'];
    }

    public function getEpicOriginalEstimate() { return $this->epicOriginalEstimate;}
    public function getEpicShortSummary() { return $this->epicShortSummary;}
}

class JIRAIssueHistoryTblTuple
{
    private $issueKey;
    private $historyDatetime;
    private $field;
    private $fromString;
    private $toString;

    public function __construct($row)
    {
        $this->issueKey = $row['issue_key'];
        $this->historyDatetime = $row['history_datetime'];
        $this->field = $row['field'];
        $this->fromString = $row['from_string'];
        $this->toString = $row['to_string'];
    }

    public function getIssueKey() { return $this->issueKey;}
    public function getHistoryDatetime() {return $this->historyDatetime;}
    public function getField() { return $this->field;}
    public function getFromString() {return $this->fromString;}
    public function getToString() { return $this->toString;}
}