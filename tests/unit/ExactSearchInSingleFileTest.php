<?php
declare(strict_types=1);

use Boruta\Timebase\Common\Constant\SearchStrategyConstant;
use Boruta\Timebase\Timebase;
use PHPUnit\Framework\TestCase;

/**
 * Class ExactSearchInSingleFileTest
 */
final class ExactSearchInSingleFileTest extends TestCase
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
    public function testSearchForExactValueSingle(): void
    {
        $timebase = new Timebase(self::DATABASE_DIR);
        $currentTimestamp = time();

        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $timebase->insert()->storage(['test' . $i])->timestamp($currentTimestamp + $j)->set([
                    'test' => md5(uniqid('', true))
                ])->execute();
            }
            for ($j = 0; $j <= $i; $j++) {
                $result = $timebase->search()->storage(['test' . $i])->timestamp($currentTimestamp + $j)->strategy(SearchStrategyConstant::EXACT)->execute();
                $resultExact = $result->getExact();
                self::assertNotEmpty($resultExact);
                self::assertTrue(isset($resultExact[$currentTimestamp + $j]));
                self::assertCount(1, $resultExact[$currentTimestamp + $j]);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testSearchForExactValueMultiple(): void
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
            for ($j = 0; $j <= $i; $j++) {
                $result = $timebase->search()->storage(['test' . $i])->timestamp($currentTimestamp + $j)->strategy(SearchStrategyConstant::EXACT)->execute();
                $resultExact = $result->getExact();
                self::assertNotEmpty($resultExact);
                self::assertTrue(isset($resultExact[$currentTimestamp + $j]));
                self::assertCount(3, $resultExact[$currentTimestamp + $j]);
            }
        }
    }
}
