<?php

use Starlit\Db\Migration\AbstractMigration;

class Migration2WithDefaultDown extends AbstractMigration
{
    public function up(): void
    {
        $this->db->exec('SOME SQL');
    }
}
