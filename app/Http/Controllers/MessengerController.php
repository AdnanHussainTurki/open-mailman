<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Messenger;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class MessengerController extends Controller
{
    function index()
    {
        $messengers = Messenger::all();
        return view('messenger.index', compact('messengers'));
    }
    function create(Request $request)
    {
        return view('messenger.create');
    }
    function show(Messenger $messenger)
    {
        $messages = $messenger->messages()->orderBy('id', 'desc')->paginate(50);
        return view('messenger.show', compact('messenger', 'messages'));
    }
    function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);
        if (!$validated) {
            return redirect()->route('messenger.create')->with('error', 'There seems to be some issue with the your provided entries. Please try again.');
        }

        $messenger = new Messenger();
        $messenger->uuid =  Uuid::uuid4();
        $messenger->name = $request->name;
        $messenger->driver = $request->type;
        $messenger->host = $request->input("host", null);
        $messenger->port = $request->input("port", null);
        $messenger->username = $request->input("username");
        $messenger->password = $request->input("password");
        $messenger->from = $request->input("from", null);
        $messenger->from_name = $request->input("from_name", null);
        $messenger->meta = json_encode($request->input("meta", []));
        $messenger->code = \Str::random(10);
        $messenger->slug = \Str::slug($messenger->name . '-' . \Str::random(5));
        $messenger->save();

        return redirect()->route('messengers')->with('success', 'Messenger created successfully');
    }

    function edit(Messenger $messenger)
    {
        return view('messenger.edit', compact('messenger'));
    }

    function update(Request $request, Messenger $messenger)
    {
        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);
        if (!$validated) {
            return redirect()->route('messenger.edit', $messenger)->with('error', 'There seems to be some issue with the your provided entries. Please try again.');
        }

        $messenger->name = $request->name;
        $messenger->driver = $request->type;
        $messenger->host = $request->input("host", null);
        $messenger->port = $request->input("port", null);
        $messenger->username = $request->input("username");
        $messenger->password = $request->input("password");
        $messenger->from = $request->input("from", null);
        $messenger->from_name = $request->input("from_name", null);
        $messenger->meta = json_encode($request->input("meta", []));
        $messenger->save();

        // Redirect to the messenger show page
        return redirect()->route('messenger.show', $messenger)->with('success', 'Messenger updated successfully');
    }


    // API
    function sendMessage(Request $request)
    {
        $validated = $request->validate([

            'subject' => 'required',
            'body' => 'required',
            'to' => 'required',
            'to_name' => 'required',
            'messenger_slug' => 'required',
            'code' => 'required',

        ]);
        if (!$validated) {
            return response()->json(['error' => 'There seems to be some issue with the your provided entries. Please try again.'], 400);
        }
        // Get Messenger
        $messenger = Messenger::where('slug', $request->messenger_slug)->where('code', $request->code)->first();
        if (!$messenger) {
            return response()->json(['error' => 'Messenger not found'], 404);
        }
        Log::debug('Messenger found', ['messenger' => $messenger]);
        // Create Message
        $message = new Message();
        $message->uuid = \Str::uuid();
        $message->messenger_id = $messenger->id;
        $message->subject = $request->subject;
        $message->status = 'pending';
        $message->body = $request->body;
        $message->from = $messenger->from;
        $message->from_name = $messenger->from_name;
        $message->to = $request->to;
        $message->to_name = $request->to_name;
        $message->user_id = auth()->id();
        $message->scheduled_at = now();
        $message->type = 'direct';
        $message->save();
        Log::debug('Message created', ['message' => $message]);
        $message->send();
        return response()->json(['success' => 'Message sent successfully'], 200);
    }
}
