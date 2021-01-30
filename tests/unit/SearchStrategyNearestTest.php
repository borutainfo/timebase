<?php
/** @noinspection DuplicatedCode */
declare(strict_types=1);

namespace Test;


use Boruta\Timebase\Common\Constant\SearchStrategyConstant;
use Boruta\Timebase\Common\Exception\DataReadingException;
use Exception;

/**
 * Class SearchStrategyNearestTest
 * @package Test
 */
final class SearchStrategyNearestTest extends AbstractTimebaseTest
{
    /**
     * @throws Exception
     */
    public function testSuccessfulExact(): void
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j)->set('ts:' . ($this->timestamp + $j))->execute();
            }
            for ($j = 0; $j <= $i; $j++) {
                $result = $this->timebase->search()->storage(['test' . $i])->timestamp($this->timestamp + $j)->execute();
                self::assertNotEmpty($result);
                self::assertIsArray($result);
                self::assertEquals($result['timestamp'], $this->timestamp + $j);
                self::assertEquals($result['value'], 'ts:' . ($this->timestamp + $j));
                self::assertFalse(isset($result['all']));
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testSuccessfulEarlier(): void
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j * 4)->set('ts:' . ($this->timestamp + $j * 4))->execute();
            }
            for ($j = 0; $j <= $i; $j++) {
                $result = $this->timebase->search()->storage(['test' . $i])->timestamp($this->timestamp + $j * 4 + 1)->execute();
                self::assertNotEmpty($result);
                self::assertIsArray($result);
                self::assertEquals($result['timestamp'], $this->timestamp + $j * 4);
                self::assertEquals($result['value'], 'ts:' . ($this->timestamp + $j * 4));
                self::assertFalse(isset($result['all']));
            }

        }
    }

    /**
     * @throws Exception
     */
    public function testSuccessfulLater(): void
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j * 4)->set('ts:' . ($this->timestamp + $j * 4))->execute();
            }
            for ($j = 0; $j <= $i; $j++) {
                $result = $this->timebase->search()->storage(['test' . $i])->timestamp($this->timestamp + $j * 4 - 1)->execute();
                self::assertNotEmpty($result);
                self::assertIsArray($result);
                self::assertEquals($result['timestamp'], $this->timestamp + $j * 4);
                self::assertEquals($result['value'], 'ts:' . ($this->timestamp + $j * 4));
                self::assertFalse(isset($result['all']));
            }

        }
    }

    /**
     * @throws Exception
     */
    public function testSuccessfulFoundInLaterFile(): void
    {
        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp + 86400)->set('ts:' . ($this->timestamp + 86400))->execute();
        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp + 86400)->set('ts:' . ($this->timestamp + 86400 + 1))->execute();
        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp - 86400 * 3)->set('ts:' . ($this->timestamp - 86400 * 3))->execute();
        $result = $this->timebase->search()->storage(['test'])->timestamp($this->timestamp)->execute();
        self::assertNotEmpty($result);
        self::assertIsArray($result);
        self::assertEquals($result['timestamp'], $this->timestamp + 86400);
        self::assertEquals($result['value'], 'ts:' . ($this->timestamp + 86400));
        self::assertFalse(isset($result['all']));

        $result = $this->timebase->search()->storage(['test'])->timestamp($this->timestamp)->all()->execute();
        self::assertNotEmpty($result);
        self::assertIsArray($result);
        self::assertEquals($result['timestamp'], $this->timestamp + 86400);
        self::assertEquals($result['value'], 'ts:' . ($this->timestamp + 86400));
        self::assertTrue(isset($result['all']));
        self::assertCount(2, $result['all']);
        self::assertEquals($result['all'][0], 'ts:' . ($this->timestamp + 86400));
        self::assertEquals($result['all'][1], 'ts:' . ($this->timestamp + 86400 + 1));

        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp - 86400)->set('ts:' . ($this->timestamp - 86400))->execute();
        $result = $this->timebase->search()->storage(['test'])->timestamp($this->timestamp)->execute();
        self::assertNotEmpty($result);
        self::assertIsArray($result);
        self::assertNotEquals($result['timestamp'], $this->timestamp + 86400);
        self::assertNotEquals($result['value'], 'ts:' . ($this->timestamp + 86400));
        self::assertFalse(isset($result['all']));
    }

    /**
     * @throws Exception
     */
    public function testSuccessfulFoundInEarlierFile(): void
    {
        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp - 86400)->set('ts:' . ($this->timestamp - 86400))->execute();
        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp - 86400)->set('ts:' . ($this->timestamp - 86400 - 1))->execute();
        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp + 86400 * 3)->set('ts:' . ($this->timestamp + 86400 * 3))->execute();
        $result = $this->timebase->search()->storage(['test'])->timestamp($this->timestamp)->execute();
        self::assertNotEmpty($result);
        self::assertIsArray($result);
        self::assertEquals($result['timestamp'], $this->timestamp - 86400);
        self::assertEquals($result['value'], 'ts:' . ($this->timestamp - 86400));
        self::assertFalse(isset($result['all']));

        $result = $this->timebase->search()->storage(['test'])->timestamp($this->timestamp)->all()->execute();
        self::assertNotEmpty($result);
        self::assertIsArray($result);
        self::assertEquals($result['timestamp'], $this->timestamp - 86400);
        self::assertEquals($result['value'], 'ts:' . ($this->timestamp - 86400));
        self::assertTrue(isset($result['all']));
        self::assertCount(2, $result['all']);
        self::assertEquals($result['all'][0], 'ts:' . ($this->timestamp - 86400));
        self::assertEquals($result['all'][1], 'ts:' . ($this->timestamp - 86400 - 1));

        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp + 86400)->set('ts:' . ($this->timestamp + 86400))->execute();
        $result = $this->timebase->search()->storage(['test'])->timestamp($this->timestamp)->execute();
        self::assertNotEmpty($result);
        self::assertIsArray($result);
        self::assertEquals($result['timestamp'], $this->timestamp - 86400);
        self::assertEquals($result['value'], 'ts:' . ($this->timestamp - 86400));
        self::assertFalse(isset($result['all']));
    }

    /**
     * @throws Exception
     */
    public function testUnsuccessfulNoDatabase(): void
    {
        $this->expectException(DataReadingException::class);
        $this->timebase->search()->storage(['test'])->timestamp($this->timestamp)->strategy(SearchStrategyConstant::EARLIER)->execute();
    }
}
