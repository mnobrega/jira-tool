<?php

use Phinx\Migration\AbstractMigration;

class IssuesHistoryTable extends AbstractMigration
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
        $jiraIssuesHistoryTbl = $this->table('jira_issues_histories');
        $jiraIssuesHistoryTbl->addColumn('issue_key','string')
            ->addColumn('history_datetime','datetime')
            ->addColumn('field','string',array('limit'=>50))
            ->addColumn('from_string','string',array('limit'=>50))
            ->addColumn('to_string','string',array('limit'=>50))
            ->addIndex('issue_key')
            ->setOptions([
                'encoding'  => 'utf8',
                'collation' => 'utf8_general_ci',
            ])
            ->addTimestamps()
            ->save();
    }
}
