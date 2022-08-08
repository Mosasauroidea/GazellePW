<?php

use Phinx\Migration\AbstractMigration;

class ArtistRefactor extends AbstractMigration {
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
            ->table('artists_group')
            ->addColumn('Body', 'text')
            ->addColumn('Image', 'string')
            ->addColumn('IMDBID', 'string')
            ->addColumn('SubName', 'string')
            ->addColumn('Birthday', 'string')
            ->addColumn('PlaceOfBirth', 'string')
            ->addIndex('IMDBID')
            ->save();
        $this->table('wiki_artists')->renameColumn('ChineseName', 'SubName')->addColumn('Name', 'string')->save();
        $this->execute("update artists_group as dst, wiki_artists as src 
            set 
                dst.Body = src.Body, 
                dst.Image= src.Image, 
                dst.IMDBID= src.IMDBID, 
                dst.SubName = src.SubName, 
                dst.Birthday= src.Birthday, 
                dst.PlaceOfBirth = src.PlaceOfBirth
            where dst.RevisionID = src.RevisionID");
        $this->execute("update artists_group as src, wiki_artists as dst 
            set 
                dst.Name = src.Name
            where dst.RevisionID = src.RevisionID");
    }
}
