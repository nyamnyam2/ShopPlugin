<?php


namespace skh6075\ShopPluginAssistant;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\Player;

use pocketmine\Server;
use skh6075\injectorutils\InjectorItemUtils;
use skh6075\ShopPlugin\entity\ShopHuman;
use skh6075\ShopPlugin\event\ShopItemBuyEvent;
use skh6075\ShopPlugin\event\ShopItemSellEvent;
use skh6075\economyplus\EconomyPlus;

class EventListener implements Listener {

    /** @priority HIGHEST */
    public function onShopItemBuy (ShopItemBuyEvent $event): void{
        $player = $event->getPlayer ();
        $item = $event->getItem ();
        $price = $event->getPrice ();
        $count = $event->getCount ();
        $nowMoney = EconomyPlus::getInstance()->myMoney($player);
        $needMoney = $price->getBuyPrice () * $count;
        
        if ($price->getBuyPrice () <= -1) {
            $player->sendMessage(ShopPluginAssistant::$prefix . "해당 아이템은 구매가 불가능 합니다.");
            return;
        }
        if (EconomyPlus::getInstance ()->myMoney ($player) <= $needMoney) {
            $player->sendMessage(ShopPluginAssistant::$prefix . "돈이 부족 합니다.");
            return;
        }
        $newItem = $item->setCount ($count);
        if ($player->getInventory ()->canAddItem ($newItem)) {
            EconomyPlus::getInstance()->reduceMoney($player, $needMoney, "상점 " . InjectorItemUtils::getItemName($newItem) . " 아이템을 " . $newItem->getCount() . "개 구매");
            $player->getInventory()->addItem(clone $newItem);
            $player->sendMessage(ShopPluginAssistant::$prefix . "§f" . InjectorItemUtils::getItemName($newItem) . "§7 아이템을 §f" . $count . "개§7 만큼 구매 하셨습니다.");
            $afterMoney = EconomyPlus::getInstance()->myMoney($player);
            $player->sendMessage(ShopPluginAssistant::$prefix . "구매전 금액: §f" . EconomyPlus::getInstance()->wonFormat($nowMoney, EconomyPlus::WON_FORMAT) . "§7   구매후 금액: §f" . EconomyPlus::getInstance()->wonFormat($afterMoney, EconomyPlus::WON_FORMAT) . "§7   소비한 금액: §f" . EconomyPlus::getInstance()->wonFormat($needMoney, EconomyPlus::WON_FORMAT) . "");
        } else {
            $player->sendMessage(ShopPluginAssistant::$prefix . "인벤토리 공간이 부족합니다.");
        }
    }
    
    public function getInventoryItemCount (Player $player, Item $searchItem): int{
        $count = 0;
        foreach ($player->getInventory ()->all ($searchItem) as $item) {
            $count += $item->getCount ();
        }
        return $count;
    }

    /** @priority HIGHEST */
    public function onShopItemSell (ShopItemSellEvent $event): void{
        $player = $event->getPlayer ();
        $item = $event->getItem ();
        $price = $event->getPrice ();
        $count = $event->getCount ();
        $nowMoney = EconomyPlus::getInstance ()->myMoney ($player);
        $origin = $event->getOrigin ();
        
        if ($price->getSellPrice () <= -1) {
            $player->sendMessage(ShopPluginAssistant::$prefix . "해당 아이템은 판매가 불가능 합니다.");
            return;
        }
        if ($origin < $count) {
            $player->sendMessage(ShopPluginAssistant::$prefix . "판매할 아이템이 부족합니다.");
            return;
        }
        $newItem = $item->setCount ($count);
        $player->getInventory ()->removeItem (clone $newItem);
        $getMoney = $price->getSellPrice () * $count;
        EconomyPlus::getInstance ()->addMoney ($player, $getMoney, "상점 " . InjectorItemUtils::getItemName($newItem) . " 아이템을 " . $newItem->getCount() . "개 판매");
        $afterMoney = EconomyPlus::getInstance ()->myMoney ($player);
        $player->sendMessage (ShopPluginAssistant::$prefix . "§f" . InjectorItemUtils::getItemName($newItem) . "§7 아이템을 §f" . $count . "개§7 만큼 판매 하셨습니다.");
        $player->sendMessage (ShopPluginAssistant::$prefix . "판매전 금액: §f" . EconomyPlus::getInstance()->wonFormat($nowMoney, EconomyPlus::WON_FORMAT) . "§7   판매후 금액: §f" . EconomyPlus::getInstance()->wonFormat($afterMoney, EconomyPlus::WON_FORMAT) . "§7   이득본 금액: §f" . EconomyPlus::getInstance()->wonFormat($getMoney, EconomyPlus::WON_FORMAT) . "");
    }

    /** @priority HIGHEST */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void{
        $player = $event->getPlayer();
        $packet = $event->getPacket();
        if($packet instanceof InventoryTransactionPacket
            && $packet->trData instanceof UseItemOnEntityTransactionData){
            /** @var ShopHuman $entity */
            if(($entity = $player->getLevel()->getEntity($packet->trData->getEntityRuntimeId())) instanceof ShopHuman){
                if($player->isOp() && $player->isSneaking()){
                    $entity->close();
                    return;
                }
                $entity->sendShopInventory($player);
            }
        }
    }
}