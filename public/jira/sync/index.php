<?php

    require_once(dirname(__FILE__)."/../../../" . "config.php");
    require_once(DIR_SERVICES."jira_service.php");
    require_once(DIR_SERVICES."app_service.php");

    $JIRAService = new JIRAService();
    $appService = new AppService();

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

    $epicIssues = array();
    $issues = array();
    $issuesTimeSpent = array();

    echo "Start: ".date("Y-m-d H:i:s")."<br>";

    $JIRAService->deleteAllPersistedIssues();

    $issues = $JIRAService->getIssuesByStatuses($selectedStatuses);
    $JIRAService->persistIssues($issues);

    $issuesHistory = $JIRAService->getIssuesHistories($issues, array(DAOJIRAIssues::TYPE_EPIC));
    $JIRAService->persistIssuesHistories($issuesHistory);

    $PMProjects = $appService->getProjectsByTeamKey(AppService::TEAM_MARKETBILITY_KEY,false);
    foreach ($PMProjects as $PMProject)
    {
        $workingDayHours = $appService->getProjectTeamAllocatedTime($PMProject->getName());
        $JIRAService->updatePMProjectsEstimatedDates($PMProject,$workingDayHours->getTeamAllocatedHoursPerDay());
    }

    echo "End: ".date("Y-m-d H:i:s")."<br>";
    echo "JIRA Sync finished successfully";
