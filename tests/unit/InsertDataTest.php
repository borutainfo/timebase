<?php
/** @noinspection DuplicatedCode */
declare(strict_types=1);

namespace Test;


use Boruta\Timebase\Filesystem\Constant\ExtensionConstant;
use Boruta\Timebase\Filesystem\Helper\LineTimestampHelper;
use DateTime;
use Exception;
use SplFileObject;

/**
 * Class InsertDataTest
 * @package Test
 */
final class InsertDataTest extends AbstractTimebaseTest
{
    /**
     * @throws Exception
     */
    public function testInsertToTheBeginning(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $this->timebase->insert()->storage(['test0'])->timestamp($this->timestamp - $i)->set('ts:' . ($this->timestamp + $i))->execute();

            $datetime = new DateTime();
            $datetime->setTimestamp($this->timestamp - $i);
            $filePath = self::DATABASE_DIR . 'test0/' . $datetime->format('Y-m-d') . ExtensionConstant::DB_EXTENSION;
            $timestamp = LineTimestampHelper::getFirstLineTimestamp($filePath);
            self::assertEquals($this->timestamp - $i, $timestamp);

            $file = new SplFileObject($filePath);
            $file->seek(PHP_INT_MAX);
            self::assertEquals($i + 1, $file->key());
        }
    }

    /**
     * @throws Exception
     */
    public function testInsertToTheEnd(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $this->timebase->insert()->storage(['test0'])->timestamp($this->timestamp + $i)->set('ts:' . ($this->timestamp + $i))->execute();

            $datetime = new DateTime();
            $datetime->setTimestamp($this->timestamp + $i);
            $filePath = self::DATABASE_DIR . 'test0/' . $datetime->format('Y-m-d') . ExtensionConstant::DB_EXTENSION;
            $timestamp = LineTimestampHelper::getLastLineTimestamp($filePath);
            self::assertEquals($this->timestamp + $i, $timestamp);

            $file = new SplFileObject($filePath);
            $file->seek(PHP_INT_MAX);
            self::assertEquals($i + 1, $file->key());
        }
    }

    /**
     * @throws Exception
     */
    public function testInsertInTheMiddleOfFile(): void
    {
        $this->timebase->insert()->storage(['test0'])->timestamp($this->timestamp - 1)->set('ts:' . ($this->timestamp - 1))->execute();
        $this->timebase->insert()->storage(['test0'])->timestamp($this->timestamp + 1000)->set('ts:' . ($this->timestamp + 1000))->execute();

        for ($i = 0; $i < 100; $i++) {
            $this->timebase->insert()->storage(['test0'])->timestamp($this->timestamp + $i)->set('ts:' . ($this->timestamp + $i))->execute();
            $datetime = new DateTime();
            $datetime->setTimestamp($this->timestamp + $i);
            $filePath = self::DATABASE_DIR . 'test0/' . $datetime->format('Y-m-d') . ExtensionConstant::DB_EXTENSION;

            $timestamp = LineTimestampHelper::getFirstLineTimestamp($filePath);
            self::assertEquals($this->timestamp - 1, $timestamp);
            $timestamp = LineTimestampHelper::getLastLineTimestamp($filePath);
            self::assertEquals($this->timestamp + 1000, $timestamp);

            $file = new SplFileObject($filePath);
            $file->seek(PHP_INT_MAX);
            self::assertEquals($i + 3, $file->key());

            $file->seek($i + 1);
            $currentLineContent = $file->current();
            $currentLineTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, '/'));
            self::assertEquals($this->timestamp + $i, $currentLineTimestamp);
        }
    }

    /**
     * @throws Exception
     */
    public function testInsertInTheMiddleOfFileWithSameTimestamp(): void
    {
        $this->timebase->insert()->storage(['test0'])->timestamp($this->timestamp - 1)->set('ts:' . ($this->timestamp - 1))->execute();
        $this->timebase->insert()->storage(['test0'])->timestamp($this->timestamp)->set('ts:' . ($this->timestamp))->execute();
        $this->timebase->insert()->storage(['test0'])->timestamp($this->timestamp + 1000)->set('ts:' . ($this->timestamp + 1000))->execute();

        $datetime = new DateTime();
        $datetime->setTimestamp($this->timestamp);
        $filePath = self::DATABASE_DIR . 'test0/' . $datetime->format('Y-m-d') . ExtensionConstant::DB_EXTENSION;

        for ($i = 0; $i < 100; $i++) {
            $this->timebase->insert()->storage(['test0'])->timestamp($this->timestamp)->set('ts:' . ($this->timestamp + $i))->execute();

            $timestamp = LineTimestampHelper::getFirstLineTimestamp($filePath);
            self::assertEquals($this->timestamp - 1, $timestamp);
            $timestamp = LineTimestampHelper::getLastLineTimestamp($filePath);
            self::assertEquals($this->timestamp + 1000, $timestamp);

            $file = new SplFileObject($filePath);
            $file->seek(PHP_INT_MAX);
            self::assertEquals($i + 4, $file->key());

            $file->seek($i + 2);
            $currentLineContent = $file->current();
            $currentLineTimestamp = (int)substr($currentLineContent, 0, strpos($currentLineContent, '/'));
            self::assertEquals($this->timestamp, $currentLineTimestamp);

            $content = json_decode(base64_decode(substr($currentLineContent, strpos($currentLineContent, '/') + 1)),
                true, 512, JSON_THROW_ON_ERROR);
            self::assertEquals('ts:' . ($this->timestamp + $i), $content);
        }
    }
}
