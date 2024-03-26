<?php

namespace App\Http\Controllers;

use App\Models\Messenger;

use Illuminate\Http\Request;
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
        $messenger->save();

        return redirect()->route('messengers')->with('success', 'Messenger created successfully');
    }
}
