<?php

namespace pocketcloud\player;

use pocketcloud\event\impl\player\PlayerConnectEvent;
use pocketcloud\event\impl\player\PlayerDisconnectEvent;
use pocketcloud\network\Network;
use pocketcloud\network\packet\impl\normal\PlayerSyncPacket;
use pocketcloud\util\CloudLogger;
use pocketcloud\util\SingletonTrait;

final class CloudPlayerManager {
    use SingletonTrait;

    /** @var array<CloudPlayer> */
    private array $players = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function addPlayer(CloudPlayer $player): void {
        if ($player->getCurrentServer() === null) CloudLogger::get()->info("Player %s is connected. (On: %s)", $player->getName(), ($player->getCurrentProxy()?->getName() ?? "NULL"));
        else CloudLogger::get()->info("Player %s is connected. (On: %s)", $player->getName(), ($player->getCurrentServer()->getName() ?? "NULL"));
        $this->players[$player->getName()] = $player;
        Network::getInstance()->broadcastPacket(new PlayerSyncPacket($player));
        (new PlayerConnectEvent($player, ($player->getCurrentServer() ?? $player->getCurrentProxy())))->call();
    }

    public function removePlayer(CloudPlayer $player): void {
        if ($player->getCurrentServer() === null) CloudLogger::get()->info("Player %s is disconnected. (From: %s)", $player->getName(), ($player->getCurrentProxy()?->getName() ?? "NULL"));
        else CloudLogger::get()->info("Player %s is disconnected. (From: %s)", $player->getName(), ($player->getCurrentServer()->getName() ?? "NULL"));
        if (isset($this->players[$player->getName()])) unset($this->players[$player->getName()]);
        (new PlayerDisconnectEvent($player, ($player->getCurrentServer() ?? $player->getCurrentProxy())))->call();
        $player->setCurrentServer(null);
        $player->setCurrentProxy(null);
        Network::getInstance()->broadcastPacket(new PlayerSyncPacket($player, true));
    }

    public function getPlayerByName(string $name): ?CloudPlayer {
        return $this->players[$name] ?? null;
    }

    public function getPlayerByUniqueId(string $uniqueId): ?CloudPlayer {
        return array_filter($this->players, fn(CloudPlayer $player) => $player->getUniqueId() == $uniqueId)[0] ?? null;
    }

    public function getPlayerByXboxUserId(string $xboxUserId): ?CloudPlayer {
        return array_filter($this->players, fn(CloudPlayer $player) => $player->getXboxUserId() == $xboxUserId)[0] ?? null;
    }

    public function getPlayers(): array {
        return $this->players;
    }

    public static function getInstance(): self {
        return self::$instance ??= new self;
    }
}