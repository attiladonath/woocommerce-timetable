<?php
// Prevent direct access to this script file.
defined('ABSPATH') or die();

class WooCommerceTimetable_State {
    private static $now;

    public static function getNow() {
        if (! isset(self::$now)) {
            self::$now = time();
        }
        return self::$now;
    }

    public static function setNow($now) {
        self::$now = $now;
    }
}

class WooCommerceTimetable_TimetableChecker {
    private $raw_timetable;

    public function __construct($raw_timetable) {
        $this->raw_timetable = $this->normalizeTimetable($raw_timetable);
    }

    public function isOpenNow() {
        // We cannot evaluate an invalid timetable.
        if (! $this->isValid()) return FALSE;

        $timetable = explode("\n", $this->raw_timetable);
        // The lower line overrides the upper.
        $raw_lines = array_reverse($timetable);
        foreach ($raw_lines as $raw_line) {
            $line = new WooCommerceTimetable_TimetableLine($raw_line);
            if ($line->isToday()) return $line->isNowInTimeIntervals();
        }
        // If today is found, then we've evaluated the given time intervals.
        // Otherwise today is not in the timetable, so the shop is closed.
        return FALSE;
    }

    public function isValid() {
        $days = '(' . join('|', WooCommerceTimetable_Date::DAYS) . ')';
        $date_format = '((\d{4}|\*)-(\d{2}|\*)-(\d{2}|\*))';
        $date = '(' . $days . '|' . $date_format . ')';
        $time_intervals = '([ ]\d{2}:\d{2}-\d{2}:\d{2})+';
        $line = $date . $time_intervals;
        $lines = '(' . $line . '\n)*';
        return 1 === preg_match('/^' . $lines . '$/m', $this->raw_timetable . "\n");
    }

    private function normalizeTimetable($timetable) {
      $timetable = str_replace("\r", "\n", $timetable);
      $timetable = preg_replace('/\n+/', "\n", $timetable);
      $timetable = preg_replace('/[^\S\n]+/', ' ', $timetable);
      return trim(strtolower($timetable));
    }
}

class WooCommerceTimetable_TimetableLine {
    private $raw_line;
    private $now;
    private $date;
    private $raw_time_intervals;

    public function __construct($raw_line) {
        $this->raw_line = $raw_line;
        $this->now = new DateTime();
        $this->now->setTimestamp(WooCommerceTimetable_State::getNow());
    }

    public function isToday() {
        return $this->getDate()->isToday();
    }

    public function isNowInTimeIntervals() {
        foreach ($this->getRawTimeIntervals() as $raw_time_interval) {
            $isOpenNow = $this->isNowInTimeInterval($raw_time_interval);
            if ($isOpenNow) return TRUE;
        }
        return FALSE;
    }

    private function isNowInTimeInterval($raw_time_interval) {
      $parts = explode('-', $raw_time_interval);
      $date = $this->now->format('Y-m-d');
      $from = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $parts[0]);
      $to = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $parts[1]);
      return $from <= $this->now && $this->now < $to;
    }

    private function getDate() {
        if (!isset($this->date)) {
            $this->fetchParts();
        }
        return $this->date;
    }

    private function getRawTimeIntervals() {
        if (!isset($this->raw_time_intervals)) {
            $this->fetchParts();
        }
        return $this->raw_time_intervals;
    }

    private function fetchParts() {
        $parts = explode(' ', $this->raw_line);
        $this->date = new WooCommerceTimetable_Date(array_shift($parts));
        $this->raw_time_intervals = $parts;
    }
}

class WooCommerceTimetable_Date {
    // Days in an order which matches the date('w') format.
    const DAYS = [
        'sunday', 'monday', 'tuesday', 'wednesday',
        'thursday', 'friday', 'saturday',
    ];

    private $date_pattern;

    public function __construct($date_pattern) {
        $this->date_pattern = $date_pattern;
    }

    public function isToday() {
        // Date pattern is a string day name.
        $index = array_search($this->date_pattern, self::DAYS);
        if (FALSE !== $index) {
            return date('w', WooCommerceTimetable_State::getNow()) == $index;
        }

        // Date pattern is in the format Y-m-d where
        // all parts can be wildcards: *.
        $date_parts = explode('-', $this->date_pattern);
        $date_pattern_chars = 'Ymd';
        for ($i = 0; $i <= 2; $i++) {
            $isWildcard = '*' == $date_parts[$i];
            $isMatching = date($date_pattern_chars[$i],
                               WooCommerceTimetable_State::getNow()) == $date_parts[$i];
            if (! ($isWildcard || $isMatching) ) {
              return FALSE;
            }
        }
        return TRUE;
    }
}
