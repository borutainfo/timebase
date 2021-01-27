<?php
declare(strict_types=1);

use Boruta\Timebase\Timebase;
use PHPUnit\Framework\TestCase;

/**
 * Class AfterSearchInSingleFileTest
 */
final class AfterSearchInSingleFileTest extends TestCase
{
    private const DATABASE_DIR = __DIR__ . '/../database/';

    public function setUp(): void
    {
        array_map('unlink', glob(self::DATABASE_DIR . '*/*.*'));
        array_map('rmdir', glob(self::DATABASE_DIR . 'test*'));
        parent::setUp();
    }

    public function tearDown(): void
    {
        array_map('unlink', glob(self::DATABASE_DIR . '*/*.*'));
        array_map('rmdir', glob(self::DATABASE_DIR . 'test*'));
        parent::tearDown();
    }

    /**
     * @throws Exception
     */
    public function testSearchForAfterValueSingle(): void
    {
        $timebase = new Timebase(self::DATABASE_DIR);
        $currentTimestamp = time();

        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j)->set([
                    'test' => md5(uniqid('', true))
                ])->execute();
            }
            $result = $timebase->query()->storage(['test' . $i])->timestamp($currentTimestamp - 1)->exact()->execute();
            $resultAfter = $result->getAfter();
            self::assertNotEmpty($resultAfter);
            self::assertTrue(isset($resultAfter[$currentTimestamp]));
            self::assertCount(1, $resultAfter[$currentTimestamp]);
        }
    }

    /**
     * @throws Exception
     */
    public function testSearchForAfterValueMultiple(): void
    {
        $timebase = new Timebase(self::DATABASE_DIR);
        $currentTimestamp = time();

        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j)->set([
                    'test1' => md5(uniqid('', true))
                ])->execute();
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j)->set([
                    'test2' => md5(uniqid('', true))
                ])->execute();
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j)->set([
                    'test3' => md5(uniqid('', true))
                ])->execute();
            }
            $result = $timebase->query()->storage(['test' . $i])->timestamp($currentTimestamp - 1)->exact()->execute();
            $resultAfter = $result->getAfter();
            self::assertNotEmpty($resultAfter);
            self::assertTrue(isset($resultAfter[$currentTimestamp]));
            self::assertCount(3, $resultAfter[$currentTimestamp]);
        }
    }
}
