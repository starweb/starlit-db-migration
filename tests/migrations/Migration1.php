<?php

use Starlit\Db\Migration\AbstractMigration;

class Migration1 extends AbstractMigration
{
    public function up(): void
    {
        $this->db->exec('SOME SQL');
    }

    public function down(): void
    {
        $this->db->exec('SOME SQL');
    }
}
