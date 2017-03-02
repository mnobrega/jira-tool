<?php

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
            array(
                'name'=>'MOB-EMP-DEV',
                'team_key'=>'MM',
                'jira_project_key'=>'MOB',
                'team_allocated_percentage'=>37.5,
                'issues_allocation_criteria_sql'=>'project_key="MOB" AND issue_type NOT IN ("Task","Bug","Spike")',
            ),
            array(
                'name'=>'MOB-EMP-TSK',
                'team_key'=>'MM',
                'jira_project_key'=>'MOB',
                'team_allocated_percentage'=>12.5,
                'issues_allocation_criteria_sql'=>'project_key="MOB" AND issue_type IN ("Task","Bug","Spike")',
            ),
            array(
                'name'=>'MOB-PM-DEV',
                'team_key'=>'MM',
                'jira_project_key'=>'MOB',
                'team_allocated_percentage'=>12.5,
                'issues_allocation_criteria_sql'=>'project_key="MOB" AND emp_it_requestor IS NULL',
            ),
            array(
                'name'=>'APK-EMP-DEV',
                'team_key'=>'MM',
                'jira_project_key'=>'APK',
                'team_allocated_percentage'=>17.5,
                'issues_allocation_criteria_sql'=>'project_key="APK" AND issue_type NOT IN ("Task","Bug","Spike")',
            ),
            array(
                'name'=>'APK-EMP-TSK',
                'team_key'=>'MM',
                'jira_project_key'=>'APK',
                'team_allocated_percentage'=>7.5,
                'issues_allocation_criteria_sql'=>'project_key="APK" AND issue_type IN ("Task","Bug","Spike")',
            ),
            array(
                'name'=>'APK-PM-DEV',
                'team_key'=>'MM',
                'jira_project_key'=>'APK',
                'team_allocated_percentage'=>12.5,
                'issues_allocation_criteria_sql'=>'project_key="APK" AND emp_it_requestor IS NULL',
            ),
        );

        $this->execute("DELETE FROM app_projects");
        $projectsTbl = $this->table('app_projects');
        $projectsTbl->insert($projectsData)
            ->save();
    }
}
