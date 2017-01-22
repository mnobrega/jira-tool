<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();
    $selectedStatusesTeam = array(
        JIRAService::STATUS_TO_DEVELOP,
        JIRAService::STATUS_TO_QUALITY,
        JIRAService::STATUS_QA_IN_PROGRESS,
        JIRAService::STATUS_DEV_IN_PROGRESS,
        JIRAService::STATUS_DEV_DONE,
        JIRAService::STATUS_QA_DONE,
        JIRAService::STATUS_READY_TO_DEPLOY
    );
    $selectedStatusesPM = array(
        JIRAService::STATUS_ANALYSED
    );
    $selectedIssuesHistoryTypes = array(
        JIRAService::HISTORY_ITEM_TYPE_STATUS
    );

    $issues = array();
    $issuesTimeSpent = array();

    if (isset($_GET['sync']) && $_GET['sync']=='true'){
        $issues = $JIRAService->getIssuesByStatuses($selectedStatusesTeam);
        $JIRAService->persistIssues($issues);

        $issuesHistory = $JIRAService->getIssuesHistories($issues, $selectedIssuesHistoryTypes);
        $JIRAService->persistIssuesHistories($issuesHistory);
    }
    $issues = $JIRAService->getPersistedIssues();
    $issuesTimeSpent = $JIRAService->getPersistedIssuesTimeSpent($issues);
?>
<table id="tableIssuesProgressId" class="table table-striped table-bordered table-condensed">
    <thead>
        <tr>
            <th>Issue</th>
            <th>Status</th>
            <th>Deploy</th>
            <th>Priority</th>
            <th>Assignee</th>
            <th>Summary</th>
<!--            <th>Days (real/estimated)</th>-->
<!--            <th>Progress (%)</th>-->
        </tr>
    </thead>
    <tbody>
        <?php foreach ($issues as $issue) {?>
        <tr>
            <td><a href="http://market.kujira.premium-minds.com/browse/<?php echo $issue->getIssueKey();?>" target="_blank"><?php echo $issue->getIssueKey();?></a></td>
            <td><?php echo $issue->getIssueStatus();?></td>
            <td><?php echo $issue->getReleaseDate();?></td>
            <td><?php echo $issue->getPriority();?></td>
            <td><?php echo $issue->getAssignee();?></td>
            <td><?php echo $issue->getSummary();?></td>
<!--            <td>--><?php //echo round($issuesTimeSpent[$issue->getIssueKey()]/8,1);?><!-- / --><?php //echo round($issue->getOriginalEstimate()/3600/8,1);?><!--</td>-->
<!--            <td>--><?php //echo (round($issue->getOriginalEstimate()/3600,2)!=0?round($issuesTimeSpent[$issue->getIssueKey()]/round($issue->getOriginalEstimate()/3600,2),2)*100:0);?><!--</td>-->
        </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    $(document).ready( function () {
        $('#tableIssuesProgressId').DataTable({
            "pageLength":20,
            dom: 'Bfrtip',
            buttons: ['excel'],
            columns: [
                {"width":"9%"},
                {"width":"8%"},
                {"width":"8%"},
                {"width":"5%"},
                {"width":"10%"},
                {"width":"60%"}
            ]
        });
    } );
</script>