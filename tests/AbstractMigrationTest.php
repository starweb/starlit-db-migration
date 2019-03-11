<?php

namespace Starlit\Db\Migration\Tests;

use InvalidMigration;
use Migration1;
use Migration2WithDefaultDown;
use PHPUnit\Framework\TestCase;
use Starlit\Db\Db;
use Starlit\Db\Migration\AbstractMigration;

class AbstractMigrationTest extends TestCase
{
    /**
     * @var Migration1
     */
    protected $migration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockDb;

    public function setUp()
    {
        $this->mockDb = $this->createMock(Db::class);
        $this->migration = new Migration1($this->mockDb);
    }

    public function testGetNumber(): void
    {
        $this->assertEquals(1, $this->migration->getNumber());
    }

    public function testGetNumberException(): void
    {
        $migration = (new class($this->mockDb) extends AbstractMigration {
            public function up(): void
            {
            }
        });

        $this->expectException(\LogicException::class);
        $migration->getNumber();
    }

    public function testUp(): void
    {
        $this->mockDb->expects($this->once())->method('exec');
        $this->migration->up();
    }

    public function testDown(): void
    {
        $this->mockDb->expects($this->once())->method('exec');
        $this->migration->down();
    }

    public function testDownDefault(): void
    {
        $this->mockDb->expects($this->never())->method('exec');

        $this->migration = new Migration2WithDefaultDown($this->mockDb);
        $this->migration->down();
    }
}
