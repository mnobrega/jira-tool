<?php

require ROOT_DIR.'vendor/autoload.php';

class JIRAService
{
    const STATUS_DEV_IN_PROGRESS = 'Dev In Progress';
    const STATUS_QA_IN_PROGRESS = 'QA In Progress';
    const STATUS_TO_DEVELOP = 'To Develop';
    const STATUS_TO_QUALITY = 'To Quality';
    const STATUS_ANALYSED = 'Analysed';

    private $api;
    private $walker;

    function __construct()
    {
        $this->api = new \Jira_Api(
            'http://market.kujira.premium-minds.com',
            new \Jira_Api_Authentication_Basic('mnobrega','Madeira.24404')
        );

        $this->walker = new Jira_Issues_Walker($this->api);
    }

    /**
     * @param array $status
     * @return JIRAIssue []
     */
    public function getIssuesByStatuses(Array $status)
    {
        $issues = array();
        $statusString = '"'.implode('","',$status).'"';

        $this->walker->push('status IN ('.$statusString.') ORDER BY priority DESC');
        foreach ($this->walker as $issue)
        {
            $issues[] = new JIRAIssue($issue);
        }

        return $issues;
    }
}

class JIRAIssue
{
    private $key;
    private $summary;
    private $priority;
    private $type;
    private $project;
    private $originalEstimate;
    private $remainingEstimate;
    private $releaseDate;
    private $labels;
    private $assignee;
    private $releaseSummary;
    private $status;

    public function __construct(Jira_Issue $issue)
    {
        $this->key = $issue->getKey();
        $this->summary = $issue->getSummary();
        $this->priority = $issue->getPriority()['id'];
        $this->type = $issue->getFields()['Issue Type']['name'];
        $this->project = $issue->getFields()['Project']['name'];
        $this->originalEstimate = $issue->getFields()['Original Estimate'];
        $this->remainingEstimate = $issue->getFields()['Remaining Estimate'];
        $this->releaseDate = count($issue->getFields()['Fix Version/s'])==1?
            $issue->getFields()['Fix Version/s'][0]['releaseDate']:null;
        $this->labels = implode(',',$issue->getFields()['Labels']);
        $this->assignee = $issue->getFields()['Assignee']['displayName'];
        $this->releaseSummary = $issue->getFields()['Release Summary'];
        $this->status = $issue->getStatus()['name'];
    }

    public function getKey() { return $this->key;}
    public function getSummary() { return $this->summary;}
    public function getPriority() { return $this->priority;}
    public function getType() { return $this->type;}
    public function getProject() { return $this->project;}
    public function getOriginalEstimate() { return $this->originalEstimate;}
    public function getRemainingEstimate() { return $this->remainingEstimate;}
    public function getReleaseDate() { return $this->releaseDate;}
    public function getLabels() { return $this->labels;}
    public function getAssignee() { return $this->assignee;}
    public function getReleaseSummary(){ return $this->releaseSummary;}
    public function getStatus() { return $this->status;}
}