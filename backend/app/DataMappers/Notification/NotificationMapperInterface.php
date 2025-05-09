<?php

namespace App\DataMappers\Notification;

use App\DTOs\NotificationDTO;

/**
 * Interface NotificationMapperInterface
 *
 * 定義了在 Notification DTO 和持久層表示之間進行映射的契約。
 */
interface NotificationMapperInterface
{
    /**
     * 將從持久層獲取的原始資料映射到 NotificationDTO。
     *
     * @param array $data 從資料庫查詢結果等獲取的原始資料陣列。
     *                    
     * @return NotificationDTO 映射後的資料傳輸物件。
     */
    public function mapToDomainObject(array $data): NotificationDTO;

    /**
     * 將 NotificationDTO 映射到持久層可以理解的格式（通常是陣列）。
     *
     * @param NotificationDTO $dto 要進行映射的資料傳輸物件。
     * @return array 準備好用於資料庫插入或更新的陣列。
     *               鍵名應對應資料庫欄位名 ('notifiable_type', 'read_at' 等)。
     *               'data' 欄位應為 JSON 字串。
     */
    public function mapToPersistence(NotificationDTO $dto): array;
}