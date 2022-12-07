<?php

use Phinx\Migration\AbstractMigration;

class Ipv6 extends AbstractMigration {
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
        $this->table('ip_bans')
            ->renameColumn('FromIP', 'FromIP_Old')
            ->renameColumn('ToIP', 'ToIP_Old')
            ->addColumn('FromIP', 'varbinary', ['length' => 16])
            ->addColumn('ToIP', 'varbinary', ['length' => 16])
            ->save();
        $this->execute('UPDATE ip_bans SET FromIP=INET6_ATON(INET_NTOA(FromIP_Old)), ToIP=INET6_ATON(INET_NTOA(ToIP_Old));');

        $this->table('geoip_country')
            ->renameColumn('StartIP', 'StartIP_Old')
            ->renameColumn('EndIP', 'EndIP_Old')
            ->addColumn('StartIP', 'varbinary', ['length' => 16])
            ->addColumn('EndIP', 'varbinary', ['length' => 16])
            ->save();
        $this->execute('UPDATE geoip_country SET StartIP=INET6_ATON(INET_NTOA(StartIP_Old)), EndIP=INET6_ATON(INET_NTOA(EndIP_Old));');

        $this->table('login_attempts')
            ->changeColumn('IP', 'string', ['length' => 45])
            ->save();
        $this->table('users_history_ips')
            ->changeColumn('IP', 'string', ['length' => 45])
            ->save();
        $this->table('users_main')
            ->changeColumn('IP', 'string', ['length' => 45])
            ->save();
        $this->table('users_history_ips')
            ->changeColumn('IP', 'string', ['length' => 45])
            ->save();
        $this->table('users_history_emails')
            ->changeColumn('IP', 'string', ['length' => 45])
            ->save();
        $this->table('users_history_passkeys')
            ->changeColumn('ChangerIP', 'string', ['length' => 45])
            ->save();
        $this->table('users_history_passkeys')
            ->changeColumn('ChangerIP', 'string', ['length' => 45])
            ->save();
        $this->table('users_sessions')
            ->changeColumn('IP', 'string', ['length' => 45])
            ->save();
        $this->table('apply_user')
            ->changeColumn('IP', 'string', ['length' => 45])
            ->save();
        $this->table('ip_lock')
            ->changeColumn('IPs', 'string', ['length' => 255])
            ->save();
        $this->table('register_apply_link')
            ->changeColumn('IP', 'string', ['length' => 45])
            ->save();
        $this->table('users_enable_requests')
            ->changeColumn('IP', 'string', ['length' => 45])
            ->save();
    }
}
