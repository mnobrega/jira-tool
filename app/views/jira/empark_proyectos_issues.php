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

    $selectedProjectKeys = array (
        DAOJIRAIssues::PROJECT_MOBILITY,
        DAOJIRAIssues::PROJECT_MARKET
    );

    $whereSQL = "project_key IN ('".implode("','",$selectedProjectKeys)."')
                        AND issue_type IN ('".implode("','",$selectedTypes)."')
                        AND issue_status IN ('".implode("','",$selectedStatuses)."')
                        AND emp_it_requestor IS NOT NULL
                        AND epic_original_estimate >= '".JIRAService::EMPARK_PROYECTO_THRESHOLD."'";

    $issues = $JIRAService->getEmparkIssuesData($whereSQL);
    require_once(DIR_VIEWS."common/empark_issues_list.php");
