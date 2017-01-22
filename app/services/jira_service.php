<?php

require ROOT_DIR.'vendor/autoload.php';
require ROOT_DIR.'app/common/converters.php';
require ROOT_DIR.'app/services/time_service.php';
require ROOT_DIR . 'app/services/daos/dao_jira_issues.php';

class JIRAService
{
    const STATUS_DEV_IN_PROGRESS = 'Dev In Progress';
    const STATUS_QA_IN_PROGRESS = 'QA In Progress';
    const STATUS_ANALYSING = 'Analysing';
    const STATUS_TO_DEVELOP = 'To Develop';
    const STATUS_TO_QUALITY = 'To Quality';
    const STATUS_ANALYSED = 'Analysed';
    const STATUS_DEV_DONE = 'Dev Done';
    const STATUS_READY_TO_DEPLOY = 'Ready to Deploy';
    const STATUS_QA_DONE = 'QA Done';
    const STATUS_RAW_REQUEST = 'Raw Request';

    const HISTORY_ITEM_TYPE_STATUS = 'status';

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

        $this->walker->push('status IN (' . $statusString . ')  AND resolution=Unresolved ORDER BY priority DESC');
        foreach ($this->walker as $issue) {
            /**@var $issue Jira_Issue */
            $issues[] = new JIRAIssue($issue);
        }

        return $issues;
    }

    /**
     * @param $issues JIRAIssue []
     */
    public function persistIssues(Array $issues)
    {
        $this->daoJIRAIssues->deleteAllJIRAIssues();
        foreach ($issues as $issue)
        {
            $this->daoJIRAIssues->insertJIRAIssue(new JIRAIssueTblTuple($issue->toArray()));
        }
    }

    public function getPersistedIssues()
    {
        return $this->daoJIRAIssues->searchJIRAIssues();
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
        $fromStrings = array(DAOJIRAIssues::HISTORY_STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::HISTORY_STATUS_QA_IN_PROGRESS);
        $toStrings = array(DAOJIRAIssues::HISTORY_STATUS_DEV_IN_PROGRESS,
            DAOJIRAIssues::HISTORY_STATUS_QA_IN_PROGRESS);

        $issuesTimeSpent = array();
        $now = new DateTime();

        foreach ($issues as $issue){
            $issuesTimeSpent = 0;
            $issueHistories = $this->daoJIRAIssues->searchJIRAIssueHistories($issue->getIssueKey(),$fields,
                $fromStrings,$toStrings);
            foreach ($issueHistories as $issueHistory) {

                if ($issueHistory->getToString()==DAOJIRAIssues::HISTORY_STATUS_DEV_IN_PROGRESS)
                {

                }

                if ($issueHistory->getFromString()==DAOJIRAIssues::HISTORY_STATUS_DEV_IN_PROGRESS)
                {

                }

//                if ($historyItem['fromString'] == self::STATUS_TO_DEVELOP && $historyItem['toString'] == self::STATUS_DEV_IN_PROGRESS) {
//                    if (!is_null($timeInterval)) {
//                        $timeIntervals[] = $timeInterval;
//                    }
//                    $timeInterval = array('start' => $history['created'], 'end' => $now->format(DATE_ISO8601));
//                }
//                if ($historyItem['toString'] == self::STATUS_DEV_DONE || $historyItem['toString'] == self::STATUS_TO_DEVELOP) {
//                    if (is_array($timeInterval)) {
//                        $timeInterval['end'] = $history['created'];
//                        $timeIntervals[] = $timeInterval;
//                        $timeInterval = null;
//                    }
//                }

            }
        }
    }

    public function getIssuesTimeSpent(Array $issues)
    {
        $issuesTimeSpent = array();
        foreach ($issues as $issue) {
            var_dump($issue);
            /**@var $issue JIRAIssue */
            $issueTimeSpent = 0;
            if ($issue->getOriginalEstimate() != null) {
                $JiraApiResult = $this->api->getIssue($issue->getIssueKey(), "changelog");
                /**@var $JiraApiResult Jira_Api_Result */
                $expandedInformation = $JiraApiResult->getResult();
                $changelog = $expandedInformation['changelog'];
                $timeIntervals = array();
                $timeInterval = null;
                $now = new DateTime();
                foreach ($changelog['histories'] as $history) {
                    var_dump($history);
                    foreach ($history['items'] as $historyItem) {
                        if ($historyItem['field'] == 'status') {
                            if ($historyItem['fromString'] == self::STATUS_TO_DEVELOP && $historyItem['toString'] == self::STATUS_DEV_IN_PROGRESS) {
                                if (!is_null($timeInterval)) {
                                    $timeIntervals[] = $timeInterval;
                                }
                                $timeInterval = array('start' => $history['created'], 'end' => $now->format(DATE_ISO8601));
                            }
                            if ($historyItem['toString'] == self::STATUS_DEV_DONE || $historyItem['toString'] == self::STATUS_TO_DEVELOP) {
                                if (is_array($timeInterval)) {
                                    $timeInterval['end'] = $history['created'];
                                    $timeIntervals[] = $timeInterval;
                                    $timeInterval = null;
                                }
                            }
                        }
                    }
                }
                if (!is_null($timeInterval)){
                    $timeIntervals[] = $timeInterval;
                }
                $issueTimeSpent = $this->timeService->getWorkingHours($timeIntervals);
            }

            $issuesTimeSpent[$issue->getIssueKey()] = $issueTimeSpent;

        }

        return $issuesTimeSpent;
    }
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
    private $releaseSummary;
    private $issueStatus;

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
        $this->releaseSummary = $fields['Release Summary'];
        $this->issueStatus = $status['name'];
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
    public function getReleaseSummary(){ return $this->releaseSummary;}
    public function getIssueStatus() { return $this->issueStatus;}

    public function toArray()
    {
        $params = get_object_vars($this);
        return convertCamelCaseKeys2camel_case($params);
    }
}