<?php

namespace App\Repositories\Notification;

use App\DTOs\NotificationDTO;
use Illuminate\Support\Collection; // 可以用 Collection 來回傳多筆結果

/**
 * Interface NotificationRepositoryInterface
 *
 * 定義了存取通知資料的契約。
 * 任何實現此介面的類別都需要提供這些資料操作方法。
 * 方法的參數和回傳值應使用 NotificationDTO，以保持與持久層的解耦。
 */
interface NotificationRepositoryInterface
{
    /**
     * 根據 ID 查找通知。
     *
     * @param string $id 通知的 UUID。
     * @return NotificationDTO|null 如果找到則回傳 DTO，否則回傳 null。
     */
    public function findById(string $id): ?NotificationDTO;

    /**
     * 儲存通知（新增或更新）。
     * 實現類別需要處理判斷是新增還是更新的邏輯。
     *
     * @param NotificationDTO $notification 要儲存的通知 DTO。
     * @return bool 操作是否成功。
     */
    public function save(NotificationDTO $notification): bool;
    
    /**
     * 批量儲存多個通知。
     * 這對於一次性為多個接收者創建通知（例如，粉絲通知）可能更有效率。
     *
     * @param Collection<int, NotificationDTO> $notifications 要儲存的通知 DTO 集合。
     * @return bool 操作是否整體成功，或者返回成功插入的數量等（取決於具體實現需求）。
     */
    public function saveMany(Collection $notifications): bool; // 或者 public function saveMany(array $notificationDTOs): int;


    /**
     * 根據接收者 ID 查找所有未讀通知。
     *
     * @param string $notifiableId 接收者的 ID (例如 User ID)。
     * @param string $notifiableType 接收者的類型 (例如 App\Models\User::class)。
     * @return Collection<int, NotificationDTO> 包含未讀通知 DTO 的集合。
     */
    public function findUnreadByNotifiable(string $notifiableId, string $notifiableType): Collection;

    /**
     * 將特定通知標記為已讀。
     *
     * @param string $id 要標記為已讀的通知 ID。
     * @return bool 操作是否成功。
     */
    public function markAsRead(string $id): bool;

    /**
     * 將特定接收者的所有未讀通知標記為已讀。
     *
     * @param string $notifiableId 接收者的 ID。
     * @param string $notifiableType 接收者的類型。
     * @return int 被標記為已讀的通知數量。
     */
    public function markAllAsRead(string $notifiableId, string $notifiableType): int;

    /**
     * 刪除通知。
     *
     * @param string $id 要刪除的通知 ID。
     * @return bool 操作是否成功。
     */
    public function delete(string $id): bool;
}