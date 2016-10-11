<?php
use PHPUnit\Framework\TestCase;

define('ABSPATH', ''); // Required for running the script.
require_once dirname(__FILE__) . '/../timetable_checker.php';

class TimetableCheckerTest extends TestCase
{
    /**
     * @dataProvider isOpenNowDataProvider
     */
    public function testIsOpenNow($timetable, $now, $expectedIsOpenNow) {
        $this->setNow($now);
        $timetable_checker = new WooCommerceTimetable_TimetableChecker($timetable);
        $this->assertEquals($expectedIsOpenNow, $timetable_checker->isOpenNow());
    }

    public function isOpenNowDataProvider() {
        $timetable_1 = <<<'EOS'
Monday 10:00-20:00
EOS;

        $timetable_2 = <<<'EOS'
Monday 00:00-24:00
EOS;
        $timetable_3 = <<<'EOS'
Monday 10:00-20:00
Tuesday 10:00-20:00
2016-10-10 08:00-12:00 13:00-18:00
2016-11-* 12:00-20:00
EOS;

        $monday_1 = '2016-10-10';
        $monday_2 = '2016-10-17';
        $tuesday_1 = '2016-10-11';

        return [
            [$timetable_1, "$monday_1 12:00:00",  TRUE],
            [$timetable_1, "$monday_1 09:00:00",  FALSE],
            [$timetable_1, "$tuesday_1 12:00:00", FALSE],

            [$timetable_2, "$monday_1 00:00:00",  TRUE],
            [$timetable_2, "$monday_1 23:59:59",  TRUE],
            [$timetable_2, "$tuesday_1 00:00:00", FALSE],

            [$timetable_3, "$monday_1 12:00:00",  FALSE],
            [$timetable_3, "$monday_1 13:00:00",  TRUE],
            [$timetable_3, "$monday_2 12:00:00",  TRUE],

            [$timetable_3, "2016-11-01 13:00:00",  TRUE],
            [$timetable_3, "2016-11-01 11:00:00",  FALSE],
            [$timetable_3, "$tuesday_1 11:00:00",  TRUE],

            ['INVALID',    "$monday_1 11:00:00",   FALSE],
        ];
    }

    private function setNow($now) {
        WooCommerceTimetable_State::setNow(strtotime($now));
    }
}
