<?php

use Phinx\Migration\AbstractMigration;

class WikiTorrent extends AbstractMigration {
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
        $this
            ->table('wiki_torrents')
            ->addColumn('Name', 'string')
            ->addColumn('SubName', 'string')
            ->addColumn('Year', 'integer')
            ->addColumn('ReleaseType', 'integer')
            ->removeColumn('IMDBRating')
            ->removeColumn('DoubanRating')
            ->removeColumn('Duration')
            ->removeColumn('ReleaseDate')
            ->removeColumn('Region')
            ->removeColumn('Language')
            ->removeColumn('RTRating')
            ->removeColumn('DoubanVote')
            ->removeColumn('IMDBVote')
            ->save();
        $this->execute("update wiki_torrents as dst, torrents_group as src 
            set 
                dst.Year = src.Year, 
                dst.Name = src.Name, 
                dst.SubName = src.SubName,
                dst.ReleaseType = src.ReleaseType
            where dst.RevisionID = src.RevisionID and src.RevisionID <> 0");
    }
}
