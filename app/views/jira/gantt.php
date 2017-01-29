<?php
    require_once(DIR_SERVICES."jira_service.php");

    $JIRAService = new JIRAService();

    $ganttData = $JIRAService->getGanttData();


?>
<div id="ganttContainerId">

</div>

<script>
    var dataJSON = {
        "chart": {
            "dateformat": "dd/mm/yyyy",
            "outputdateformat": "hh12:mn ampm",
            "caption": "Team - eos Market | eos Mobility",
            "canvasBorderAlpha": "30",
            "theme": "carbon"
        },
        "categories": [
            {
                "category": [
                    {
                        "start": "2017-01-01",
                        "end": "2017-04-30",
                        "label": "1st Quarter"
                    }
                ]
            },
            {
                "category": [
                    {
                        "start": "2017-01-01",
                        "end":"2017-01-31",
                        "label":"January"
                    }
                ]
            }
        ],
        "processes": {
            "fontsize": "12",
            "isbold": "1",
            "align": "left",
            "headertext": "Team Member",
            "headerfontsize": "14",
            "headervalign": "middle",
            "headeralign": "left",
            "process": [
                {
                    "label": "MOB-DEV-1",
                    "id": "MOBDEV1"
                },
                {
                    "label": "MOB-DEV-2",
                    "id": "MOBDEV2"
                },
                {
                    "label": "APK-DEV-1",
                    "id": "APKDEV1"
                },
                {
                    "label": "APK-DEV-2",
                    "id": "APKDEV2"
                }
                {
                    "label": "QA-1",
                    "id": "QA1"
                },
                {
                    "label": "QA-2",
                    "id":"QA2"
                }
            ]
        },
        "tasks": {
            "showlabels": "1",
            "task": [
                {
                    "processid": "EMP121",
                    "start": "08:00:00",
                    "end": "12:30:00",
                    "label": "Morning Shift"
                },
                {
                    "processid": "EMP121",
                    "start": "15:00:00",
                    "end": "19:30:00",
                    "label": "Evening Shift"
                },
                {
                    "processid": "EMP122",
                    "start": "10:00:00",
                    "end": "16:30:00",
                    "label": "Half Day"
                },
                {
                    "processid": "EMP123",
                    "start": "08:00:00",
                    "end": "12:00:00",
                    "label": "Morning Shift"
                },
                {
                    "processid": "EMP123",
                    "start": "15:00:00",
                    "end": "21:30:00",
                    "label": "Evening Shift"
                },
                {
                    "processid": "EMP124",
                    "start": "08:00:00",
                    "end": "20:30:00",
                    "label": "Full time support"
                },
                {
                    "processid": "EMP125",
                    "start": "10:00:00",
                    "end": "14:30:00",
                    "label": "Half Day"
                }
            ]
        }
    };
    new FusionCharts({
        "type" : "gantt",
        "width":$(document).width(),
        "height":"500",
        "dataFormat":"json",
        "dataSource": dataJSON
    }).render("ganttContainerId");
</script>