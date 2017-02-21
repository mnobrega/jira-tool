<?php

use Phinx\Migration\AbstractMigration;

class TeamsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $teamsTbl = $this->table('app_teams');
        $teamsTbl->addColumn('key','string',array('limit'=>50))
            ->addColumn('name','string',array('limit'=>100))
            ->addIndex('key')
            ->addTimestamps()
            ->save();

        $teamsPersonsTbl = $this->table('app_teams_persons');
        $teamsPersonsTbl->addColumn('team_key','string',array('limit'=>50))
            ->addColumn('person_username','string',array('limit'=>50))
            ->addColumn('person_allocated_hours_per_day','float')
            ->addIndex(array('team_key','person_username'))
            ->addTimestamps()
            ->save();
    }
}
