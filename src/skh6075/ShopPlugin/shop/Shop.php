<?php

namespace skh6075\ShopPlugin\shop;

use pocketmine\item\Item;

use skh6075\injectorutils\InjectorItemUtils;
use skh6075\ShopPlugin\traits\ShopTrait;
use function count;

class Shop implements \JsonSerializable {

    private string $shopName;

    private array $items = [];
    
    
    public function __construct (string $shopName, array $items) {
        $this->shopName = $shopName;
        $this->items = $items;
    }
    
    public static function jsonDeserialize (array $data): self{
        return new Shop (
            (string) $data ['name'],
            (array) $data ['items']
        );
    }
    
    public function jsonSerialize (): array{
        return [
            'name' => $this->shopName,
            'items' => $this->items
        ];
    }
    
    public function getShopName (): string{
        return $this->shopName;
    }
    
    public function setShopItem (int $page = 0, int $slot = 0, Item $item): void{
        $this->items [$page] [$slot] = InjectorItemUtils::itemToHash($item, false, true);
    }
    
    public function getShopItemByPage (int $page = 0): array{
        return $this->items [$page];
    }
    
    public function getShopItemBySlot (int $page = 0, int $slot = 0): string{
        return $this->items [$page] [$slot] ?? '0:0:';
    }
    
    public function isExistsShopPage (int $page = 0): bool{
        return isset ($this->items [$page]);
    }
    
    public function addShopPage (): int{
        $page = count ($this->items);
        $this->items [$page] = [];
        for ($i = 0; $i < 45; $i ++) {
            $this->items [$page] [$i] = '0:0:';
        }
        return $page;
    }
    
    public function getItems (): array{
        return $this->items;
    }
    
    public function getPageItems (int $page = 0): array{
        return $this->items [$page];
    }
    
    public function getSlotItemByPage (int $page = 0, int $slot = 0): string{
        return $this->items [$page] [$slot] ?? '0:0:';
    }
    
    public function getShopMaxPage (): int{
        return count ($this->items);
    }
}