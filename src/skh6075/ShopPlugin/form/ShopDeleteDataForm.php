<?php


namespace skh6075\ShopPlugin\form;

use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\Server;

use skh6075\ShopPlugin\shop\ShopFactory;
use skh6075\ShopPlugin\ShopPlugin;
use skh6075\ShopPlugin\entity\ShopHuman;

use function is_null;
use function array_keys;
use function array_map;

class ShopDeleteDataForm implements Form {


    public function jsonSerialize (): array{
        return [
            "type" => "form",
            "title" => "§l상점 데이터삭제",
            "content" => "\n삭제하실 상점을 선택 해주세요.",
            "buttons" => array_map (function (string $name): array{
                return [ "text" => "§l{$name}" ];
            }, array_keys (ShopFactory::getInstance ()->getShops ()))
        ];
    }
    
    public function handleResponse (Player $player, $data): void{
        if (is_null ($data)) {
            return;
        }
        $shops = array_keys (ShopFactory::getInstance ()->getShops ());
        if (!isset ($shops[$data])) {
            return;
        }
        ShopFactory::getInstance ()->deleteShopData ($shops[$data]);

        foreach (Server::getInstance ()->getLevels () as $level) {
            foreach ($level->getEntities () as $entity) {
                if (!$entity instanceof ShopHuman) {
                    continue;
                }
                if ($entity->getShopName () !== $shops[$data]) {
                    continue;
                }
                $entity->flagForDespawn();
            }
        }
        $player->sendMessage (ShopPlugin::$prefix . "§f{$shops[$data]}§r§7 상점을 삭제하였습니다.");
    }
    
}