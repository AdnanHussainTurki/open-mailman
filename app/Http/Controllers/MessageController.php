<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function changeStatus(Message $message, Request $request)
    {

        $message->status = $request->status;
        $message->save();
        return redirect()->back()->withInfo('Message status has been updated to ' . $message->status);
    }
}
