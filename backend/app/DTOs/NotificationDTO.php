<?php

namespace App\DTOs;

use DateTimeImmutable; // 使用不可變的日期時間物件，更安全

/**
 * Notification Data Transfer Object
 *
 * 用於在應用程式不同層之間傳遞通知資料，
 * 與資料庫持久層解耦。
 */
final readonly class NotificationDTO // 'final' 防止繼承, 'readonly' (PHP 8.2+) 使屬性在初始化後不可變
{
    public function __construct(
        public string $id,
        public string $type,
        public string $notifiableType,
        public string $notifiableId, 
        public string $causerId,
        public string $causerType, 
        public array $data,
        public ?DateTimeImmutable $readAt, // 可為 null 的不可變日期時間
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {
    }

    /**
     * (可選) 靜態工廠方法，用於從陣列創建 DTO。
     * 這在 Mapper 中可能很有用。
     *
     * @param array $data 來自資料庫或請求的資料陣列
     * @return self
     */
    public static function fromArray(array $data): self
    {
        // 注意：這裡需要確保 $data 中的鍵存在且類型正確
        // 在實際應用中可能需要更健壯的錯誤處理或驗證
        return new self(
            id: $data['id'],
            type: $data['type'],
            notifiableType: $data['notifiable_type'],
            notifiableId: $data['notifiable_id'],     
            data: is_string($data['data']) ? json_decode($data['data'], true) : $data['data'], 
            causerId: $data['causer_id'],
            causerType: $data['causer_type'],
            readAt: isset($data['read_at']) ? new DateTimeImmutable($data['read_at']) : null,
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: new DateTimeImmutable($data['updated_at'])
        );
    }

     /**
     * (可選) 將 DTO 轉換回陣列的方法。
     * 這在 Mapper 中將 DTO 轉換為持久化格式時可能很有用。
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'notifiable_type' => $this->notifiableType, 
            'notifiable_id' => $this->notifiableId,  
            'data' => $this->data, 
            'causer_id' => $this->causerId,
            'causer_type' => $this->causerType,   
            'read_at' => $this->readAt?->format('Y-m-d H:i:s'), 
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}