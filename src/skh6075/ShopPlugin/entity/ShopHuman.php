<?php


namespace skh6075\ShopPlugin\entity;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\NamedTag;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\scheduler\ClosureTask;

use skh6075\injectorutils\InjectorItemUtils;
use skh6075\ShopPlugin\shop\ShopFactory;
use skh6075\ShopPlugin\ShopPlugin;
use skh6075\ShopPlugin\shop\Shop;
use skh6075\ShopPlugin\shop\ShopPrice;
use skh6075\ShopPlugin\form\menu\ShopPriceMenuForm;
use skh6075\ShopPlugin\traits\ShopTrait;
use skh6075\ShopPluginAssistant\ShopPluginAssistant;

class ShopHuman extends Human{
    use ShopTrait;

    private string $name;
    
    private array $page = [];
    
    
    public function __construct (Level $level, CompoundTag $nbt) {
        parent::__construct ($level, $nbt);
    }
    
    public function initEntity (): void{
        parent::initEntity ();
        $name = $this->namedtag->getString ('shop', '');
        if (trim($name) === '') {
            $this->close();
            return;
        }
        $this->name = $name;
        $this->setNameTag("§r§l" . $this->name . " 상점");
        $this->setNameTagAlwaysVisible(true);
    }
    
    public function saveNBT (): void{
        parent::saveNBT ();
        $this->namedtag->setString ('shop', $this->name);
    }

    public function hasMovementUpdate(): bool{
        return false;
    }

    public function getShopName (): string{
        return $this->name;
    }
    
    public function sendShopInventoryTask (Player $player, int $page = 0): void{
        ShopPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $unused) use ($player, $page): void{
            if ($player->isOnline())
                $this->sendShopInventory($player, $page);
        }), 10);
    }
    
    public function sendShopMenuForm (Player $player, Item $item, ShopPrice $price): void{
        ShopPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $unused) use ($player, $item, $price): void{
            if ($player->isOnline())
                $player->sendForm(new ShopPriceMenuForm($player, $item, $price, $this));
        }), 5);
    }
    
    public function sendShopInventory (Player $player, int $page = 0): void{
        if (!($shop = ShopFactory::getInstance ()->getShop ($this->name)) instanceof Shop) {
            return;
        }
        $this->page [$player->getName ()] = $page;
        $nextPage = $page + 1;
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->getInventory()->setItem(49, $this->getShopSellAllItem());

        for ($index = 0; $index < 45; $index ++) {
            $item = InjectorItemUtils::hashToItem(($code = $shop->getSlotItemByPage($page, $index)), false, true);
            $price = ShopFactory::getInstance()->getItemPrice($code);
            $buyPrice = $price instanceof ShopPrice ? ($price->getBuyPrice() > -1 ? ShopPluginAssistant::getInstance()->getStringBuyPrice($price->getBuyPrice()) : "§c구매 불가") : "§c구매 불가";
            $sellPrice = $price instanceof ShopPrice ? ($price->getSellPrice() > -1 ? ShopPluginAssistant::getInstance()->getStringSellPrice($price->getSellPrice()) : "§c판매 불가") : "§c판매 불가";

            $item->setLore([
                "§r§f구매가: §f" . $buyPrice . "",
                "§r§f판매가: §f" . $sellPrice . ""
            ]);
            $menu->getInventory()->setItem($index, clone $item);

            if ($page !== 0)
                $menu->getInventory()->setItem(45, $this->getShopBackPageItem($page - 1));
            if ($shop->isExistsShopPage($nextPage))
                $menu->getInventory()->setItem(53, $this->getShopNextPageItem($nextPage));
        }
        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $shop, $page): void{
            $transaction->getPlayer()->getCursorInventory()->setItem(0, ItemFactory::get(ItemIds::AIR));
            $item = $transaction->getItemClicked();

            if ($item->getNamedTagEntry("sell") instanceof NamedTag) {
                ShopFactory::getInstance()->onSellAll($transaction->getPlayer(), $shop, $page);
                $menu->getInventory()->close($transaction->getPlayer());
                return;
            }

            if ($item->getNamedTagEntry("shopPage") instanceof NamedTag) {
                $fakePage = (int) $item->getNamedTagEntry("shopPage")->getValue();
                $this->sendShopInventoryTask($transaction->getPlayer(), $fakePage);
                $menu->getInventory()->close($transaction->getPlayer());
                return;
            }

            $code = $shop->getShopItemBySlot($page, $transaction->getAction()->getSlot());
            $item = InjectorItemUtils::hashToItem($code);
            if (($price = ShopFactory::getInstance()->getItemPrice($code)) instanceof ShopPrice) {
                $this->sendShopMenuForm($transaction->getPlayer(), $item, $price);
                $menu->getInventory()->close($transaction->getPlayer());
            }
        }));
        $menu->send($player, "상점: " . $this->name);
    }

    public function attack(EntityDamageEvent $source): void{
        $source->setCancelled(true);
    }
}