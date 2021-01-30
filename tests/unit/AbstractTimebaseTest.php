<?php
declare(strict_types=1);

namespace Test;


use Boruta\Timebase\Timebase;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTimebaseTest
 */
abstract class AbstractTimebaseTest extends TestCase
{
    private const DATABASE_DIR = __DIR__ . '/../_data/';

    /**
     * @var Timebase
     */
    protected $timebase;
    /**
     * @var int
     */
    protected $timestamp;

    public function setUp(): void
    {
        $this->timebase = new Timebase(self::DATABASE_DIR);
        $this->timestamp = time();
        $this->cleanDatabase();
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->timebase = null;
        $this->timestamp = null;
        $this->cleanDatabase();
        parent::tearDown();
    }

    protected function cleanDatabase(): void
    {
        array_map('unlink', glob(self::DATABASE_DIR . '*/*.*'));
        array_map('rmdir', glob(self::DATABASE_DIR . 'test*'));
    }
}
