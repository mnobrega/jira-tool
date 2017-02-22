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
    const TYPE_PROJECT_EMPARK = 'Project-EMP';
    const TYPE_PROJECT_PM = 'Project-PM';

    public function __construct()
    {
        parent::__construct();
    }

    public function searchJIRAIssuesWhere($where=null, Array $statuses=null, Array $types=null)
    {
        $query = "SELECT ji.*
                    FROM ".self::TABLENAME_JIRA_ISSUES." ji
                    WHERE ji.id = ji.id
                        AND ".$where."
                        ".(!is_null($statuses)?" AND ji.issue_status IN ".$this->inArray($statuses):"")."
                        ".(!is_null($types)?" AND ji.issue_type IN ".$this->inArray($types):"")."
                    ORDER BY ji.priority ASC";
        return $this->getObjArray($this->query($query),"JIRAIssueTblTuple");
    }

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
                    ORDER BY ji.priority ASC";
        return $this->getObjArray($this->query($query),"JIRAIssueTblTuple");
    }

    /**
     * @param JIRAIssueTblTuple $tuple
     */
    public function insertJIRAIssue(JIRAIssueTblTuple $tuple)
    {
        $query = "INSERT INTO ".self::TABLENAME_JIRA_ISSUES." (issue_key, issue_status, summary, release_summary,
            priority, issue_type, project, original_estimate, remaining_estimate, release_date, labels, assignee,
            assignee_key, requestor, epic_name, epic_link, epic_colour, priority_detail, project_key)
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
                ".(!is_null($tuple->getRequestor())?"'".$tuple->getRequestor()."'":"NULL").",
                ".(!is_null($tuple->getEpicName())?"'".$tuple->getEpicName()."'":"NULL").",
                ".(!is_null($tuple->getEpicLink())?"'".$tuple->getEpicLink()."'":"NULL").",
                ".(!is_null($tuple->getEpicColour())?"'".$tuple->getEpicColour()."'":"NULL").",
                '".$tuple->getPriorityDetail()."',
                '".$tuple->getProjectKey()."')";

        $this->query($query);
    }

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

    public function deleteAllJIRAIssues()
    {
        $query = "DELETE FROM ".self::TABLENAME_JIRA_ISSUES;
        $this->query($query);
    }

    public function deleteAllJIRAIssuesHistories()
    {
        $query = "DELETE FROM ".self::TABLENAME_JIRA_ISSUES_HISTORIES;
        $this->query($query);
    }
}

class JIRAIssueTblTuple
{
    private $issueKey;
    private $issueStatus;
    private $summary;
    private $releaseSummary;
    private $priority;
    private $priorityDetail;
    private $issueType;
    private $project;
    private $projectKey;
    private $originalEstimate;
    private $remainingEstimate;
    private $releaseDate;
    private $labels;
    private $assignee;
    private $assigneeKey;
    private $requestor;
    private $epicName;
    private $epicLink;
    private $epicColour;

    public function __construct($row)
    {
        $this->issueKey = $row['issue_key'];
        $this->issueStatus = $row['issue_status'];
        $this->summary = $row['summary'];
        $this->releaseSummary = $row['release_summary'];
        $this->priority = $row['priority'];
        $this->priorityDetail = $row['priority_detail'];
        $this->issueType = $row['issue_type'];
        $this->project = $row['project'];
        $this->projectKey = $row['project_key'];
        $this->originalEstimate = $row['original_estimate'];
        $this->remainingEstimate = $row['remaining_estimate'];
        $this->releaseDate = $row['release_date'];
        $this->labels = $row['labels'];
        $this->assignee = $row['assignee'];
        $this->assigneeKey = $row['assignee_key'];
        $this->requestor = $row['requestor'];
        $this->epicName = $row['epic_name'];
        $this->epicLink = $row['epic_link'];
        $this->epicColour = $row['epic_colour'];
    }

    public function getIssueKey() { return $this->issueKey;}
    public function getIssueStatus(){ return $this->issueStatus;}
    public function getSummary() { return $this->summary;}
    public function getReleaseSummary() { return $this->releaseSummary;}
    public function getPriority() { return $this->priority;}
    public function getPriorityDetail() {return $this->priorityDetail;}
    public function getIssueType() { return $this->issueType;}
    public function getProject() { return $this->project;}
    public function getProjectKey() { return $this->projectKey;}
    public function getOriginalEstimate() { return $this->originalEstimate;}
    public function getRemainingEstimate() { return $this->remainingEstimate;}
    public function getReleaseDate() { return $this->releaseDate;}
    public function getLabels() { return $this->labels;}
    public function getAssignee() { return $this->assignee;}
    public function getAssigneeKey() { return $this->assigneeKey;}
    public function getRequestor() { return $this->requestor;}
    public function getEpicName() { return $this->epicName;}
    public function getEpicLink() { return $this->epicLink;}
    public function getEpicColour() { return $this->epicColour;}
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