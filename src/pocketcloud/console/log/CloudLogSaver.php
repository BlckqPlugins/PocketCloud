<?php

namespace pocketcloud\console\log;

class CloudLogSaver {

    private static array $savedLines = [];

    public static function save(string $line): void {
        self::$savedLines[] = $line;
    }

    public static function clear(): void {
        self::$savedLines = [];
    }

    public static function print(): void {
        foreach (self::$savedLines as $line) {
            echo $line;
        }
    }
}