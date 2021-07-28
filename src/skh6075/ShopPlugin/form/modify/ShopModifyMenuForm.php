<?php


namespace skh6075\ShopPlugin\form\modify;

use pocketmine\form\Form;
use pocketmine\Player;

use skh6075\ShopPlugin\shop\ShopFactory;
use skh6075\ShopPlugin\ShopPlugin;
use skh6075\ShopPlugin\shop\Shop;
use function is_null;

class ShopModifyMenuForm implements Form {

    private string $name;
    
    
    public function __construct (string $name) {
        $this->name = $name;
    }
    
    public function jsonSerialize (): array{
        return [
            "type" => "form",
            "title" => "§l{$this->name} 상점 수정",
            "content" => "\n수정하실 상점 종류를 선택 해주세요.",
            "buttons" => [
                [ "text" => "§l상점 페이지추가§r\n상점 페이지를 추가 합니다." ],
                [ "text" => "§l상점 페이지수정§r\n상점 페이지를 수정 합니다." ]
            ]
        ];
    }
    
    public function handleResponse (Player $player, $data): void{
        if (is_null ($data)) {
            return;
        }
        switch ($data) {
            case 0:
                if (($shop = ShopFactory::getInstance ()->getShop ($this->name)) instanceof Shop) {
                    $page = $shop->addShopPage ();
                    $player->sendMessage (ShopPlugin::$prefix . "§f{$page} 페이지§7를 추가하였습니다.");
                }
                break;
            case 1:
                $player->sendForm (new ShopPageModifyForm ($this->name));
                break;
        }
    }
    
}