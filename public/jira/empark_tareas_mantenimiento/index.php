<?php require_once(dirname(__FILE__)."/../../../" . "config.php"); ?>
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
<html>
    <?php require_once(DIR_VIEWS."common/header.php");?>
    <body>
        <?php require_once(DIR_VIEWS."common/menu.php");?>
        <?php require_once(DIR_VIEWS . "jira/empark_tareas_mantenimiento_issues.php");?>
    </body>
</html>
