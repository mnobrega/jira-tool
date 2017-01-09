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
            /**@var $issue Jira_Issue*/
            /**@var $issueTest Jira_Issue*/
            $issues[] = new JIRAIssue($issue);
        }

        $issueTest = $this->api->getIssue($issue->getKey(),"changelog");
        /**@var $issueTest Jira_Api_Result*/
        echo "<pre>";
        $expandedInformation = $issueTest->getResult();
        $changeLog = $expandedInformation['changelog'];
        foreach($changeLog['histories'] as $history)
        {
            //var_dump($history);die();
            if ($history['items'][0]['field']=='status')
            {
                echo $history['created']." - ".$history['items'][0]['fromString']." - ".$history['items'][0]['toString']."<br>";
            }
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
        $fields = $issue->getFields();
        $priority = $issue->getPriority();
        $status = $issue->getStatus();

        $this->key = $issue->getKey();
        $this->summary = $issue->getSummary();
        $this->priority = $priority['id'];
        $this->type = $fields['Issue Type']['name'];
        $this->project = $fields['Project']['name'];
        $this->originalEstimate = $fields['Original Estimate'];
        $this->remainingEstimate = $fields['Remaining Estimate'];
        $this->releaseDate = count($fields['Fix Version/s'])==1?
            $fields['Fix Version/s'][0]['releaseDate']:null;
        $this->labels = implode(',',$fields['Labels']);
        $this->assignee = $fields['Assignee']['displayName'];
        $this->releaseSummary = $fields['Release Summary'];
        $this->status = $status['name'];
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