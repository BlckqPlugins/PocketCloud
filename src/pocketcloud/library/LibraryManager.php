<?php

namespace pocketcloud\library;

use pocketcloud\console\log\Logger;
use pocketcloud\language\Language;
use pocketcloud\util\CloudLogger;
use pocketcloud\util\SingletonTrait;
use pocketcloud\util\Utils;

class LibraryManager {
    use SingletonTrait;

    /** @var array<Library> */
    private array $libraries = [];

    public function __construct() {
        self::setInstance($this);
        $this->addLibrary(new Library(
            "Snooze",
            "https://github.com/pmmp/Snooze/archive/refs/heads/master.zip",
            LIBRARY_PATH . "snooze.zip",
            LIBRARY_PATH . "snooze/",
            ["composer.json", "README.md"],
            LIBRARY_PATH . "snooze/Snooze-master/src/",
            LIBRARY_PATH . "snooze/",
            LIBRARY_PATH . "snooze/Snooze-master/"
        ));

        $this->addLibrary(new Library(
            "configlib",
            "https://github.com/r3pt1s/configlib/archive/refs/heads/main.zip",
            LIBRARY_PATH . "configlib.zip",
            LIBRARY_PATH . "config/",
            ["README.md"],
            LIBRARY_PATH . "config/configlib-main/src/",
            LIBRARY_PATH . "config/",
            LIBRARY_PATH . "config/configlib-main/",
        ));

        $this->addLibrary(new Library(
            "pmforms",
            "https://github.com/dktapps-pm-pl/pmforms/archive/refs/heads/master.zip",
            LIBRARY_PATH . "pmforms.zip",
            LIBRARY_PATH . "pmforms/",
            ["README.md", "virion.yml", ".github/"],
            LIBRARY_PATH . "pmforms/pmforms-master/src/",
            LIBRARY_PATH . "pmforms/",
            LIBRARY_PATH . "pmforms/pmforms-master/",
            true
        ));
    }

    public function load() {
        foreach ($this->libraries as $library) {
            if (!$library->exists()) {
                $temporaryLogger = new Logger(saveMode: false);
                try {
                    $temporaryLogger->info("Start downloading library: %s (%s)", $library->getName(), $library->getDownloadUrl());
                    if ($library->download()) {
                        $temporaryLogger->info("Successfully downloaded library: %s (%s)", $library->getName(), $library->getUnzipLocation());
                    } else {
                        $temporaryLogger->warn("Failed to downloaded library: %s", $library->getName());
                    }
                } catch (\Throwable $exception) {
                    $temporaryLogger->warn("Failed to downloaded library: %s", $library->getName());
                    $temporaryLogger->exception($exception);
                }
            }

            if (!$library->isCloudBridgeOnly()) Utils::requireDirectory($library->getUnzipLocation());
        }
    }

    public function addLibrary(Library $library) {
        $this->libraries[$library->getName()] = $library;
    }

    public function removeLibrary(Library $library) {
        if (isset($this->libraries[$library->getName()])) unset($this->libraries[$library->getName()]);
    }

    public function getLibrary(string $name): ?Library {
        return $this->libraries[$name] ?? null;
    }

    public function getLibraries(): array {
        return $this->libraries;
    }
}