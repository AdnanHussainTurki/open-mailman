<?php

namespace App\Http\Controllers;

use App\Models\MailingList;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ListController extends Controller
{
    public function index()
    {
        $lists = MailingList::all();
        return view('lists.index', compact('lists'));
    }
    public function create()
    {
        return view('lists.create');
    }
    public function show(MailingList $list)
    {
        $subscribers = $list->subscribers()->orderBy("id")->paginate(300);
        return view('lists.show', compact('list', 'subscribers'));
    }


    public function store(Request $request)
    {
        //     "subscribers" => "["1","2"]"
        //   "name" => "Test Mailing List"
        //   "tags" => array:1 [▼
        //     0 => "General"
        //   ]

        // Validate the request
        $request->validate([
            'name' => 'required',
            'subscribers' => 'required',
            'tags' => 'required|array',
        ]);

        $subscribersId = json_decode($request->subscribers);
        if (empty($subscribersId)) {
            return redirect()->back()->with('error', 'Atleast one subscriber should be selected');
        }
        $subscribers = Subscriber::whereIn('id', $subscribersId)->get();
        if ($subscribers->count() !== count($subscribersId)) {
            return redirect()->back()->with('error', 'Invalid subscriber selected');
        }



        $list = new MailingList();
        $list->name = $request->name;
        $list->slug = \Str::slug($request->name . '-' . \Str::random(6));
        $list->code = \Str::random(10);
        $list->description = $request->description;
        $list->tags = json_encode($request->tags);
        $list->save();
        $list->subscribers()->sync($subscribersId);

        return redirect()->route('lists')->withSuccess('Mailing list created successfully');
    }

    // API
    // POST /api/list/subscribe
    public function subscribe(Request $request)
    {
        $code = $request->code;
        $mailingList = MailingList::where('slug', $request->slug)->where('code', $code)->first();
        if (!$mailingList) {
            return response()->json(['message' => 'Invalid mailing list'], 404);
        }
        $externalId = $request->external_id;
        $subscriber = Subscriber::where('external_id', $externalId)->first();
        if (!$subscriber) {
            return response()->json(['message' => 'Invalid subscriber'], 404);
        }
        // Check if the subscriber is already subscribed
        if ($mailingList->subscribers->contains($subscriber)) {
            return response()->json(['message' => 'Subscriber already subscribed'], 400);
        }
        $mailingList->subscribers()->attach($subscriber);
        return response()->json(['message' => 'Subscribed successfully']);
    }
    // POST
    // /api/list/unsubscribe-all
    public function unsubscribeAll(Request $request)
    {
        $code = $request->code;
        $mailingList = MailingList::where('slug', $request->slug)->where('code', $code)->first();
        if (!$mailingList) {
            return response()->json(['message' => 'Invalid mailing list'], 404);
        }
        $mailingList->subscribers()->sync([]);
        return response()->json(['message' => 'All unsubscribed successfully']);
    }
}
