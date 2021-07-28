<?php


namespace skh6075\ShopPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\entity\Entity;

use pocketmine\utils\SingletonTrait;
use skh6075\ShopPlugin\command\ShopCommand;
use skh6075\ShopPlugin\entity\ShopHuman;
use skh6075\ShopPlugin\shop\ShopFactory;

class ShopPlugin extends PluginBase {
    use SingletonTrait;

    public static string $prefix = '§l§b[상점]§r§7 ';


    public function onLoad (): void{
        self::setInstance($this);
        Entity::registerEntity (ShopHuman::class, true, [ 'ShopHuman' ]);
    }
    
    public function onEnable (): void{
        ShopFactory::getInstance()->init();
        $this->getServer()->getCommandMap()->register(strtolower($this->getName()), new ShopCommand($this));
    }
    
    public function onDisable (): void{
        ShopFactory::getInstance()->save();
    }
}