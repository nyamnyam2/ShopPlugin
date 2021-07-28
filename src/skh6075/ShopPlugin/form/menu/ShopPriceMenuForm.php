<?php


namespace skh6075\ShopPlugin\form\menu;

use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\item\Item;

use skh6075\ShopPlugin\entity\ShopHuman;
use skh6075\ShopPlugin\shop\ShopPrice;
use skh6075\ShopPlugin\event\ShopItemBuyEvent;
use skh6075\ShopPlugin\event\ShopItemSellEvent;
use skh6075\ShopPlugin\ShopPlugin;
use skh6075\ShopPluginAssistant\ShopPluginAssistant;

class ShopPriceMenuForm implements Form {

    private Player $player;
    
    private Item $item;
    
    private ShopPrice $price;
    
    private ShopHuman $entity;
    
    
    public function __construct (Player $player, Item $item, ShopPrice $price, ShopHuman $entity) {
        $this->player = $player;
        $this->item = $item;
        $this->price = $price;
        $this->entity = $entity;
    }
    
    public function getInventoryItemCount (Player $player, Item $searchItem): int{
        $count = 0;
        foreach ($player->getInventory ()->all ($searchItem) as $item) {
            $count += $item->getCount ();
        }
        return $count;
    }
    
    public function getShopItemName (): string{
        return $this->item->hasCustomName () ? $this->item->getCustomName () . "§r" : $this->item->getName ();
    }
    
    public function jsonSerialize (): array{
        return [
            "type" => "custom_form",
            "title" => "상점: " . $this->entity->getShopName () . "",
            "content" => [
                [
                    "type" => "label",
                    "text" => "§b" . $this->getShopItemName () . "§f 아이템을 구매 또는 판매를 하시겠습니까?\n\n\n"
                                . "보유 아이템 수량: §b" . $this->getInventoryItemCount ($this->player, $this->item) . "개§r\n\n"
                                . "구매가: §f" . ShopPluginAssistant::getInstance()->getStringBuyPrice ($this->price->getBuyPrice ()) . "§f\n"
                                . "판매가: §f" . ShopPluginAssistant::getInstance()->getStringSellPrice ($this->price->getSellPrice ()) . "§f\n"
                ],
                [
                    "type" => "dropdown",
                    "text" => "원하시는 상점 메뉴를 선택 해주세요.",
                    "options" => [
                        "- 구매하기",
                        "- 판매하기"
                    ]
                ],
                [
                    "type" => "input",
                    "text" => "원하시는 수량을 적어주세요."
                ]
            ]
        ];
    }
    
    public function handleResponse (Player $player, $data): void{
        $option = intval ($data [1] ?? 0);
        $count = $data [2] ?? "";

        if ($option === null or trim($count) === "" or !is_numeric($count)) {
            $player->sendMessage(ShopPlugin::$prefix . "수량을 잘 적어주셔야 합니다.");
            return;
        }

        if ($count <= 0) {
            return;
        }

        switch ($option) {
            case ShopPluginAssistant::SHOP_OPTION_BUY:
                (new ShopItemBuyEvent ($player, $this->item, $this->price, $count))->call ();
                break;
            case ShopPluginAssistant::SHOP_OPTION_SELL:
                $origin = $this->getInventoryItemCount ($player, $this->item);
                (new ShopItemSellEvent ($player, $this->item, $this->price, $count, $origin))->call ();
                break;
            default:
                break;
        }
    }
    
}