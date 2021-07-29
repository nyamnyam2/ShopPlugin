<?php


namespace skh6075\ShopPluginAssistant;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\SingletonTrait;
use skh6075\economyplus\EconomyPlus;
use skh6075\ShopPlugin\ShopPlugin;
use function class_exists;

class ShopPluginAssistant extends PluginBase{
    use SingletonTrait;

    public static string $prefix = "";
    
    public const SHOP_OPTION_BUY = 0;
    public const SHOP_OPTION_SELL = 1;


    public function onLoad(): void{
        self::setInstance($this);
    }
    
    public function onEnable (): void{
        if (!class_exists (EconomyPlus::class)  || !class_exists (ShopPlugin::class)) {
            $this->getLogger ()->error ("주요 연동 플러그인을 찾을 수 없습니다.");
            $this->getServer ()->getPluginManager ()->disablePlugin ($this);
            return;
        }
        self::$prefix = ShopPlugin::$prefix;
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public function onDisable(): void{
    }
    
    public function getStringBuyPrice (int $price = -1): string{
        return $price > -1 ? EconomyPlus::getInstance()->wonFormat($price, EconomyPlus::WON_FORMAT) : "구매 불가";
    }
    
    public function getStringSellPrice (int $price = -1): string{
        return $price > -1 ? EconomyPlus::getInstance()->wonFormat($price, EconomyPlus::WON_FORMAT) : "판매 불가";
    }
    
    public function getMyMoney ($player): int{
        return EconomyPlus::getInstance()->myMoney($player);
    }
}