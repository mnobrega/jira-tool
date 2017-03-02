<?php

use Phinx\Migration\AbstractMigration;

class ProjectsTable extends AbstractMigration
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
        $projectsTbl = $this->table('app_projects');
        $projectsTbl->addColumn('name','string',array('limit'=>100))
            ->addColumn('team_key','string',array('limit'=>50))
            ->addColumn('jira_project_key','string',array('limit'=>10))
            ->addColumn('team_allocated_percentage','float')
            ->addColumn('issues_allocation_criteria_sql','text')
            ->addColumn('hidden','boolean',array('default'=>true))
            ->addTimestamps()
            ->save();
    }
}
