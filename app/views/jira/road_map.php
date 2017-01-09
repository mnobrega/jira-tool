<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();
    $selectedStatusesTeam = array(
        JIRAService::STATUS_QA_IN_PROGRESS,
        JIRAService::STATUS_DEV_IN_PROGRESS,
        JIRAService::STATUS_TO_DEVELOP,
        JIRAService::STATUS_TO_QUALITY
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
            <th>Type</th>
            <th>Release Date</th>
            <th>Priority</th>
            <th>Assignee</th>
            <th>Summary</th>
            <th>Original Estimate</th>
            <th>Remaining Estimate</th>
        </tr>
    </thead>
    <tbody>
        <?php  $issues = $JIRAService->getIssuesByStatuses($selectedStatusesTeam); ?>
        <?php foreach ($issues as $issue) {?>
        <tr>
            <td><a href="http://market.kujira.premium-minds.com/browse/<?php echo $issue->getKey();?>" target="_blank"><?php echo $issue->getKey();?></a></td>
            <td><?php echo $issue->getStatus();?></td>
            <td><?php echo $issue->getType();?></td>
            <td><?php echo $issue->getReleaseDate();?></td>
            <td><?php echo $issue->getPriority();?></td>
            <td><?php echo $issue->getAssignee();?></td>
            <td><?php echo $issue->getSummary();?></td>
            <td><?php echo $issue->getOriginalEstimate();?></td>
            <td><?php echo $issue->getRemainingEstimate();?></td>
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