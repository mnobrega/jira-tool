<?php

    require_once(DIR_SERVICES."jira_service.php");
    require_once(DIR_SERVICES."app_service.php");

    $JIRAService = new JIRAService();
    $appService = new AppService();


    $JIRAIssuesSelectedStatuses = array (
        DAOJIRAIssues::STATUS_DEV_IN_PROGRESS,
        DAOJIRAIssues::STATUS_QA_IN_PROGRESS,
        DAOJIRAIssues::STATUS_TO_DEVELOP,
        DAOJIRAIssues::STATUS_TO_QUALITY,
        DAOJIRAIssues::STATUS_ANALYSED
    );

    $JIRAVersions = $JIRAService->getVersions();

    $projects = $appService->getProjectsByTeamKey(AppService::TEAM_MARKETBILITY_KEY);
    $projectIssues = array();
    foreach ($projects as $project) {
        $projectIssues[$project->getName()] = $JIRAService->getPersistedIssuesWhere($project->getIssuesAllocationCriteriaSQL(),$JIRAIssuesSelectedStatuses);
        $roadmapData = $JIRAService->getTeamRoadmapData($projectIssues[$project->getName()]);
    }


    //TIME WINDOW CONFIG
    $now = new Datetime();
    $ganttStartMonth = $now->format("Y-m");
    $ganttMonthsNumber = 3;
    $JIRAIssueURL = "http://market.kujira.premium-minds.com/browse/";

    //GANT LINES
    $ganttLines = array();
    foreach ($JIRAVersions as $project=>$versions) {
        foreach ($versions as $JIRAVersion) {
            if (!$JIRAVersion->getReleased()) {
                $ganttLines[] = array (
                    "start" => $JIRAVersion->getReleaseDate(),
                    "displayValue" => $project."-".str_replace("-","",$JIRAVersion->getReleaseDate()),
                    "color" => "#DC143C",
                    "thickness" => "2",
                    "dashed" => "0"
                );
            }
        }
    }
    $ganttLines[] = array (
        "start" => $now->format("Y-m-d"),
        "displayValue" => "NOW",
        "color" => "#000000",
        "thickness" => "1"
    );

    //GANTT
    $ganttStart = new DateTime($ganttStartMonth."-01 00:00:00");
    $ganttEnd = clone($ganttStart);
    $ganttEnd->modify("+".($ganttMonthsNumber-1)." months");
    $ganttEnd = new DateTime($ganttEnd->format("Y-m-t 23:59:59"));

    //GATEGORIES
    $ganttMonthCategories = array();
    $ganttWeekCategories = array();
    $ganttStartDate = new DateTime($ganttStartMonth."-01 00:00:00");
    $week['start'] = $ganttStartDate->format("Y-m-d 00:00:00");
    $week['end'] = $ganttStartDate->format("Y-m-d 23:59:59");
    $weekCounter = 1;
    for ($i=0; $i<$ganttMonthsNumber;$i++)
    {
        $row['start'] = $ganttStartDate->format("Y-m-d H:i:s");
        $ganttEndDate = new DateTime($ganttStartDate->format("Y-m-t 23:59:59"));
        $row['end'] = $ganttEndDate->format("Y-m-d H:i:s");
        $row['align'] = "center";
        $row['name'] = $ganttStartDate->format("F");
        $row['fontcolor'] = "ffffff";
        $row['isbold'] = "1";
        $row['fontsize'] = "16";
        $ganttMonthCategories[] = $row;

        for ($j=1; $j <= $ganttEndDate->format("d"); $j++)
        {
            $ganttDay = new DateTime($ganttStartDate->format("Y-m-".$j));
            if ($ganttDay->format("w")=='1')
            {
                $week['label'] = "Week ".$weekCounter;
                $ganttWeekCategories[] = $week;
                $weekCounter++;
                $week['start'] = $ganttDay->format("Y-m-d 00:00:00");
                $week['end'] = $ganttDay->format("Y-m-d 23:59:59");
            }
            else
            {
                $week['end'] = $ganttDay->format("Y-m-d 23:59:59");
            }
        }
        $ganttStartDate->modify("+1 months");
    }
    $week['label'] = "Week ".$weekCounter;
    $ganttWeekCategories[] = $week;

    //PROCESSES
    $ganttProcesses = array();
    foreach($projects as $project) {
        $ganttProcesses[] = array(
            "label" => $project->getName(),
            "id" => $project->getName()
        );
    }

    //JIRA ISSUES
    $ganttTasks = array();
    $epicsDetected = array();
    foreach ($JIRAResourcesIssues as $resource=>$issues) {
        foreach ($issues as $issue) {
            /**@var $issue JIRAGanttIssue */
            $row['processid'] = $resource;
            $row['start'] = $issue->getStart();
            $row['end'] = $issue->getEnd();
            $row['label'] = $issue->getLabel();
            $row['color'] = $issue->getEpicColor();
            $row['link'] = "javascript:window.open('".$JIRAIssueURL.$issue->getIssueKey()."','_blank')";
            $ganttTasks[] = $row;

            if (!array_key_exists($issue->getEpicName(),$epicsDetected))
            {
                $epicsDetected[$issue->getEpicName()] = array("label"=>$issue->getEpicName(),"color"=>$issue->getEpicColor());
            }
        }
    }

    //LEGEND
    $ganttLegend = array();
    foreach ($epicsDetected as $epic)
    {
        $ganttLegend[] = $epic;
    }

