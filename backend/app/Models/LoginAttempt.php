<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginAttempt extends Model
{
    use HasFactory;
    
    protected $table = 'loginattempts';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user',
        'ip_address',
        'timestamp'
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'integer',
        ];
    }

    //登入請求使用函數
    //在資料庫記錄登入失敗次數
    public function recordFailedAttempt($userId, $ipAddress) {
        $query = "INSERT INTO loginattempts (user, ip_address, timestamp) VALUES (?,?,?)";
        $params = [$userId, $ipAddress, time()];
        $result = DB::insert($query, $params);

        return (bool) $result;
    }

    // 重置登入次數
    public function clearAttempt($userId)
    {
        $query = "DELETE FROM loginattempts WHERE user = ?";
        $params = [$userId];
        $result = DB::delete($query, $params);

        return $result > 0;
    }
}
