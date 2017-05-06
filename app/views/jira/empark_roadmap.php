<?php

    require_once(DIR_SERVICES."jira_service.php");
    require_once(DIR_SERVICES."app_service.php");

    $JIRAService = new JIRAService();
    $appService = new AppService();

    $JIRAIssuesSelectedStatuses = array (
        DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
        DAOJIRAIssues::STATUS_QA_IN_PROGRESS,
        DAOJIRAIssues::STATUS_TO_DEVELOP,
        DAOJIRAIssues::STATUS_TO_QUALITY,
        DAOJIRAIssues::STATUS_ANALYSED
    );

    $selectedTeams = array (
        AppService::TEAM_MM_Empark_DEV,
        AppService::TEAM_MM_Empark_QA,
        AppService::TEAM_MM_Premium_DEV,
        AppService::TEAM_MM_Premium_QA,
        AppService::TEAM_MM_Empark_DEV_FR,
    );

    $selectedProjects = array (
        DAOJIRAIssues::PROJECT_MARKET,
        DAOJIRAIssues::PROJECT_MOBILITY
    );

    $JIRAProjects = array();
    $JIRAProjectsIssues = array();
    $projects = $appService->getProjectNamesByTeamKeys($selectedTeams,false);
    foreach ($projects as $PMProjectName) {
        $projectIssues = $JIRAService->getPersistedIssuesByPMProjectName($PMProjectName->getName(),"estimated_end_date ASC");
        $workingDayHours = $appService->getProjectTeamAllocatedTime($PMProjectName->getName());
        $JIRAProjectsIssues[$PMProjectName->getName()] = $JIRAService->getTeamRoadmapData($projectIssues,$PMProjectName->getName(),
            $workingDayHours->getTeamAllocatedHoursPerDay());
    }
    $JIRAVersions = $JIRAService->getProjectsVersions($selectedProjects);

    require_once(DIR_VIEWS."common/roadmap.php");