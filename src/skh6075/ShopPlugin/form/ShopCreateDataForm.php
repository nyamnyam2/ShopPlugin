<?php


namespace skh6075\ShopPlugin\form;

use pocketmine\form\Form;
use pocketmine\Player;

use skh6075\ShopPlugin\shop\ShopFactory;
use skh6075\ShopPlugin\ShopPlugin;
use skh6075\ShopPlugin\shop\Shop;

use function trim;

class ShopCreateDataForm implements Form {


    public function jsonSerialize (): array{
        return [
            "type" => "custom_form",
            "title" => "§l상점 데이터생성",
            "content" => [
                [
                    "type" => "input",
                    "text" => "- 상점 이름을 적어주세요."
                ]
            ]
        ];
    }
    
    public function handleResponse (Player $player, $data): void{
        if (trim ($data [0] ?? "") === "") {
            $player->sendMessage (ShopPlugin::$prefix . '상점 이름을 적어주셔야 합니다.');
            return;
        }
        if (ShopFactory::getInstance ()->getShop ($data [0]) instanceof Shop) {
            $player->sendMessage (ShopPlugin::$prefix . '이미 존재하는 상점 입니다.');
            return;
        }
        ShopFactory::getInstance ()->addShopData ($data [0]);
        $player->sendMessage (ShopPlugin::$prefix . '해당 상점을 생성하였습니다.');
    }
}