<?php

namespace App\Repositories\Notification;

use App\DTOs\NotificationDTO;
use App\DataMappers\Notification\NotificationMapperInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable; // Catch any throwable error/exception

class NotificationRawSqlRepository implements NotificationRepositoryInterface
{
    protected NotificationMapperInterface $mapper;

    public function __construct(NotificationMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    //儲存通知（處理新增或更新）。
    public function save(NotificationDTO $notification): bool
    {
        try {
            $data = $this->mapper->mapToPersistence($notification);

            $data['created_at'] = $data['created_at'] ?? now()->toDateTimeString();
            $data['updated_at'] = $data['updated_at'] ?? now()->toDateTimeString();

            $sql = "INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, causer_id, causer_type, read_at, created_at, updated_at)
                    VALUES (:id, :type, :notifiable_type, :notifiable_id, :data, :causer_id, :causer_type, :read_at, :created_at, :updated_at)
                    ON DUPLICATE KEY UPDATE
                        type = VALUES(type),
                        notifiable_type = VALUES(notifiable_type),
                        notifiable_id = VALUES(notifiable_id),
                        data = VALUES(data),
                        causer_id = VALUES(causer_id),
                        causer_type = VALUES(causer_type),
                        read_at = VALUES(read_at),
                        updated_at = VALUES(updated_at)"; 

            return DB::insert($sql, $data); 

        } catch (Throwable $e) {
            Log::error("Error saving notification [{$notification->id}]: " . $e->getMessage(), [
                'notification_data' => $notification->toArray(),
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * 批量儲存多個通知。
     * 這對於一次性為多個接收者創建通知（例如，粉絲通知）可能更有效率。
     *
     * @param Collection<int, NotificationDTO> $notifications 要儲存的通知 DTO 集合。
     * @return bool 操作是否整體成功，或者返回成功插入的數量等（取決於具體實現需求）。
     */
    public function saveMany(Collection $notifications): bool
    {
        if ($notifications->isEmpty()) {
            return true;
        }

        DB::beginTransaction();

        try {
            foreach ($notifications as $notification) {
                if (!$this->save($notification)) {
                    DB::rollBack();
                    Log::warning("Failed to save one notification during saveMany operation. Transaction rolled back.", [
                        'notification_id' => $notification->id ?? 'unknown_id', 
                    ]);
                    return false; 
                }
            }

            DB::commit();
            return true;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Error during saveMany operation. Transaction rolled back: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return false;
        }
    }
    
    //根據ID查找通知
    public function findById(string $id): ?NotificationDTO
    {
        $sql = "SELECT id, type, notifiable_type, notifiable_id, data, causer_id, causer_type, read_at, created_at, updated_at
                FROM notifications
                WHERE id = ?";

        try {
            $result = DB::selectOne($sql, [$id]);

            if ($result) {
                return $this->mapper->mapToDomainObject((array) $result);
            }
        } catch (Throwable $e) {
            Log::error("Error finding notification by ID [{$id}]: " . $e->getMessage(), ['exception' => $e]);
        }

        return null;
    }


    /**
     * 根據接收者 ID 查找所有未讀通知。
     */
    public function findUnreadByNotifiable(string $notifiableId, string $type): Collection
    {
        if ($type !== 'all') {
            $sql = "SELECT id, type, notifiable_type, notifiable_id, data, causer_id, causer_type, read_at, created_at, updated_at
                FROM notifications
                WHERE notifiable_id = ? AND notifiable_type = ? AND read_at IS NULL
                ORDER BY created_at DESC"; 
            $bindings = [$notifiableId, $type];
        } else {
            $sql = "SELECT id, type, notifiable_type, notifiable_id, data, causer_id, causer_type, read_at, created_at, updated_at
                FROM notifications
                WHERE notifiable_id = ? AND read_at IS NULL
                ORDER BY created_at DESC"; 
            $bindings = [$notifiableId];
        }

        $dtos = new Collection(); 

        try {
            $results = DB::select($sql, $bindings);

            foreach ($results as $result) {
                 try {
                    // 將每個結果映射到 DTO
                    $dtos->push($this->mapper->mapToDomainObject((array) $result));
                 } catch (Throwable $mapError) {
                    // 記錄單個映射錯誤，但繼續處理其他記錄
                    Log::warning("Error mapping one notification during findUnreadByNotifiable: " . $mapError->getMessage(), [
                        'raw_data' => (array) $result,
                        'exception' => $mapError
                    ]);
                 }
            }
        } catch (Throwable $e) {
            Log::error("Error finding unread notifications for [{$type}:{$notifiableId}]: " . $e->getMessage(), ['exception' => $e]);
            // 發生查詢錯誤時返回空集合
        }

        return $dtos;
    }

    /**
     * 將特定通知標記為已讀。
     */
    public function markAsRead(string $id): bool
    {
        $sql = "UPDATE notifications
                SET read_at = ?, updated_at = ?
                WHERE id = ? AND read_at IS NULL";

        try {
            $now = now()->toDateTimeString();
            $affectedRows = DB::update($sql, [$now, $now, $id]);
            return $affectedRows > 0; // 如果有行被更新，則返回 true
        } catch (Throwable $e) {
            Log::error("Error marking notification as read [{$id}]: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    /**
     * 將特定接收者的所有未讀通知標記為已讀。
     */
    public function markAllAsRead(string $notifiableId, string $notifiableType): int
    {
        $sql = "UPDATE notifications
                SET read_at = ?, updated_at = ?
                WHERE notifiable_id = ? AND notifiable_type = ? AND read_at IS NULL";

        try {
            $now = now()->toDateTimeString();
            // DB::update 返回受影響的行數
            return DB::update($sql, [$now, $now, $notifiableId, $notifiableType]);
        } catch (Throwable $e) {
            Log::error("Error marking all notifications as read for [{$notifiableType}:{$notifiableId}]: " . $e->getMessage(), ['exception' => $e]);
            return 0; // 返回 0 表示沒有行被更新或發生錯誤
        }
    }

    /**
     * 刪除通知。
     */
    public function delete(string $id): bool
    {
        $sql = "DELETE FROM notifications WHERE id = ?";

        try {
            $affectedRows = DB::delete($sql, [$id]);
            return $affectedRows > 0; // 如果有行被刪除，則返回 true
        } catch (Throwable $e) {
            Log::error("Error deleting notification [{$id}]: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}