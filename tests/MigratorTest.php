<?php

namespace Starlit\Db\Migration\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Starlit\Db\Db;
use Starlit\Db\Migration\Migrator;

class MigratorTest extends TestCase
{
    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * @var MockObject
     */
    protected $mockDb;

    /**
     * @var string
     */
    protected $callbackInfo;

    public function setUp()
    {
        $this->mockDb = $this->createMock(Db::class);

        $infoCallback = function ($info) {
            $this->callbackInfo .= $info . "\n";
        };

        $migrationsDir = dirname(__FILE__) . '/migrations/';
        $this->migrator = new Migrator($migrationsDir, $this->mockDb, $infoCallback);
    }

    public function testGetLatestNumber(): void
    {
         $this->assertEquals(2, $this->migrator->getLatestNumber());
    }

    public function testGetCurrentNumber(): void
    {
        // Set current version to 1
        $migrationTableRows = [
            ['migration_number' => 1],
        ];
        $this->mockDb->expects($this->any())->method('fetchRows')->willReturn($migrationTableRows);

        $this->assertEquals(1, $this->migrator->getCurrentNumber());
    }

    public function testEmptyDb(): void
    {
        $migrationTableRows = [
            ['table1'],
            ['table2'],
        ];
        $this->mockDb->expects($this->any())->method('fetchRows')->willReturn($migrationTableRows);

        $this->mockDb->expects($this->at(2))->method('exec')->with($this->stringContains('DROP TABLE `table1`'));
        $this->mockDb->expects($this->at(3))->method('exec')->with($this->stringContains('DROP TABLE `table2`'));

        $this->migrator->emptyDb();
    }

    public function testMigrateDown(): void
    {
        // Set current version to 2
        $migrationTableRows = [
            ['migration_number' => 2],
        ];
        $this->mockDb->expects($this->any())->method('fetchRows')->willReturn($migrationTableRows);

        $this->migrator->migrate(1);

        $this->assertContains('Migrating down', $this->callbackInfo);
        $this->assertContains('Done!', $this->callbackInfo);
    }

    public function testMigrateUp(): void
    {
        // Set current version to 1
        $migrationTableRows = [
            ['migration_number' => 1],
        ];
        $this->mockDb->expects($this->any())->method('fetchRows')->willReturn($migrationTableRows);

        $this->migrator->migrate();

        $this->assertContains('Migrating up 2', $this->callbackInfo);
        $this->assertContains('Done!', $this->callbackInfo);
    }

    public function testMigrateAlreadyUpToDate(): void
    {
        // Set current version to 2
        $migrationTableRows = [
            ['migration_number' => 1],
            ['migration_number' => 2],
        ];
        $this->mockDb->expects($this->any())->method('fetchRows')->willReturn($migrationTableRows);

        $this->migrator->migrate();

        $this->assertContains('up to date ', $this->callbackInfo);
    }

    public function testMigrateMissing(): void
    {
        // Set current version to 2
        $migrationTableRows = [
            ['migration_number' => 2],
        ];
        $this->mockDb->expects($this->any())->method('fetchRows')->willReturn($migrationTableRows);

        $this->migrator->migrate();

        $this->assertContains('Migrating up 1', $this->callbackInfo);
    }

    public function testMigrateLatestVersionHigher(): void
    {
        // Set current version to 3
        $migrationTableRows = [
            ['migration_number' => 3],
        ];
        $this->mockDb->expects($this->any())->method('fetchRows')->willReturn($migrationTableRows);

        $this->expectException(\RuntimeException::class);
        $this->migrator->migrate();
    }

    public function testMigrateToNonExistantNumber(): void
    {
        // Set current version to 1
        $migrationTableRows = [
            ['migration_number' => 1],
        ];
        $this->mockDb->expects($this->any())->method('fetchRows')->willReturn($migrationTableRows);

        $this->expectException(\InvalidArgumentException::class);
        $this->migrator->migrate(3);
    }
}
