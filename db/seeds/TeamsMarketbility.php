<?php

use Phinx\Seed\AbstractSeed;

class TeamsMarketbility extends AbstractSeed
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
        $teamsData = array(
            array(
                'key'=>'MM_EMP_DEV',
                'name'=>'Marketbility Empark Dev',
            ),
            array(
                'key'=>'MM_EMP_QA',
                'name'=>'Marketbility Empark QA',
            ),
            array(
                'key'=>'MM_EMP_PMA',
                'name'=>'Marketbility Empark Product Management',
            ),
            array(
                'key'=>'MM_PM_DEV',
                'name'=>'Marketbility Premium Minds Dev',
            ),
            array(
                'key'=>'MM_PM_QA',
                'name'=>'Marketbility Premium Minds QA'
            )
        );

        $teamsPersonsData = array(
            array(
                'team_key'=>'MM_EMP_PMA',
                'person_username'=>'mnobrega',
                'person_allocated_hours_per_day'=>7,
            ),
            array(
                'team_key'=>'MM_EMP_DEV',
                'person_username'=>'mnobrega',
                'person_allocated_hours_per_day'=>1,
            ),
            array(
                'team_key'=>'MM_EMP_DEV',
                'person_username'=>'asoares',
                'person_allocated_hours_per_day'=>8,
            ),
            array(
                'team_key'=>'MM_EMP_DEV',
                'person_username'=>'sguerreiro',
                'person_allocated_hours_per_day'=>8,
            ),
            array(
                'team_key'=>'MM_EMP_QA',
                'person_username'=>'sottaviani',
                'person_allocated_hours_per_day'=>4,
            ),
            array(
                'team_key'=>'MM_EMP_QA',
                'person_username'=>'mmatos',
                'person_allocated_hours_per_day'=>4
            ),
            array(
                'team_key'=>'MM_PM_DEV',
                'person_username'=>'lgoncalves',
                'person_allocated_hours_per_day'=>8,
            ),
            array(
                'team_key'=>'MM_PM_QA',
                'person_username'=>'tcarreira',
                'person_allocated_hours_per_day'=>4
            )
        );

        $this->execute("DELETE FROM app_teams");
        $this->execute("DELETE FROM app_teams_persons");
        $teamsTbl = $this->table('app_teams');
        $teamsPersonsTbl = $this->table('app_teams_persons');
        $teamsTbl->insert($teamsData)
            ->save();
        $teamsPersonsTbl->insert($teamsPersonsData)
            ->save();
    }
}
