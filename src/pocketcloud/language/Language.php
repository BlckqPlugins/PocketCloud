<?php

namespace pocketcloud\language;

use pocketcloud\config\impl\DefaultConfig;
use pocketcloud\util\CloudLogger;
use pocketcloud\util\EnumTrait;
use Throwable;

/**
 * @method static Language GERMAN()
 * @method static Language ENGLISH()
 */
final class Language {
    use EnumTrait;

    public const FALLBACK = "en";

    protected static function init(): void {
        CloudLogger::get()->debug("Initializing languages...");
        self::register("german", new Language(
            "German",
            STORAGE_PATH . "de_DE.yml",
            ["de_DE", "ger", "Deutsch"],
            DefaultMessages::MESSAGES_DE
        ));

        self::register("english", new Language(
            "English",
            STORAGE_PATH . "en_US.yml",
            ["en_US", "en", "Englisch"],
            DefaultMessages::MESSAGES
        ));
    }

    public static function current(): Language {
        return self::getLanguage(DefaultConfig::getInstance()->getLanguage() ?? self::FALLBACK);
    }

    public static function fallback(): Language {
        return self::getLanguage(self::FALLBACK);
    }

    public static function getLanguage(string $name): ?Language {
        /** @var Language $language */
        foreach (self::getAll() as $language) {
            if ($language->getName() == $name || in_array($name, $language->getAliases())) return $language;
        }
        return null;
    }

    /** @var array<string, string> */
    private array $messages;

    public function __construct(
        private readonly string $name,
        private readonly string $filePath,
        private readonly array $aliases,
        array $defaultMessages = []
    ) {
        if (file_exists($this->filePath)) {
            try {
                $this->messages = yaml_parse(file_get_contents($this->filePath));
                $foundMissingKeys = false;
                foreach ($defaultMessages as $key => $value) {
                    if (!isset($this->messages[$key])) {
                        $this->messages[$key] = $value;
                        $foundMissingKeys = true;
                    }
                }

                if ($foundMissingKeys) {
                    CloudLogger::get()->info("Incomplete language file found: %s, completed the file with the missing lang keys.", $this->filePath);
                    file_put_contents($this->filePath, yaml_emit($this->messages, YAML_UTF8_ENCODING));
                }
            } catch (Throwable $exception) {
                $this->messages = $defaultMessages;
                CloudLogger::get()->exception($exception);
            }
        } else {
            $this->messages = $defaultMessages;
            file_put_contents($this->filePath, yaml_emit($this->messages, YAML_UTF8_ENCODING));
        }
    }

    public function translate(string $key, mixed ...$params): string {
        $message = str_replace("{PREFIX}", $this->messages["inGame.prefix"] ?? "", $this->messages[$key] ?? $key);
        foreach ($params as $i => $param) $message = str_replace("%" . $i . "%", $param, $message);
        return $message;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAliases(): array {
        return $this->aliases;
    }

    public function getMessages(): array {
        return $this->messages;
    }
}