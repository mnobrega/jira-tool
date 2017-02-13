<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    $requestedIssues = $JIRAService->getPreviousWeekCreatedIssues();
    $startedIssues = $JIRAService->getPreviousWeekStartedIssues();
    $finishedIssues = $JIRAService->getPreviousWeekFinishedIssues();

?>
<div class="col-md-4 text-center">
    <span>Últimos Pedidos</span>
</div>
<div class="col-md-4 text-center">
    <span>Iniciados</span>
</div>
<div class="col-md-4 text-center">
    <span>Terminados</span>
</div>
<div class="col-md-1">
    APK-12
</div>
<div class="col-md-3">
    Requestor
</div>
<div class="col-md-1">
    APK-12
</div>
<div class="col-md-3">
    Requestor
</div>
<div class="col-md-1">
    APK-12
</div>
<div class="col-md-3">
    Requestor
</div>