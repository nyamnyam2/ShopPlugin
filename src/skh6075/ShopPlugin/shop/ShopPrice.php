<?php


namespace skh6075\ShopPlugin\shop;

class ShopPrice implements \JsonSerializable {

    private string $code;

    private int $buyPrice = -1;

    private int $sellPrice = -1;
    
    
    public function __construct (string $code, int $buyPrice = -1, int $sellPrice = -1) {
        $this->code = $code;
        $this->buyPrice = $buyPrice;
        $this->sellPrice = $sellPrice;
    }
    
    public static function jsonDeserialize (array $data): self{
        return new ShopPrice (
            (string) $data ['code'],
            (int) $data ['buyPrice'],
            (int) $data ['sellPrice']
        );
    }
    
    public function jsonSerialize (): array{
        return [
            'code' => $this->code,
            'buyPrice' => $this->buyPrice,
            'sellPrice' => $this->sellPrice
        ];
    }
    
    public function getBuyPrice (): int{
        return $this->buyPrice;
    }
    
    public function getSellPrice (): int{
        return $this->sellPrice;
    }
    
    public function setBuyPrice (int $buyPrice = -1): void{
        $this->buyPrice = $buyPrice;
    }
    
    public function setSellPrice (int $sellPrice = -1): void{
        $this->sellPrice = $sellPrice;
    }
    
    public function getCode (): string{
        return $this->code;
    }
    
}