<?php
declare(strict_types=1);

use Boruta\Timebase\Timebase;
use PHPUnit\Framework\TestCase;

/**
 * Class BeforeAndAfterSearchInSingleFileTest
 */
final class BeforeAndAfterSearchInSingleFileTest extends TestCase
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
    public function testSearchForBeforeValueSingle(): void
    {
        $timebase = new Timebase(self::DATABASE_DIR);
        $currentTimestamp = time();

        for ($i = 1; $i <= 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j * 2)->set([
                    'test' => md5(uniqid('', true))
                ])->execute();
            }
            for ($j = 1; $j <= $i; $j++) {
                $result = $timebase->query()->storage(['test' . $i])->timestamp($currentTimestamp + $j * 2 - 1)->exact()->execute();

                $resultBefore = $result->getBefore();
                self::assertNotEmpty($resultBefore);
                self::assertTrue(isset($resultBefore[$currentTimestamp + ($j - 1) * 2]));
                self::assertCount(1, $resultBefore[$currentTimestamp + ($j - 1) * 2]);

                $resultAfter = $result->getAfter();
                self::assertNotEmpty($resultAfter);
                self::assertTrue(isset($resultAfter[$currentTimestamp + $j * 2]));
                self::assertCount(1, $resultAfter[$currentTimestamp + $j * 2]);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testSearchForBeforeValueMultiple(): void
    {
        $timebase = new Timebase(self::DATABASE_DIR);
        $currentTimestamp = time();

        for ($i = 1; $i <= 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j * 2)->set([
                    'test1' => md5(uniqid('', true))
                ])->execute();
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j * 2)->set([
                    'test2' => md5(uniqid('', true))
                ])->execute();
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j * 2)->set([
                    'test3' => md5(uniqid('', true))
                ])->execute();
            }
            for ($j = 1; $j <= $i; $j++) {
                $result = $timebase->query()->storage(['test' . $i])->timestamp($currentTimestamp + $j * 2 - 1)->exact()->execute();

                $resultBefore = $result->getBefore();
                self::assertNotEmpty($resultBefore);
                self::assertTrue(isset($resultBefore[$currentTimestamp + ($j - 1) * 2]));
                self::assertCount(3, $resultBefore[$currentTimestamp + ($j - 1) * 2]);

                $resultAfter = $result->getAfter();
                self::assertNotEmpty($resultAfter);
                self::assertTrue(isset($resultAfter[$currentTimestamp + $j * 2]));
                self::assertCount(3, $resultAfter[$currentTimestamp + $j * 2]);
            }
        }
    }
}
