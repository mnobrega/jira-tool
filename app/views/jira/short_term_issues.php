<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    $selectedStatuses = array(
        DAOJIRAIssues::STATUS_TO_QUALITY,
        DAOJIRAIssues::STATUS_TO_DEVELOP,
        DAOJIRAIssues::STATUS_QA_IN_PROGRESS,
        DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
        DAOJIRAIssues::STATUS_DEV_DONE,
        DAOJIRAIssues::STATUS_QA_DONE,
        DAOJIRAIssues::STATUS_READY_TO_DEPLOY
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
            <th>Requestor</th>
            <th>Summary</th>
            <th>Days (R/E)</th>
            <th>Progress (%)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($issues as $issue) {?>
        <tr>
            <td><a href="http://market.kujira.premium-minds.com/browse/<?php echo $issue->getIssueKey();?>" target="_blank"><?php echo $issue->getIssueKey();?></a></td>
            <td><?php echo $issue->getIssueStatus();?></td>
            <td><?php echo $issue->getReleaseDate();?></td>
            <td><?php echo $issue->getPriority();?></td>
            <td><?php echo $issue->getRequestor();?></td>
            <td><?php echo $issue->getSummary();?></td>
            <td><?php echo round($issuesTimeSpent[$issue->getIssueKey()]/8,1);?> / <?php echo round($issue->getOriginalEstimate()/3600/8,1);?></td>
            <td><?php echo (round($issue->getOriginalEstimate()/3600,2)!=0?round($issuesTimeSpent[$issue->getIssueKey()]/round($issue->getOriginalEstimate()/3600,2),2)*100:0);?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    $(document).ready( function () {
        $('#tableIssuesProgressId').DataTable({
            "pageLength":20,
            dom: 'Bfrtip',
            order: [[3,"asc"]],
            buttons: ['excel'],
            columns: [
                {"width":"6%"},
                {"width":"8%"},
                {"width":"8%"},
                {"width":"5%"},
                {"width":"10%"},
                {"width":"50%"},
                {"width":"6%"},
                {"width":"7%"}
            ]
        });
    } );
</script>