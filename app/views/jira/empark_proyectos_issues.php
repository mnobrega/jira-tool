<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    $selectedStatuses = array(
        DAOJIRAIssues::STATUS_RAW_REQUEST,
        DAOJIRAIssues::STATUS_ANALYSING,
        DAOJIRAIssues::STATUS_ANALYSED,
        DAOJIRAIssues::STATUS_TO_QUALITY,
        DAOJIRAIssues::STATUS_TO_DEVELOP,
        DAOJIRAIssues::STATUS_QA_IN_PROGRESS,
        DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
        DAOJIRAIssues::STATUS_DEV_DONE,
        DAOJIRAIssues::STATUS_QA_DONE,
        DAOJIRAIssues::STATUS_READY_TO_DEPLOY
    );

    $selectedTypes = array (
        DAOJIRAIssues::TYPE_STORY,
        DAOJIRAIssues::TYPE_IMPROVEMENT
    );

    $proyectoEstimateThreshold = 8*10*3600; //2 weeks

    $issues = $JIRAService->getPersistedIssues($selectedStatuses, $selectedTypes);
    $epicIssues = $JIRAService->getPersistedIssues($selectedStatuses, array(DAOJIRAIssues::TYPE_EPIC));
    $epics = array();
    foreach ($epicIssues as $epicIssue) {
        $epics[$epicIssue->getIssueKey()] = $epicIssue;
    }

    /*
     * BIZZ RULES
     * 1 - Only issues that belong to epics with more than $proyectoEstimateThreshold
     * 2 - Only iessus with Empark IT Requestor
     */
    $proyectoIssues = array();
    foreach ($issues as $issue) {
        if ($issue->getOriginalEstimate()>$proyectoEstimateThreshold ||
            (!is_null($issue->getEpicLink()) && $epics[$issue->getEpicLink()]->getOriginalEstimate()>$proyectoEstimateThreshold)) {
            if (!is_null($issue->getEMPITRequestor())) {
                $proyectoIssues[] = $issue;
            }
        }
    }

    //$issuesTimeSpent = $JIRAService->getPersistedIssuesTimeSpent($issues);

    if (count($_POST)) {
        foreach ($_POST["newPriorityDetail"] as $key=>$value) {
            if ($_POST["oldPriorityDetail"][$key]!==$value) {
                $JIRAService->editIssuePriorityDetail($key,$value);
            }
        }
    }

    $issues = $proyectoIssues;
    require_once(DIR_VIEWS."common/empark_issues_list.php");
