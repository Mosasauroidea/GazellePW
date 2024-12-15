<?php

use Phinx\Migration\AbstractMigration;

class ReportsMessage extends AbstractMigration {
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
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change() {
        $this->table('reports_message')
            ->addColumn('ReportID', 'integer')
            ->addColumn('SentDate', 'datetime')
            ->addColumn('SenderID', 'integer')
            ->addColumn('Body', 'text')
            ->addIndex(['ReportID'])
            ->save();
        $builder = $this->getQueryBuilder('select');
        $statement = $builder->select('*')->from('reportsv2')->execute();
        foreach ($statement->fetchAll('assoc') as $line) {
            if (empty($line['UploaderReply'])) {
                continue;
            }

            $builder = $this->getQueryBuilder('select');
            $statement = $builder->select('UserID')->where(['ID' => $line['TorrentID']])->from('torrents')->execute();
            $Torrent = $statement->fetch('assoc');
            $UploaderID = $Torrent['UserID'];
            if (empty($UploaderID)) {
                continue;
            }
            $builder = $this->getQueryBuilder('insert');
            $builder
                ->insert(['ReportID', 'SentDate', 'SenderID', 'Body'])
                ->into('reports_message')
                ->values(['ReportID' =>  $line['ID'], 'SentDate' => $line['ReplyTime'], 'SenderID' => $UploaderID, 'Body' => $line['UploaderReply']])
                ->execute();
        }
    }
}
