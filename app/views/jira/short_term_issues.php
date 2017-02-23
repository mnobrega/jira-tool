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

    $selectedTypes = array (
        DAOJIRAIssues::TYPE_BUG,
        DAOJIRAIssues::TYPE_TASK,
        DAOJIRAIssues::TYPE_PROJECT_EMPARK,
        DAOJIRAIssues::TYPE_PROJECT_PM
    );

    $issues = $JIRAService->getPersistedIssues($selectedStatuses, $selectedTypes);
    $issuesTimeSpent = $JIRAService->getPersistedIssuesTimeSpent($issues);

    if (count($_POST)) {
        foreach ($_POST["newPriorityDetail"] as $key=>$value) {
            if ($_POST["oldPriorityDetail"][$key]!==$value) {
                $JIRAService->editIssue();
            }
        }
    }
?>
<form id="issuesForm" method="post" action="">
    <table id="tableIssuesProgressId" class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th>Issue</th>
                <th>Status</th>
                <th>Deploy</th>
                <th>Priority</th>
                <th class="col-sm-1">Pri. Detail</th>
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
                <td>
                    <input type="hidden" id="oldPriorityDetail[<?php echo $issue->getIssueKey();?>]" name="oldPriorityDetail[<?php echo $issue->getIssueKey();?>]"
                        value="<?php echo $issue->getPriorityDetail();?>"/>
                    <input id="newPriorityDetail[<?php echo $issue->getIssueKey();?>]" name="newPriorityDetail[<?php echo $issue->getIssueKey();?>]"
                           type="text" value="<?php echo $issue->getPriorityDetail();?>" class="form-control" />
                </td>
                <td><?php echo $issue->getRequestor();?></td>
                <td><?php echo $issue->getSummary();?></td>
                <td><?php echo round($issuesTimeSpent[$issue->getIssueKey()]/8,1);?> / <?php echo round($issue->getOriginalEstimate()/3600/8,1);?></td>
                <td><?php echo (round($issue->getOriginalEstimate()/3600,2)!=0?round($issuesTimeSpent[$issue->getIssueKey()]/round($issue->getOriginalEstimate()/3600,2),2)*100:0);?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</form>

<script>
    /* Create an array with the values of all the input boxes in a column, parsed as numbers */
    $.fn.dataTable.ext.order['dom-text-numeric'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
            return $('input', td).val() * 1;
        } );
    }

    $(document).ready( function () {
        $('#tableIssuesProgressId').DataTable({
            "pageLength":20,
            dom: 'Bfrtip',
            buttons: ['excel',{
                text : 'Update JIRA',
                action: function (e, dt, node, config) {
                    if (e.type=='click') {
                        $("#issuesForm").submit();
                    }
                }
            }],
            columns: [
                {"width":"6%"},
                {"width":"8%"},
                {"width":"8%"},
                {"width":"5%"},
                {"width":"3%","orderDataType":"dom-text-numeric"},
                {"width":"10%"},
                {"width":"45%"},
                {"width":"6%"},
                {"width":"7%"}
            ],
            order: [[4,"asc"]]
        });
    } );
</script>