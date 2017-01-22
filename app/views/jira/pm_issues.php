<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    $selectedStatuses = array(
        DAOJIRAIssues::STATUS_ANALYSING,
        DAOJIRAIssues::STATUS_ANALYSED,
    );

    $issues = $JIRAService->getPersistedIssues($selectedStatuses);
    $issuesTimeSpent = $JIRAService->getPersistedIssuesTimeSpent($issues);
?>
<table id="tableIssuesProgressId" class="table table-striped table-bordered table-condensed">
    <thead>
        <tr>
            <th>Issue</th>
            <th>Status</th>
            <th>Deploy</th>
            <th>Priority</th>
            <th>Summary</th>
            <th>Days (E)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($issues as $issue) {?>
        <tr>
            <td><a href="http://market.kujira.premium-minds.com/browse/<?php echo $issue->getIssueKey();?>" target="_blank"><?php echo $issue->getIssueKey();?></a></td>
            <td><?php echo $issue->getIssueStatus();?></td>
            <td><?php echo $issue->getReleaseDate();?></td>
            <td><?php echo $issue->getPriority();?></td>
            <td><?php echo $issue->getSummary();?></td>
            <td><?php echo round($issue->getOriginalEstimate()/3600/8,1);?></td>
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
                {"width":"11%"},
                {"width":"8%"},
                {"width":"10%"},
                {"width":"45%"},
                {"width":"6%"}
            ]
        });
    } );
</script>