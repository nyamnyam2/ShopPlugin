<?php


namespace skh6075\ShopPlugin\form;

use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\item\Item;

use skh6075\injectorutils\InjectorItemUtils;
use skh6075\ShopPlugin\shop\ShopFactory;
use skh6075\ShopPlugin\ShopPlugin;
use skh6075\ShopPlugin\shop\ShopPrice;
use skh6075\ShopPluginAssistant\ShopPluginAssistant;

use function trim;
use function is_numeric;

class ShopItemModifyForm implements Form {

    private Item $item;

    private ?ShopPrice $price;
    
    
    public function __construct (Item $item) {
        $this->item = $item;

        if (!ShopFactory::getInstance()->getItemPrice(InjectorItemUtils::itemToHash($this->item, false, true)) instanceof ShopPrice) {
            ShopFactory::getInstance()->addItemPrice($this->item);
        }
        $this->price = ShopFactory::getInstance()->getItemPrice(InjectorItemUtils::itemToHash($this->item, false, true ));
    }
    
    public function jsonSerialize (): array{
        return [
            "type" => "custom_form",
            "title" => "§l아이템 수정",
            "content" => [
                [
                    "type" => "input",
                    "text" => "- 아이템 구매가를 적어주세요.",
                    "default" => "" . $this->price->getBuyPrice ()
                ],
                [
                    "type" => "input",
                    "text" => "- 아이템 판매가를 적어주세요.",
                    "default" => "" . $this->price->getSellPrice ()
                ]
            ]
        ];
    }
    
    public function handleResponse (Player $player, $data): void{
        if (trim ($data [0] ?? "") === "" || !is_numeric ($data [0]) || trim ($data [1] ?? "") === "" || !is_numeric ($data [1])) {
            $player->sendMessage (ShopPlugin::$prefix . "가격을 적어주셔야 합니다.");
            return;
        }
        $this->price->setBuyPrice ((int) $data [0]);
        $this->price->setSellPrice ((int) $data [1]);

        $buyPrice  = $this->price->getBuyPrice() > -1 ? ShopPluginAssistant::getInstance()->getStringBuyPrice($this->price->getBuyPrice()) : "구매 불가";
        $sellPrice = $this->price->getSellPrice() > -1 ? ShopPluginAssistant::getInstance()->getStringSellPrice($this->price->getSellPrice()) : "판매 불가";
        $player->sendMessage(ShopPlugin::$prefix . "구매가: §f" . $buyPrice . "§7  /  판매가: §f" . $sellPrice);
    }
    
}