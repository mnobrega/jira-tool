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
?>
<table id="tableIssuesProgressId" class="table table-striped table-bordered table-condensed">
    <thead>
        <tr>
            <th>Key</th>
            <th>Status</th>
            <th>Release Date</th>
            <th>Priority</th>
            <th>Assignee</th>
            <th>Summary</th>
            <th>Orig. Estimate (days)</th>
            <th>Rema. Estimate (days)</th>
            <th>Cur. Time Spent (days)</th>
            <th>Progress (%)</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $issues = $JIRAService->getIssuesByStatuses($selectedStatusesTeam);
            $issuesTimeSpent = $JIRAService->getIssuesTimeSpent($issues);
        ?>
        <?php foreach ($issues as $issue) {?>
        <tr>
            <td><a href="http://market.kujira.premium-minds.com/browse/<?php echo $issue->getKey();?>" target="_blank"><?php echo $issue->getKey();?></a></td>
            <td><?php echo $issue->getStatus();?></td>
            <td><?php echo $issue->getReleaseDate();?></td>
            <td><?php echo $issue->getPriority();?></td>
            <td><?php echo $issue->getAssignee();?></td>
            <td><?php echo $issue->getSummary();?></td>
            <td><?php echo round($issue->getOriginalEstimate()/3600/8,2);?></td>
            <td><?php echo round($issue->getRemainingEstimate()/3600/8,2);?></td>
            <td><?php echo round($issuesTimeSpent[$issue->getKey()]/8,2);?></td>
            <td><?php echo (round($issue->getOriginalEstimate()/3600,2)!=0?round($issuesTimeSpent[$issue->getKey()]/round($issue->getOriginalEstimate()/3600,2),2)*100:0);?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    $(document).ready( function () {
        $('#tableIssuesProgressId').DataTable({
            "pageLength":20,
            dom: 'Bfrtip',
            buttons: ['excel']
        });
    } );
</script>