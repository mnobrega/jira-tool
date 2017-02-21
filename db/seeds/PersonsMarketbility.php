<?php

use Phinx\Seed\AbstractSeed;

class PersonsMarketbility extends AbstractSeed
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
        $data = array (
            array(
                'username'=>'mnobrega',
                'full_name'=>'Márcio Nóbrega',
                'jira_username'=>'mnobrega',
            ),
            array(
                'username'=>'asoares',
                'full_name'=>'André Soares',
                'jira_username'=>'aoares',
            ),
            array(
                'username'=>'sguerreiro',
                'full_name'=>'Sara Guerreiro',
                'jira_username'=>'sguerreiro',
            ),
            array(
                'username'=>'lgoncalves',
                'full_name'=>'Luís Gonçalves',
                'jira_username'=>'lgoncalves',
            )
        );

        $personsTbl = $this->table('app_persons');
        $personsTbl->truncate();
        $personsTbl->insert($data)
            ->save();
    }
}
