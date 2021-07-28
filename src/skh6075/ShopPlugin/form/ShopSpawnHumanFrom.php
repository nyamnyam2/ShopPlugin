<?php


namespace skh6075\ShopPlugin\form;

use pocketmine\entity\Entity;
use pocketmine\form\Form;
use pocketmine\Player;

use skh6075\injectorutils\InjectorEntityUtils;
use skh6075\ShopPlugin\shop\ShopFactory;
use skh6075\ShopPlugin\ShopPlugin;

use function is_null;
use function array_keys;
use function array_map;

class ShopSpawnHumanFrom implements Form {

    public function jsonSerialize (): array{
        return [
            "type" => "form",
            "title" => "§l상점 엔피시소환",
            "content" => "\n소환하실 상점을 선택 해주세요.",
            "buttons" => array_map (function (string $name): array{
                return [ "text" => "§l{$name}" ];
            }, array_keys (ShopFactory::getInstance ()->getShops ()))
        ];
    }
    
    public function handleResponse (Player $player, $data): void{
        if (is_null($data)) {
            return;
        }
        $shops = array_keys(ShopFactory::getInstance()->getShops());
        if (!isset($shops[$data])) {
            return;
        }
        $nbt = InjectorEntityUtils::createEntityBaseNBT($player, null, $player->yaw, $player->pitch);
        $nbt->setString("shop", $shops[$data]);
        InjectorEntityUtils::pushEntitySkinCompoundTag($nbt, $player->getSkin());
        InjectorEntityUtils::createEntity("ShopHuman", $player->level, $nbt)->spawnToAll();
        $player->sendMessage(ShopPlugin::$prefix . "§f" . $shops[$data] . "§r§7 상점 엔피시를 소환하였습니다.");
    }
}