<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    $selectedStatuses = array(
        DAOJIRAIssues::STATUS_TO_QUALITY,
        DAOJIRAIssues::STATUS_TO_DEVELOP,
        DAOJIRAIssues::STATUS_QA_IN_PROGRESS,
        DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
        DAOJIRAIssues::STATUS_DEV_DONE,
        DAOJIRAIssues::STATUS_QA_DONE,
        DAOJIRAIssues::STATUS_READY_TO_DEPLOY
    );

    $selectedTypes = array (
        DAOJIRAIssues::TYPE_BUG,
        DAOJIRAIssues::TYPE_TASK,
        DAOJIRAIssues::TYPE_STORY,
        DAOJIRAIssues::TYPE_SPIKE,
        DAOJIRAIssues::TYPE_IMPROVEMENT
    );

    $issues = $JIRAService->getPersistedIssues($selectedStatuses, $selectedTypes);
    $issuesTimeSpent = $JIRAService->getPersistedIssuesTimeSpent($issues);

    if (count($_POST)) {
        foreach ($_POST["newPriorityDetail"] as $key=>$value) {
            if ($_POST["oldPriorityDetail"][$key]!==$value) {
                $JIRAService->editIssuePriorityDetail($key,$value);
            }
        }
    }

    require_once(DIR_VIEWS."common/issues_list.php");
