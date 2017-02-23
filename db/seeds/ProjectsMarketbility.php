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
                'name'=>'MOB-EPK-DEV',
                'team_key'=>'MM',
                'jira_project_key'=>'MOB',
                'team_allocated_percentage'=>37.5,
                'issues_allocation_criteria_sql'=>'project_key="MOB" AND issue_type="Project-EPK"',
            ),
            array(
                'name'=>'MOB-EPK-TSK',
                'team_key'=>'MM',
                'jira_project_key'=>'MOB',
                'team_allocated_percentage'=>12.5,
                'issues_allocation_criteria_sql'=>'project_key="MOB" AND issue_type NOT IN ("Project-EPK","Project-PM")',
            ),
            array(
                'name'=>'MOB-PM-DEV',
                'team_key'=>'MM',
                'jira_project_key'=>'MOB',
                'team_allocated_percentage'=>12.5,
                'issues_allocation_criteria_sql'=>'project_key="MOB" AND issue_type="Project-PM"',
            ),
            array(
                'name'=>'APK-EPK-DEV',
                'team_key'=>'MM',
                'jira_project_key'=>'APK',
                'team_allocated_percentage'=>22.5,
                'issues_allocation_criteria_sql'=>'project_key="APK" AND issue_type="Project-EPK"',
            ),
            array(
                'name'=>'APK-EPK-TSK',
                'team_key'=>'MM',
                'jira_project_key'=>'APK',
                'team_allocated_percentage'=>2.5,
                'issues_allocation_criteria_sql'=>'project_key="APK" AND issue_type NOT IN ("Project-EPK","Project-PM")',
            ),
            array(
                'name'=>'APK-PM-DEV',
                'team_key'=>'MM',
                'jira_project_key'=>'APK',
                'team_allocated_percentage'=>12.5,
                'issues_allocation_criteria_sql'=>'project_key="APK" AND issue_type="Project-PM"',
            ),
        );

        $this->execute("DELETE FROM app_projects");
        $projectsTbl = $this->table('app_projects');
        $projectsTbl->insert($projectsData)
            ->save();
    }
}
