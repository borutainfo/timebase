<?php
/** @noinspection DuplicatedCode */
declare(strict_types=1);

namespace Test;


use Boruta\Timebase\Common\Constant\SearchStrategyConstant;
use Boruta\Timebase\Common\Exception\DataReadingException;
use Exception;

/**
 * Class SearchStrategyExactTest
 * @package Test
 */
final class SearchStrategyExactTest extends AbstractTimebaseTest
{
    /**
     * @throws Exception
     */
    public function testSuccessfulSingleFromSingle(): void
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j)->set('ts:' . ($this->timestamp + $j))->execute();
            }
            for ($j = 0; $j <= $i; $j++) {
                $result = $this->timebase->search()->storage(['test' . $i])->timestamp($this->timestamp + $j)->strategy(SearchStrategyConstant::EXACT)->execute();
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
    public function testSuccessfulMultipleFromSingle(): void
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j)->set('ts:' . ($this->timestamp + $j))->execute();
            }
            for ($j = 0; $j <= $i; $j++) {
                $result = $this->timebase->search()->storage(['test' . $i])->timestamp($this->timestamp + $j)->strategy(SearchStrategyConstant::EXACT)->all()->execute();
                self::assertNotEmpty($result);
                self::assertIsArray($result);
                self::assertEquals($result['timestamp'], $this->timestamp + $j);
                self::assertEquals($result['value'], 'ts:' . ($this->timestamp + $j));
                self::assertTrue(isset($result['all']));
                self::assertCount(1, $result['all']);
                self::assertEquals($result['all'][0], 'ts:' . ($this->timestamp + $j));
            }

        }
    }

    /**
     * @throws Exception
     */
    public function testSuccessfulSingleFromMultiple(): void
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j)->set('ts:' . ($this->timestamp + $j))->execute();
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j)->set('ts:' . ($this->timestamp + $j + 1))->execute();
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j)->set('ts:' . ($this->timestamp + $j + 2))->execute();
            }
            for ($j = 0; $j <= $i; $j++) {
                $result = $this->timebase->search()->storage(['test' . $i])->timestamp($this->timestamp + $j)->strategy(SearchStrategyConstant::EXACT)->execute();
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
    public function testSuccessfulMultipleFromMultiple(): void
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j)->set('ts:' . ($this->timestamp + $j))->execute();
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j)->set('ts:' . ($this->timestamp + $j + 1))->execute();
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j)->set('ts:' . ($this->timestamp + $j + 2))->execute();
            }
            for ($j = 0; $j <= $i; $j++) {
                $result = $this->timebase->search()->storage(['test' . $i])->timestamp($this->timestamp + $j)->strategy(SearchStrategyConstant::EXACT)->all()->execute();
                self::assertNotEmpty($result);
                self::assertIsArray($result);
                self::assertEquals($result['timestamp'], $this->timestamp + $j);
                self::assertEquals($result['value'], 'ts:' . ($this->timestamp + $j));
                self::assertTrue(isset($result['all']));
                self::assertCount(3, $result['all']);
                self::assertEquals($result['all'][0], 'ts:' . ($this->timestamp + $j));
                self::assertEquals($result['all'][1], 'ts:' . ($this->timestamp + $j + 1));
                self::assertEquals($result['all'][2], 'ts:' . ($this->timestamp + $j + 2));
            }

        }
    }

    /**
     * @throws Exception
     */
    public function testUnsuccessfulTimestampNotFoundInCorrectFile(): void
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $this->timebase->insert()->storage(['test' . $i])->timestamp($this->timestamp + $j * 2)->set('ts:' . ($this->timestamp + $j))->execute();
            }
            for ($j = 0; $j <= $i; $j++) {
                $result = $this->timebase->search()->storage(['test' . $i])->timestamp($this->timestamp + $j * 2 - 1)->strategy(SearchStrategyConstant::EXACT)->execute();
                self::assertNull($result);
            }

        }
    }

    /**
     * @throws Exception
     */
    public function testUnsuccessfulNotFoundCorrectFile(): void
    {
        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp + 86400)->set('ts:' . ($this->timestamp))->execute();
        $this->timebase->insert()->storage(['test'])->timestamp($this->timestamp - 86400)->set('ts:' . ($this->timestamp))->execute();
        $result = $this->timebase->search()->storage(['test'])->timestamp($this->timestamp)->strategy(SearchStrategyConstant::EXACT)->execute();
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testUnsuccessfulNoDatabase(): void
    {
        $this->expectException(DataReadingException::class);
        $this->timebase->search()->storage(['test'])->timestamp($this->timestamp)->strategy(SearchStrategyConstant::EXACT)->execute();
    }
}
