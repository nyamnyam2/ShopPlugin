<?php


namespace skh6075\ShopPlugin\form;

use pocketmine\form\Form;
use pocketmine\Player;

use skh6075\ShopPlugin\shop\ShopFactory;
use skh6075\ShopPlugin\form\modify\ShopModifyMenuForm;

use function array_map;
use function array_keys;
use function is_null;

class ShopModifyManageForm implements Form {


    public function jsonSerialize (): array{
        return [
            "type" => "form",
            "title" => "§l상점 메뉴창관리",
            "content" => "\n메뉴창을 관리할 상점을 선택 해주세요.",
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
        $player->sendForm (new ShopModifyMenuForm ($shops [$data]));
    }
    
}