<?php

require_once(dirname(__FILE__)."/../../" . "config.php");
require_once(DIR_SERVICES."jira_service.php");
require_once(DIR_SERVICES . "daos/dao_jira.php");

use Phinx\Seed\AbstractSeed;

class ProjectsMarketbility extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $projectsData = array(

            // MM_EMP_DEV (Andre, Soares, Marcio)
            array(
                'name'=>'MOB-EMP-DEV',
                'team_key'=>'MM_EMP_DEV',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MOBILITY,
                'team_allocated_percentage'=>50,
                'hidden'=>0,
            ),
            array(
                'name'=>'MOB-EMP-TSK',
                'team_key'=>'MM_EMP_DEV',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MOBILITY,
                'team_allocated_percentage'=>10,
                'hidden'=>0,
            ),
            array(
                'name'=>'APK-EMP-DEV',
                'team_key'=>'MM_EMP_DEV',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MARKET,
                'team_allocated_percentage'=>30,
                'hidden'=>0,
            ),
            array(
                'name'=>'APK-EMP-TSK',
                'team_key'=>'MM_EMP_DEV',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MARKET,
                'team_allocated_percentage'=>10,
                'hidden'=>0,
            ),

            // MM_EMP_QA (Sandro,Miguel)
            array(
                'name'=>'MOB-EMP-TSK',
                'team_key'=>'MM_EMP_QA',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MOBILITY,
                'team_allocated_percentage'=>50,
                'hidden'=>0,
            ),
            array(
                'name'=>'APK-EMP-TSK',
                'team_key'=>'MM_EMP_QA',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MARKET,
                'team_allocated_percentage'=>50,
                'hidden'=>0,
            ),

            // PM DEV (Luis)
            array(
                'name'=>'MOB-PM-DEV',
                'team_key'=>'MM_PM_DEV',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MOBILITY,
                'team_allocated_percentage'=>50,
                'hidden'=>1,
            ),
            array(
                'name'=>'APK-PM-DEV',
                'team_key'=>'MM_PM_DEV',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MARKET,
                'team_allocated_percentage'=>50,
                'hidden'=>1,
            ),


            // PM QA (Tiago)
            array(
                'name'=>'APK-PM-DEV',
                'team_key'=>'MM_PM_QA',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MARKET,
                'team_allocated_percentage'=>50,
                'hidden'=>1,
            ),
            array(
                'name'=>'MOB-PM-DEV',
                'team_key'=>'MM_PM_QA',
                'jira_project_key'=>DAOJIRAIssues::PROJECT_MOBILITY,
                'team_allocated_percentage'=>50,
                'hidden'=>1,
            ),
        );

        $this->execute("DELETE FROM app_projects");
        $projectsTbl = $this->table('app_projects');
        $projectsTbl->insert($projectsData)
            ->save();
    }
}
