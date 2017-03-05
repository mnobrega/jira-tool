<?php
    require_once(DIR_SERVICES."jira_service.php");
    $JIRAService = new JIRAService();

    if (count($_POST)) {
        foreach ($_POST["newPriorityDetail"] as $key=>$value) {
            if ($_POST["oldPriorityDetail"][$key]!==$value) {
                $JIRAService->editIssuePriorityDetail($key,$value);
            }
        }
        header('Location:'.$_SERVER['PHP_SELF']);
    }
?>
<div class="panel">
    <div class="panel-body">
        <form id="issuesForm" method="post" action="">
            <table id="tableIssuesProgressId" class="table table-striped table-bordered table-condensed">
                <thead>
                <tr>
                    <th class="col-sm-1">Prio. Det.</th>
                    <th>Prio.</th>
                    <th>Status</th>
                    <th>JIRA Issue</th>
                    <th>Proyecto</th>
                    <th>Descripción</th>
                    <th>Resp. EMP</th>
                    <th>Resp. PM</th>
                    <th>App</th>
                    <th>Esfuerzo (Días)</th>
                    <th>Cliente</th>
                    <th>Fecha Solicitud</th>
                    <th>Fecha EMP</th>
                    <th>Fecha PM (Estimada)</th>
                    <th>Dependencias</th>
                    <th>Fecha Despliegue</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($issues as $issue) { /**@var $issue JIRAIssueTblTupleExtended */?>
                    <tr>
                        <td>
                            <input type="hidden" id="oldPriorityDetail[<?php echo $issue->getIssueKey();?>]" name="oldPriorityDetail[<?php echo $issue->getIssueKey();?>]"
                                   value="<?php echo $issue->getPriorityDetail();?>"/>
                            <input id="newPriorityDetail[<?php echo $issue->getIssueKey();?>]" name="newPriorityDetail[<?php echo $issue->getIssueKey();?>]"
                                   type="text" value="<?php echo $issue->getPriorityDetail();?>" class="form-control-priority" />
                        </td>
                        <td><?php echo $issue->getPriority();?></td>
                        <td><?php echo $issue->getIssueStatus();?></td>
                        <td><a href="http://market.kujira.premium-minds.com/browse/<?php echo $issue->getIssueKey();?>" target="_blank"><?php echo $issue->getIssueKey();?></a></td>
                        <td><?php echo $issue->getEpicShortSummary().(strlen($issue->getShortSummary()>0)?" - ".$issue->getShortSummary():"");?></td>
                        <td><?php echo $issue->getReleaseSummary();?></td>
                        <td><?php echo $issue->getEMPITRequestor();?></td>
                        <td><?php echo $issue->getPMProjectManager();?></td>
                        <td><?php echo $issue->getProject();?></td>
                        <td><?php echo round(($issue->getOriginalEstimate()/3600)/8,2)==0?"n/d":round(($issue->getOriginalEstimate()/3600)/8,2);?></td>
                        <td><?php echo $issue->getEMPCustomer();?></td>
                        <td><?php echo $issue->getRequestDate();?></td>
                        <td><?php echo $issue->getDueDate();?></td>
                        <td><?php echo $issue->getPMEstimatedDate();?></td>
                        <td><!-- dependencias --></td>
                        <td><?php echo $issue->getReleaseDate();?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
<script>
    /* Create an array with the values of all the input boxes in a column, parsed as numbers */
    $.fn.dataTable.ext.order['dom-text-numeric'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
            return $('input', td).val() * 1;
        } );
    };

    $(document).ready( function () {
        $('#tableIssuesProgressId').DataTable({
            pageLength:15,
            dom: 'Bfrtlip',
            buttons: [
                {
                    extend: 'excel',
                    exportOptions: { orthogonal: 'export'}
                },
                {
                    text : 'Update JIRA',
                    action: function (e, dt, node, config) {
                        if (e.type=='click') {
                            $("#issuesForm").submit();
                        }
                    }
                }
            ],
            columns: [
                {
                    "width":"3%",
                    "orderDataType":"dom-text-numeric",
                    render: function(data, type, row) {
                        if (type === 'export') {
                            var priorityDetail = $(data).find('input[type="text"]').addBack();
                            return priorityDetail.val();
                        } else {
                            return data;
                        }
                    }
                }, //priority detail
                {"width":"3%"}, //priority
                {"width":"4%"}, // status
                {"width":"3%"}, // JIRA Issue
                {"width":"10%"}, // Summary
                {"width":"20%"}, // Description
                {"width":"5%"}, // Requestor Emp
                {"width":"5%"}, // Requestor PM
                {"width":"8%"}, // App
                {"width":"3%"}, // Estimate
                {"width":"5%"}, // customer
                {"width":"7%"}, // Request Date
                {"width":"5%"}, // Emp Date
                {"width":"5%"}, // PM Estimated Date
                {"width":"5%"}, // Dependencies
                {"width":"5%"}  // PM Deploy Date
             ],
            order: [[4,"asc"]]
        });
    } );
</script>