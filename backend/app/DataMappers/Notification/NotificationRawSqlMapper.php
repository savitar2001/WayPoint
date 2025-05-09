<?php

namespace App\DataMappers\Notification;

use App\DTOs\NotificationDTO;
use DateTimeImmutable;
use Exception; 
use Illuminate\Support\Arr; 
use Illuminate\Support\Facades\Log; 

class NotificationRawSqlMapper implements NotificationMapperInterface
{
    /**
     * 將從持久層獲取的原始資料映射到 NotificationDTO。
     *
     * @param array $data 從資料庫查詢結果等獲取的原始資料陣列。
     * @return NotificationDTO
     * @throws Exception 如果必要的資料缺失或格式錯誤。
     */
    public function mapToDomainObject(array $data): NotificationDTO
    {
        // 基本驗證，確保必要欄位存在
        $requiredKeys = ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'causer_id', 'causer_type','created_at', 'updated_at'];
        foreach ($requiredKeys as $key) {
            if (!Arr::exists($data, $key)) {
                Log::error("Notification mapping error: Missing key '{$key}' in data.", ['data' => $data]);
                throw new Exception("Cannot map notification data: Missing key '{$key}'.");
            }
        }

        try {
            // 處理 data 欄位 (假設從 DB 取出的是 JSON 字串)
            $notificationData = is_string($data['data']) ? json_decode($data['data'], true, 512, JSON_THROW_ON_ERROR) : $data['data'];
            if (!is_array($notificationData)) {
                 throw new Exception("Cannot map notification data: 'data' field is not a valid JSON string or array.");
            }

            return new NotificationDTO(
                id: (string) $data['id'], 
                type: (string) $data['type'],
                notifiableType: (string) $data['notifiable_type'],
                notifiableId: (string) $data['notifiable_id'], 
                data: $notificationData,
                causerId: (string) $data['causer_id'],
                causerType: (string) $data['causer_type'],
                readAt: isset($data['read_at']) ? new DateTimeImmutable($data['read_at']) : null,
                createdAt: new DateTimeImmutable($data['created_at']),
                updatedAt: new DateTimeImmutable($data['updated_at'])
            );
        } catch (\JsonException $e) {
            Log::error("Notification mapping error: Failed to decode JSON data.", ['data' => $data['data'], 'error' => $e->getMessage()]);
            throw new Exception("Cannot map notification data: Failed to decode 'data' field. " . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            Log::error("Notification mapping error: Failed to create DTO.", ['data' => $data, 'error' => $e->getMessage()]);
            throw new Exception("Cannot map notification data: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 將 NotificationDTO 映射到持久層可以理解的格式（用於原生 SQL 的陣列）。
     *
     * @param NotificationDTO $dto 要進行映射的資料傳輸物件。
     * @return array 準備好用於資料庫插入或更新的陣列。
     * @throws \JsonException 如果 data 陣列無法編碼為 JSON。
     */
    public function mapToPersistence(NotificationDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'type' => $dto->type,
            'notifiable_type' => $dto->notifiableType, 
            'notifiable_id' => $dto->notifiableId,     
            'data' => json_encode($dto->data, JSON_THROW_ON_ERROR), // 將 data 陣列編碼為 JSON 字串
            'causer_id' => $dto->causerId,
            'causer_type' => $dto->causerType,
            'read_at' => $dto->readAt?->format('Y-m-d H:i:s'), // 格式化為 DB 字串，處理 null
            'created_at' => $dto->createdAt->format('Y-m-d H:i:s'), // 格式化為 DB 字串
            'updated_at' => $dto->updatedAt->format('Y-m-d H:i:s'), // 格式化為 DB 字串
        ];
    }
}