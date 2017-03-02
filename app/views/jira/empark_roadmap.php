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

    $JIRAProjects = array();
    $JIRAProjectsIssues = array();
    $projects = $appService->getProjectsByTeamKey(AppService::TEAM_MARKETBILITY_KEY, false);
    foreach ($projects as $project) {
        $projectIssues = $JIRAService->getPersistedIssuesWhere($project->getIssuesAllocationCriteriaSQL(),
            $JIRAIssuesSelectedStatuses);
        $projectTeamAllocatedTime = $appService->getProjectTeamAllocatedTime($project->getName());
        $JIRAProjectsIssues[$project->getName()] = $JIRAService->getTeamRoadmapData($projectIssues,$project->getName(),
            $projectTeamAllocatedTime->getTeamAllocatedHoursPerDay());
        if (!in_array($project->getJIRAProjectKey(),$JIRAProjects)) {
            $JIRAProjects[] = $project->getJIRAProjectKey();
        }
    }
    $JIRAVersions = $JIRAService->getVersions($JIRAProjects);

    require_once(DIR_VIEWS."common/roadmap.php");