?>
<div id="ganttContainerId"><!-- GANTT goes here --></div>

<script>
    var dataJSON = {
        "chart": {
            "dateformat": "dd/mm/yyyy",
            "ganttlinecolor": "CCCCCC",
            "ganttlinealpha": "20",
            "gridborderalpha": "20",
            "showtasknames": "1",
            "tooltextbgcolor": "F1F1F1",
            "tooltextbordercolor": "333333",
            "palettethemecolor": "333333",
            "showborder": "0",
            "showHoverEffect" : "0",
            "showTaskLabels":"1",
            "exportenabled":"1",
            "animation":"0"
        },
        "categories": [
            {
                "bgcolor": "333333",
                "basefont": "Arial",
                "basefontcolor": "FFFFFF",
                "basefontsize": "12",
                "category": [
                    {
                        "start": "<?php echo $ganttStart->format("Y-m-d H:i:s");?>",
                        "end": "<?php echo $ganttEnd->format("Y-m-d H:i:s");?>",
                        "align": "center",
                        "name": "eos Market / eos Mobility - Team Roadmap",
                        "fontcolor": "ffffff",
                        "isbold": "1",
                        "fontsize": "16"
                    }
                ]
            },
            {
                "font": "Arial",
                "fontcolor": "ffffff",
                "isbold": "1",
                "fontsize": "12",
                "bgcolor": "333333",
                "category": <?php echo json_encode($ganttMonthCategories);?>
            },
            {
                "font": "Arial",
                "fontcolor": "ffffff",
                "isbold": "1",
                "fontsize": "12",
                "bgcolor": "333333",
                "category": <?php echo json_encode($ganttWeekCategories);?>
            }
        ],
        "processes": {
            "headerbgcolor": "333333",
                "fontcolor": "ffffff",
                "fontsize": "12",
                "bgcolor": "333333",
                "align": "right",
                "process": <?php echo json_encode($ganttProcesses);?>
        },
        "tasks": {
            "color": "",
            "alpha": "",
            "font": "",
            "fontcolor": "",
            "fontsize": "",
            "isanimated": "1",
            "task": <?php echo json_encode($ganttTasks);?>
        },
        "legend": {
            "item": <?php echo json_encode($ganttLegend);?>
        },
        "trendlines" : [
            {
                "line" : <?php  echo json_encode($ganttLines);?>
            }
        ]
    };
    new FusionCharts({
        "type" : "gantt",
        "width":$(document).width(),
        "height":"500",
        "dataFormat":"json",
        "dataSource": dataJSON
    }).render("ganttContainerId");

    var myEventListener = function (eventObj, eventArgs) {
        console.log(eventObj.eventType + " was raised by the chart whose ID is " + eventObj.sender.id);
    };
</script>