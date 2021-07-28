<?php


namespace skh6075\ShopPlugin\form\modify;

use muqsit\invmenu\InvMenu;
use pocketmine\form\Form;
use pocketmine\Player;

use skh6075\injectorutils\InjectorItemUtils;
use skh6075\ShopPlugin\shop\ShopFactory;
use skh6075\ShopPlugin\ShopPlugin;
use skh6075\ShopPlugin\shop\Shop;

use function array_map;
use function array_keys;

class ShopPageModifyForm implements Form {

    private string $name;

    private ?Shop $shop;
    
    
    public function __construct (string $name) {
        $this->name = $name;
        $this->shop = ShopFactory::getInstance ()->getShop ($this->name);
    }
    
    public function jsonSerialize (): array{
        return [
            "type" => "custom_form",
            "title" => "§l{$this->name} 페이지 수정",
            "content" => [
                [
                    "type" => "dropdown",
                    "text" => "- 수정하실 페이지를 선택 해주세요.",
                    "options" => array_map (function (int $page): string{
                        return "- {$page} 페이지";
                    }, array_keys ($this->shop->getItems ()))
                ]
            ]
        ];
    }
    
    public function handleResponse (Player $player, $data): void{
        $page = intval ($data [0]);
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        for ($i = 0; $i < 36; $i ++) {
            $item = InjectorItemUtils::hashToItem(($code = $this->shop->getSlotItemByPage($page, $i)));
            $menu->getInventory()->setItem($i, clone $item);
        }
        $menu->setInventoryCloseListener(function () use ($player, $menu, $page): void{
            for ($i = 0; $i < 36; $i ++) {
                $this->shop->setShopItem($page, $i, $menu->getInventory()->getItem($i));
            }
            $player->sendMessage(ShopPlugin::$prefix . "상업을 업데이트 하였습니다.");
        });
        $menu->send($player, "상점 수정");
    }
}