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

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $statuses array
     * @return JIRAIssueTblTuple []
     */
    public function searchJIRAIssues(Array $statuses=null)
    {
        $query = "SELECT ji.*
                    FROM ".self::TABLENAME_JIRA_ISSUES." ji
                    WHERE ji.id=ji.id
                        ".(!is_null($statuses)?" AND ji.issue_status IN ".$this->inArray($statuses):"");
        return $this->getObjArray($this->query($query),"JIRAIssueTblTuple");
    }

    public function insertJIRAIssue(JIRAIssueTblTuple $tuple)
    {
        $query = "INSERT INTO ".self::TABLENAME_JIRA_ISSUES." (issue_key, issue_status, summary, release_summary,
            priority, issue_type, project, original_estimate, remaining_estimate, release_date, labels, assignee,
            requestor)
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
                ".(!is_null($tuple->getRequestor())?"'".$tuple->getRequestor()."'":"NULL").")";

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
    private $issueType;
    private $project;
    private $originalEstimate;
    private $remainingEstimate;
    private $releaseDate;
    private $labels;
    private $assignee;
    private $requestor;

    public function __construct($row)
    {
        $this->issueKey = $row['issue_key'];
        $this->issueStatus = $row['issue_status'];
        $this->summary = $row['summary'];
        $this->releaseSummary = $row['release_summary'];
        $this->priority = $row['priority'];
        $this->issueType = $row['issue_type'];
        $this->project = $row['project'];
        $this->originalEstimate = $row['original_estimate'];
        $this->remainingEstimate = $row['remaining_estimate'];
        $this->releaseDate = $row['release_date'];
        $this->labels = $row['labels'];
        $this->assignee = $row['assignee'];
        $this->requestor = $row['requestor'];
    }

    public function getIssueKey() { return $this->issueKey;}
    public function getIssueStatus(){ return $this->issueStatus;}
    public function getSummary() { return $this->summary;}
    public function getReleaseSummary() { return $this->releaseSummary;}
    public function getPriority() { return $this->priority;}
    public function getIssueType() { return $this->issueType;}
    public function getProject() { return $this->project;}
    public function getOriginalEstimate() { return $this->originalEstimate;}
    public function getRemainingEstimate() { return $this->remainingEstimate;}
    public function getReleaseDate() { return $this->releaseDate;}
    public function getLabels() { return $this->labels;}
    public function getAssignee() { return $this->assignee;}
    public function getRequestor() { return $this->requestor;}
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