<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    $selectedStatuses = array(
        DAOJIRAIssues::STATUS_ANALYSING,
        DAOJIRAIssues::STATUS_ANALYSED,
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

    require_once(DIR_VIEWS."common/issues_list.php");
