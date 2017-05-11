<?php
    require_once(DIR_SERVICES."jira_service.php");
    require_once(DIR_SERVICES."app_service.php");

    $JIRAService = new JIRAService();
    $appService = new AppService();

    $projects = $appService->getProjects();
    $projectNames = $appService->getProjectNames($projects);

    $projectsTeamAllocatedTime = $appService->getProjectsTeamAllocatedTime($projectNames);
    debug($projectsTeamAllocatedTime);
?>
