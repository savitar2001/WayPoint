<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, HasUuids;

    /**
     * 與模型關聯的資料表。
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * 模型的主鍵。
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 指示主鍵是否為遞增。
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 主鍵的類型。
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 可大量賦值的屬性。
     * 主要用於從原生查詢結果填充模型時使用 $model->fill($data)。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id', // Include 'id' if you plan to fill it after manual generation
        'type',
        'notifiable_type',//
        'notifiable_id',
        'data',
        'causer_id',
        'causer_type',
        'read_at',
        // 'created_at' and 'updated_at' are typically handled manually in Raw SQL,
        // but can be included if you fill them during hydration.
    ];

    /**
     * 指示模型是否應自動維護時間戳。
     * 設為 false 因為時間戳將透過原生 SQL 手動管理。
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 應轉換為原生類型的屬性。
     * 即使使用原生 SQL，這對於從資料庫讀取資料後進行類型轉換仍然很有用。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',       // 將 data 欄位自動轉換為陣列
        'read_at' => 'datetime',   // 將 read_at 欄位自動轉換為 Carbon 實例
        'created_at' => 'datetime', // 即使 timestamps=false，定義 cast 仍可在手動填充時轉換
        'updated_at' => 'datetime', // 同上
    ];

    /**
     * 注意：
     * 當主要使用原生 SQL 時，此 Eloquent 模型主要作為：
     * 1. 資料結構的表示。
     * 2. 利用 $casts 屬性進行類型轉換（例如，從 DB::select 獲取的原始資料填充到此模型實例時）。
     * 3. 可能利用 HasUuids trait 在應用程式層面生成 UUID。
     *
     * Eloquent 的 ORM 功能（如 save(), find(), 關聯方法）將不會在 Repository 中直接使用。
     * 資料庫的 CRUD 操作將在 Repository 中透過 DB facade 執行原生 SQL 來完成。
     * 關聯（如 notifiable）需要透過原生 SQL 中的 JOIN 或額外查詢來手動處理。
     */
}