<?php

use Phinx\Migration\AbstractMigration;

class IssuesTable extends AbstractMigration
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
        $jiraIssuesTbl = $this->table('jira_issues');
        $jiraIssuesTbl->addColumn('issue_key','string',array('limit'=>10))
            ->addColumn('issue_status','string',array('limit'=>20))
            ->addColumn('summary','text')
            ->addColumn('release_summary','text')
            ->addColumn('priority','integer')
            ->addColumn('issue_type','string')
            ->addColumn('project','string')
            ->addColumn('original_estimate','integer',array('null'=>true))
            ->addColumn('remaining_estimate','integer',array('null'=>true))
            ->addColumn('release_date','date',array('null'=>true))
            ->addColumn('labels','text',array('null'=>true))
            ->addColumn('assignee','string',array('null'=>true))
            ->addColumn('created','timestamp',array('default'=>'CURRENT_TIMESTAMP'))
            ->addIndex(array('issue_key'),array('unique'=>true))
            ->setOptions([
                'encoding'  => 'utf8',
                'collation' => 'utf8_general_ci',
            ])
            ->save();
    }

}
