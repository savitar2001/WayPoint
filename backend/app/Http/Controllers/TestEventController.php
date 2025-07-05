<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 

class TestEventController extends Controller
{
    public function fireTestEvent()
    {
        Log::info('TestEventController fireTestEvent method called. Attempting to create log file.'); // 確認日誌訊息
        event(new NewMessage(1));
        return "Event fired!";
    }
}
