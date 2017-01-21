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

    public function __construct()
    {
        parent::__construct();
    }

    public function insertJIRAIssue(JIRAIssueTblTuple $tuple)
    {
        $query = "INSERT INTO ".self::TABLENAME_JIRA_ISSUES." (issue_key, issue_status, summary, release_summary,
            priority, issue_type, project, original_estimate, remaining_estimate, release_date, labels, assignee)
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
                '".$tuple->getAssignee()."')";

        $this->query($query);
    }

    public function deleteAllJIRAIssues()
    {
        $query = "DELETE FROM ".self::TABLENAME_JIRA_ISSUES;
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
}