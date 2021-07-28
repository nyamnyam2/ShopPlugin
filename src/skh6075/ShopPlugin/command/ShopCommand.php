<?php


namespace skh6075\ShopPlugin\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use skh6075\ShopPlugin\ShopPlugin;
use skh6075\ShopPlugin\form\ShopMainForm;

class ShopCommand extends Command {

    protected ShopPlugin $plugin;


    public function __construct(ShopPlugin $plugin) {
        parent::__construct ("상점관리", "상점관리 명령어 입니다.", "/상점관리", ["shop"]);
        $this->setPermission ("shop.permission");
        $this->plugin = $plugin;
    }
    
    public function execute (CommandSender $player, string $label, array $args): bool{
        if (!$player instanceof Player) {
            $player->sendMessage(ShopPlugin::$prefix . "인게임에서만 사용할 수 있습니다.");
            return false;
        }
        $player->hasPermission($this->getPermission()) ?
            $player->sendForm(new ShopMainForm()) :
            $player->sendMessage(ShopPlugin::$prefix . "당신은 이 명령어를 사용할 권한이 없습니다.");
        return true;
    }
    
}