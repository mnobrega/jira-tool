<?php

    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    //TIME WINDOW CONFIG
    $ganttStartMonth = '2017-01';
    $ganttMonthsNumber = 3;
    $JIRAIssueURL = "http://market.kujira.premium-minds.com/browse/";

    //GANT LINES
    $ganttLines = array (
        array (
            "start" => "2017-01-31",
            "displayValue" => "APK-JAN-1",
            "color" => "#000000",
            "thickness" => "2",
            "dashed" => "1"
        ),
        array (
            "start" => "2017-02-16",
            "displayValue" => "APK-FEV-1",
            "color" => "#000000",
            "thickness" => "2",
            "dashed" => "1"
        ),
        array (
            "start" => "2017-02-09",
            "displayValue" => "MOB-FEV-1",
            "color" => "#000000",
            "thickness" => "2",
            "dashed" => "1"
        ),
        array (
            "start" => "2017-02-22",
            "displayValue" => "MOB-FEV-2",
            "color" => "#000000",
            "thickness" => "2",
            "dashed" => "1"
        )
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
    $ganttProcesses = array(
        array(
            "label" => "MOB-DEV-1",
            "id" => "MOBDEV1"
        ),
        array (
            "label" => "APK-DEV-1",
            "id" => "APKDEV1"
        ),
        array (
            "label" => "QA-1",
            "id" => "QA1"
        ),
        array (
            "label" => "QA-2",
            "id" => "QA2"
        )
    );

    //JIRA ISSUES
    $JIRAResourcesIssues = $JIRAService->getGanttData();
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