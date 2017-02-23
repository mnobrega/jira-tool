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
                'key'=>'MM',
                'name'=>'Marketbility Team',
            ),
        );

        $teamsPersonsData = array(
            array(
                'team_key'=>'MM',
                'person_username'=>'mnobrega',
                'person_allocated_hours_per_day'=>8,
            ),
            array(
                'team_key'=>'MM',
                'person_username'=>'asoares',
                'person_allocated_hours_per_day'=>8,
            ),
            array(
                'team_key'=>'MM',
                'person_username'=>'sguerreiro',
                'person_allocated_hours_per_day'=>0,
            ),
            array(
                'team_key'=>'MM',
                'person_username'=>'lgoncalves',
                'person_allocated_hours_per_day'=>8,
            ),
            array(
                'team_key'=>'MM',
                'person_username'=>'sottaviani',
                'person_allocated_hours_per_day'=>0,
            ),
            array(
                'team_key'=>'MM',
                'person_username'=>'mmatos',
                'person_allocated_hours_per_day'=>0
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
