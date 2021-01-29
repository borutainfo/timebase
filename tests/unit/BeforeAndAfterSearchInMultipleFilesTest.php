<?php
declare(strict_types=1);

use Boruta\Timebase\Common\Constant\SearchStrategyConstant;
use Boruta\Timebase\Timebase;
use PHPUnit\Framework\TestCase;

/**
 * Class BeforeAndAfterSearchInMultipleFilesTest
 */
class BeforeAndAfterSearchInMultipleFilesTest extends TestCase
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
    public function testSearchForBeforeAndAfterValueSingle(): void
    {
        $timebase = new Timebase(self::DATABASE_DIR);
        $currentTimestamp = time();

        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp - 86400 + $j)->set([
                    'test' => md5(uniqid('', true))
                ])->execute();
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j)->set([
                    'test' => md5(uniqid('', true))
                ])->execute();
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + 86400 + $j)->set([
                    'test' => md5(uniqid('', true))
                ])->execute();
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + 86400 * 2 + $j)->set([
                    'test' => md5(uniqid('', true))
                ])->execute();
            }
            $result = $timebase->search()->storage(['test' . $i])->timestamp($currentTimestamp + 86400 - 1)->strategy(SearchStrategyConstant::EXACT)->execute();

            $resultBefore = $result->getBefore();
            self::assertNotEmpty($resultBefore);
            self::assertTrue(isset($resultBefore[$currentTimestamp + $i]));
            self::assertCount(1, $resultBefore[$currentTimestamp + $i]);

            $resultAfter = $result->getAfter();
            self::assertNotEmpty($resultAfter);
            self::assertTrue(isset($resultAfter[$currentTimestamp + 86400]));
            self::assertCount(1, $resultAfter[$currentTimestamp + 86400]);
        }
    }

    /**
     * @throws Exception
     */
    public function testSearchForBeforeAndAfterValueMultiple(): void
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
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + 86400 + $j)->set([
                    'test3' => md5(uniqid('', true))
                ])->execute();
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + 86400 + $j)->set([
                    'test4' => md5(uniqid('', true))
                ])->execute();
            }
            $result = $timebase->search()->storage(['test' . $i])->timestamp($currentTimestamp + 86400 - 1)->strategy(SearchStrategyConstant::EXACT)->execute();

            $resultBefore = $result->getBefore();
            self::assertNotEmpty($resultBefore);
            self::assertTrue(isset($resultBefore[$currentTimestamp + $i]));
            self::assertCount(2, $resultBefore[$currentTimestamp + $i]);

            $resultAfter = $result->getAfter();
            self::assertNotEmpty($resultAfter);
            self::assertTrue(isset($resultAfter[$currentTimestamp + 86400]));
            self::assertCount(2, $resultAfter[$currentTimestamp + 86400]);
        }
    }

    /**
     * @throws Exception
     */
    public function testSearchForBeforeAndAfterWithoutMainTimestampFile(): void
    {
        $timebase = new Timebase(self::DATABASE_DIR);
        $currentTimestamp = time();

        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp - 86400 + $j)->set([
                    'test' => md5(uniqid('', true))
                ])->execute();
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + 86400 + $j)->set([
                    'test' => md5(uniqid('', true))
                ])->execute();
            }
            $result = $timebase->search()->storage(['test' . $i])->timestamp($currentTimestamp)->strategy(SearchStrategyConstant::EXACT)->execute();

            $resultBefore = $result->getBefore();
            self::assertNotEmpty($resultBefore);
            self::assertTrue(isset($resultBefore[$currentTimestamp - 86400 + $i]));
            self::assertCount(1, $resultBefore[$currentTimestamp - 86400 + $i]);

            $resultAfter = $result->getAfter();
            self::assertNotEmpty($resultAfter);
            self::assertTrue(isset($resultAfter[$currentTimestamp + 86400]));
            self::assertCount(1, $resultAfter[$currentTimestamp + 86400]);
        }
    }
}