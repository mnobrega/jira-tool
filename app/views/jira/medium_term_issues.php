<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    $selectedStatuses = array(
        DAOJIRAIssues::STATUS_ANALYSING,
        DAOJIRAIssues::STATUS_ANALYSED,
    );

    $selectedTypes = array (
        DAOJIRAIssues::TYPE_BUG,
        DAOJIRAIssues::TYPE_TASK,
        DAOJIRAIssues::TYPE_PROJECT_EMPARK,
        DAOJIRAIssues::TYPE_PROJECT_PM
    );

    $issues = $JIRAService->getPersistedIssues($selectedStatuses, $selectedTypes);
    $issuesTimeSpent = $JIRAService->getPersistedIssuesTimeSpent($issues);
?>
<table id="tableIssuesProgressId" class="table table-striped table-bordered table-condensed">
    <thead>
        <tr>
            <th>Issue</th>
            <th>Type</th>
            <th>Priority</th>
            <th>Requestor</th>
            <th>Summary</th>
            <th>Days (E)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($issues as $issue) {?>
        <tr>
            <td><a href="http://market.kujira.premium-minds.com/browse/<?php echo $issue->getIssueKey();?>" target="_blank"><?php echo $issue->getIssueKey();?></a></td>
            <td><?php echo $issue->getIssueType();?></td>
            <td><?php echo $issue->getPriorityDetail();?></td>
            <td><?php echo $issue->getRequestor();?></td>
            <td><?php echo $issue->getSummary();?></td>
            <td><?php echo round($issue->getOriginalEstimate()/3600/8,1);?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    $(document).ready( function () {
        $('#tableIssuesProgressId').DataTable({
            "pageLength":35,
            dom: 'Bfrtip',
            order: [[2,"asc"]],
            buttons: ['excel'],
            columns: [
                {"width":"8%"},
                {"width":"7%"},
                {"width":"6%"},
                {"width":"10%"},
                {"width":"58%"},
                {"width":"5%"}
            ]
        });
    } );
</script>