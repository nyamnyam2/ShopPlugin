<?php

namespace skh6075\ShopPlugin\traits;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

trait ShopTrait{

    public function getShopBackPageItem(int $page): Item{
        $item = ItemFactory::get(ItemIds::WOOL, 5, 1);
        $item->setCustomName("§l§c이전§r\n§f§b" . $page . "§f 페이지");
        $item->setNamedTagEntry(new IntTag("shopPage", $page));
        return clone $item;
    }

    public function getShopNextPageItem(int $page): Item{
        $item = ItemFactory::get(ItemIds::WOOL, 14, 1);
        $item->setCustomName("§l§a다음§r\n§f§b" . $page . "§f 페이지");
        $item->setNamedTagEntry(new IntTag("shopPage", $page));
        return clone $item;
    }

    //49
    public function getShopSellAllItem(): Item{
        $item = ItemFactory::get(63, 0, 1);
        $item->setCustomName("§l§f판매 전체§r\n§7인벤토리의 모든 아이템을 판매합니다.\n§7이 기능을 이용시 수수료 §f5%§7를 가져갑니다.");
        $item->setNamedTagEntry(new StringTag("sell", "all"));
        return clone $item;
    }
}