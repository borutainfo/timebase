<?php
declare(strict_types=1);

use Boruta\Timebase\Timebase;
use PHPUnit\Framework\TestCase;

final class ExactSearchInSingleFileTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        $timebase = new Timebase(__DIR__ . '/../database/');


        $currentTimestamp = time();

        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < $i; $j++) {
                $timebase->insert()->storage([(string)$i])->timestamp($currentTimestamp + $j)->set(['test' => md5(uniqid('', true))])->execute();
            }
            for ($j = 0; $j < $i; $j++) {
                echo($j);
                $result = $timebase->query()->storage([(string)$i])->timestamp($currentTimestamp + $j)->exact()->execute();
                $resultExact = $result->getExact();
                self::assertNotEmpty($resultExact);
                self::assertTrue(isset($resultExact[$currentTimestamp + $j]));
            }
        }
    }
}
