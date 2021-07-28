<?php


namespace skh6075\ShopPlugin\event;

use pocketmine\event\Event;

use pocketmine\event\Cancellable;

use pocketmine\Player;
use pocketmine\item\Item;

use skh6075\ShopPlugin\shop\ShopPrice;

class ShopItemBuyEvent extends Event implements Cancellable {

    private Player $player;
    
    private Item $item;
    
    private ShopPrice $price;
    
    private int $count;
    
    
    public function __construct (Player $player, Item $item, ShopPrice $price, int $count) {
        $this->player = $player;
        $this->item = $item;
        $this->price = $price;
        $this->count = $count;
    }
    
    public function getPlayer (): Player{
        return $this->player;
    }
    
    public function getItem (): Item{
        return $this->item;
    }
    
    public function getPrice (): ShopPrice{
        return $this->price;
    }
    
    public function getCount (): int{
        return $this->count;
    }
}