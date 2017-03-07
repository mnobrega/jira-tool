<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    $requestedIssues = $JIRAService->getPreviousWeekCreatedIssues(true);
    $startedIssues = $JIRAService->getPreviousWeekStartedIssues(true);
    $finishedIssues = $JIRAService->getPreviousWeekFinishedIssues(true);

    $linesCount = max(count($requestedIssues),count($startedIssues),count($finishedIssues));

?>
    <div class="panel">
        <div class="panel-body">
            <div class="col-md-4 text-center">
                <h4>Latest Issues</h4>
            </div>
            <div class="col-md-4 text-center">
                <h4>Started / Revisited</h4>
            </div>
            <div class="col-md-4 text-center">
                <h4>Completed</h4>
            </div>
            <?php for ($i=0;$i<$linesCount;$i++) { ?>
                <div class="col-md-12"><hr></div>
                <?php if (array_key_exists($i,$requestedIssues)) { $issue = $requestedIssues[$i];?>
                    <div class="col-md-4">
                        <div class="col-md-2">
                            <?php echo (strlen($issue->getEmpITRequestor())>0?$issue->getEmpITRequestor():"n/d");?>
                        </div>
                        <div class="col-md-2">
                            <a href="<?php echo JIRA_URL."/browse/".$issue->getIssueKey();?>" target="_blank"><?php echo $issue->getIssueKey();?></a>
                        </div>
                        <div class="col-md-8">
                            <?php echo (strlen($issue->getReleaseSummary())>0)?$issue->getReleaseSummary():$issue->getSummary();?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="col-md-4"></div>
                <?php } ?>
                <?php if (array_key_exists($i,$startedIssues)) { $issue = $startedIssues[$i];?>
                    <div class="col-md-4">
                        <div class="col-md-2">
                            <?php echo (strlen($issue->getEmpITRequestor())>0?$issue->getEmpITRequestor():"n/d");?>
                        </div>
                        <div class="col-md-2">
                            <a href="<?php echo JIRA_URL."/browse/".$issue->getIssueKey();?>" target="_blank"><?php echo $issue->getIssueKey();?></a>
                        </div>
                        <div class="col-md-8">
                            <?php echo (strlen($issue->getReleaseSummary())>0)?$issue->getReleaseSummary():$issue->getSummary();?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="col-md-4"></div>
                <?php } ?>
                <?php if (array_key_exists($i,$finishedIssues)) { $issue = $finishedIssues[$i];?>
                    <div class="col-md-4">
                        <div class="col-md-2">
                            <?php echo (strlen($issue->getEmpITRequestor())>0?$issue->getEmpITRequestor():"n/d");?>
                        </div>
                        <div class="col-md-2">
                            <a href="<?php echo JIRA_URL."/browse/".$issue->getIssueKey();?>" target="_blank"><?php echo $issue->getIssueKey();?></a>
                        </div>
                        <div class="col-md-8">
                            <?php echo (strlen($issue->getReleaseSummary())>0)?$issue->getReleaseSummary():$issue->getSummary();?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="col-md-4"></div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>