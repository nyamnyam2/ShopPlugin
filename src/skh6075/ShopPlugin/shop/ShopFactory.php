<?php

namespace skh6075\ShopPlugin\shop;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\utils\SingletonTrait;
use skh6075\economyplus\EconomyPlus;
use skh6075\injectorutils\InjectorItemUtils;
use skh6075\ShopPlugin\ShopPlugin;
use skh6075\ShopPlugin\traits\ShopTrait;

use function mkdir;
use function unlink;
use function substr;
use function strrchr;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_encode;
use function json_decode;
use function scandir;
use function array_diff;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

final class ShopFactory{
    use SingletonTrait;

    /** @var ShopPlugin */
    protected $plugin;
    /** @var Shop[] */
    private static $shops = [];
    /** @var ShopPrice[] */
    private static $prices = [];


    public function __construct() {
        self::setInstance($this);
        $this->plugin = ShopPlugin::getInstance();

        if (!is_dir($this->plugin->getDataFolder() . "shops/")) {
            mkdir($this->plugin->getDataFolder() . "shops/");
        }
    }

    public function init(): void{
        $this->loadShops();
        $this->loadShopPrices();
    }

    public function save(): void{
        foreach (self::$shops as $name => $class) {
            file_put_contents($this->plugin->getDataFolder() . "shops/" . $name . ".json", json_encode($class->jsonSerialize(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        $items = [];
        foreach (self::$prices as $code => $class) {
            $items[$code] = $class->jsonSerialize();
        }
        file_put_contents($this->plugin->getDataFolder() . "itemPrice.json", json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function loadShops(): void{
        foreach (array_diff(scandir($this->plugin->getDataFolder() . "shops/"), ['.', '..']) as $value) {
            if (substr(strrchr($value, '.'), 1) !== "json") {
                continue;
            }
            $name = explode(".", $value)[0];
            $json = json_decode(file_get_contents($this->plugin->getDataFolder() . "shops/" . $value), true);
            self::$shops[$name] = Shop::jsonDeserialize($json);
        }
    }

    private function loadShopPrices(): void{
        $file = file_exists($this->plugin->getDataFolder() . "itemPrice.json") ? file_get_contents($this->plugin->getDataFolder() . "itemPrice.json") : "{}";
        $json = json_decode($file, true);

        foreach ($json as $hash => $data) {
            self::$prices[$hash] = ShopPrice::jsonDeserialize($data);
        }
    }

    public function addShopData(string $name): void{
        if (isset(self::$shops[$name])) {
            return;
        }
        $data = [
            "name"  => $name,
            "items" => []
        ];
        for ($i = 0; $i < 45; $i ++) {
            $data["items"][0][$i] = "0:0:";
        }
        self::$shops[$name] = Shop::jsonDeserialize($data);
    }

    public function deleteShopData(string $name): void{
        if (!isset(self::$shops[$name])) {
            return;
        }
        unset(self::$shops[$name]);
        unlink($this->plugin->getDataFolder() . "shops/" . $name . ".json");
    }

    public function addItemPrice(Item $item): void{
        if (isset(self::$prices[InjectorItemUtils::itemToHash($item, false, true)])) {
            return;
        }
        $data = [
            "code" => InjectorItemUtils::itemToHash($item, false, true),
            "buyPrice" => -1,
            "sellPrice" => -1
        ];
        self::$prices[InjectorItemUtils::itemToHash($item, false, true)] = ShopPrice::jsonDeserialize($data);
    }

    public function getShop(string $name): ?Shop{
        return self::$shops[$name] ?? null;
    }

    public function getItemPrice(string $code): ?ShopPrice{
        return self::$prices[$code] ?? null;
    }

    public function getShops(): array{
        return self::$shops;
    }

    public function onSellAll(Player $player, Shop $shop, int $page = 1): void{
        $sellItems = [];
        $price = 0;

        foreach ($player->getInventory()->getContents(true) as $slot => $item) {
            if (!($class = $this->getItemPrice(InjectorItemUtils::itemToHash($item, false, true))) instanceof ShopPrice)
                continue;
            if ($class->getSellPrice() >= 0) {
                $sellItems[] = InjectorItemUtils::getItemName($item) . "§r§f x " . $item->getCount() . "개§7";
                $price += $class->getSellPrice() * $item->getCount();
                $player->getInventory()->setItem($slot, ItemFactory::get(Item::AIR));
            }
        }

        $calculate = ceil($price * 0.05);
        $resultPrice = $price - $calculate;

        $player->sendMessage(ShopPlugin::$prefix . "판매 전체 기능을을 사용해주셔서 감사합니다.");

        if (count($sellItems) > 0) {
            $player->sendMessage(ShopPlugin::$prefix . "판매된 아이템: §f" . implode(", ", $sellItems));
            $player->sendMessage(ShopPlugin::$prefix . "판매된 액수: §f" . EconomyPlus::getInstance()->wonFormat($price, EconomyPlus::WON_FORMAT) . "  §7획득한 액수: §f" . EconomyPlus::getInstance()->wonFormat($resultPrice, EconomyPlus::WON_FORMAT));
        }

        EconomyPlus::getInstance()->addMoney($player, $resultPrice, "판매 전체");
   }
}