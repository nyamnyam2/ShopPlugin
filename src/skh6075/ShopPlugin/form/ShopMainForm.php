<?php


namespace skh6075\ShopPlugin\form;

use pocketmine\form\Form;
use pocketmine\Player;

use skh6075\ShopPlugin\ShopPlugin;
use function is_null;

class ShopMainForm implements Form {


    public function jsonSerialize (): array{
        return [
            "type" => "form",
            "title" => "§l상점 관리",
            "content" => "\n원하시는 상점 메뉴를 선택 해주세요.",
            "buttons" => [
                [ "text" => "§l상점 데이터생성§r\n상점 데이터를 생성 합니다." ],
                [ "text" => "§l상점 데이터삭제§r\n상점 데이터를 삭제 합니다." ],
                [ "text" => "§l상점 엔피시소환§r\n상점 엔피시를 소환 합니다." ],
                [ "text" => "§l상점 메뉴창관리§r\n상점 메뉴창을 관리 합니다." ],
                [ "text" => "§l상점 아이템수정§r\n상점 아이템을 수정 합니다." ]
            ]
        ];
    }
    
    public function handleResponse (Player $player, $data): void{
        if (is_null ($data)) {
            return;
        }
        switch ($data) {
            case 0:
                $player->sendForm (new ShopCreateDataForm ());
                break;
            case 1:
                $player->sendForm (new ShopDeleteDataForm ());
                break;
            case 2:
                $player->sendForm (new ShopSpawnHumanFrom ());
                break;
            case 3:
                $player->sendForm (new ShopModifyManageForm ());
                break;
            case 4:
                $item = $player->getInventory ()->getItemInHand ();
                $item->isNull () ? $player->sendMessage (ShopPlugin::$prefix . '공기 아이템은 관리 할 수 없습니다.') : $player->sendForm (new ShopItemModifyForm ($item));
                break;
        }
    }
    
}