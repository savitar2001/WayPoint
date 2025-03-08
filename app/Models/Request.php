<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Request extends Model
{
    use HasFactory;
    
    protected $table = 'requests';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user',
        'hash',
        'timestamp',
        'type'
    ]; 

    //在資料庫記錄驗證信資訊驗證碼、時間戳
    public function recordSendEmailInformation($userId, $hash, $type) {
        $timestamp = time();
        $query = "INSERT INTO requests VALUES (NULL, ?, ?, ?, ?)";
        $params = [$userId, $hash, $timestamp, $type];
        $result = DB::insert($query, $params);

        return (bool) $result;
    }
    
    //找出請求的使用者驗證碼、時間戳
    public function findSendEmailInformation($requestId, $type) {
        $query = "SELECT user,hash,timestamp FROM requests WHERE id=? AND type=?";
        $params = [$requestId, $type];
      
        return DB::select($query, $params)[0] ?? null;
    }

    //清除請求紀錄
    public function clearRequestRecord($userId, $type) {
        $query = 'DELETE FROM requests WHERE user=? AND type=?';
        $params = [$userId, $type];
        $result = DB::delete($query, $params);

        return $result > 0;
    }
}
