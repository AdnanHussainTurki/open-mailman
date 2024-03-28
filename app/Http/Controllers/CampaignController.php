<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\MailingList;
use App\Models\Message;
use App\Models\Messenger;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    function index()
    {
        $campaigns = Campaign::all();
        return view('campaign.index', compact('campaigns'));
    }
    function create(Request $request)
    {
        return view('campaign.create');
    }
    function store(Request $request)
    {
        $tz = CarbonTimeZone::createFromHourOffset($request->timezone);
        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'messenger' => 'required',
            'rate_limiting_seconds' => 'required',
            'content' => 'required',
            'subject' => 'required',
            'scheduled_at' => 'required',
            'lists' => 'required',
            'tags' => 'required|array',


        ]);
        if (!$validated) {
            return redirect()->route('messenger.create')->with('error', 'There seems to be some issue with the your provided entries. Please try again.');
        }
        // Convert scheduled_at to UTC

        $scheduledAt = Carbon::parse($request->scheduled_at, $tz)->setTimezone('UTC');
        // Make sure all the lists are valid
        $listIds = json_decode($request->lists);
        if (empty($listIds)) {
            return redirect()->route('campaign.create')->with('error', 'There seems to be some issue with the your selected mailing lists. Please try again.');
        }
        $lists = MailingList::whereIn('id', $listIds)->get();
        if ($lists->count() != count($listIds)) {
            return redirect()->route('campaign.create')->with('error', 'There seems to be some issue with the your selected mailing lists. Please try again.');
        }
        // Make sure the messenger is valid
        $messenger = Messenger::find($request->messenger);
        if (!$messenger) {
            return redirect()->route('campaign.create')->with('error', 'There seems to be some issue with messenger selected. Please try again.');
        }

        // Create the campaign
        $campaign = new Campaign();
        $campaign->uuid = \Str::uuid();
        $campaign->type = $request->type;
        $campaign->name = $request->name;
        $campaign->messenger_id = $request->messenger;
        $campaign->rate_limiting_in_seconds = $request->rate_limiting_seconds;
        $campaign->subject = $request->subject;
        $campaign->content = $request->content;
        $campaign->scheduled_at = $scheduledAt;
        $campaign->status = 'draft';
        $campaign->slug = \Str::slug($request->name . '-' . \Str::random(5));
        $campaign->tags = json_encode($request->tags);
        $campaign->save();
        $campaign->lists()->attach($lists->pluck('id')->toArray());
        return redirect()->route('campaigns')->with('success', 'Campaign created successfully.');
    }
    function edit(Campaign $campaign, Request $request)
    {
        return view('campaign.edit', compact('campaign'));
    }
    function update(Campaign $campaign, Request $request)
    {
        $tz = CarbonTimeZone::createFromHourOffset($request->timezone);
        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'messenger' => 'required',
            'rate_limiting_seconds' => 'required',
            'content' => 'required',
            'subject' => 'required',
            'scheduled_at' => 'required',
            'lists' => 'required',
            'tags' => 'required|array',


        ]);
        if (!$validated) {
            return redirect()->route('messenger.create')->with('error', 'There seems to be some issue with the your provided entries. Please try again.');
        }
        // Convert scheduled_at to UTC

        $scheduledAt = Carbon::parse($request->scheduled_at, $tz)->setTimezone('UTC');
        // Make sure all the lists are valid
        $listIds = json_decode($request->lists);
        if (empty($listIds)) {
            return redirect()->route('campaign.create')->with('error', 'There seems to be some issue with the your selected mailing lists. Please try again.');
        }
        $lists = MailingList::whereIn('id', $listIds)->get();
        if ($lists->count() != count($listIds)) {
            return redirect()->route('campaign.create')->with('error', 'There seems to be some issue with the your selected mailing lists. Please try again.');
        }
        // Make sure the messenger is valid
        $messenger = Messenger::find($request->messenger);
        if (!$messenger) {
            return redirect()->route('campaign.create')->with('error', 'There seems to be some issue with messenger selected. Please try again.');
        }



        $campaign->type = $request->type;
        $campaign->name = $request->name;
        $campaign->messenger_id = $request->messenger;
        $campaign->rate_limiting_in_seconds = $request->rate_limiting_seconds;
        $campaign->subject = $request->subject;
        $campaign->content = $request->content;
        $campaign->scheduled_at = $scheduledAt;
        $campaign->status = 'draft';
        $campaign->slug = \Str::slug($request->name . '-' . \Str::random(5));
        $campaign->tags = json_encode($request->tags);
        $campaign->save();
        $campaign->lists()->attach($lists->pluck('id')->toArray());
        return redirect()->route('campaigns')->with('success', 'Campaign created successfully.');
    }

    public function show(Campaign $campaign)
    {
        return view('campaign.show', compact('campaign'));
    }

    public function delete(Campaign $campaign)
    {
        // Delete all the messages
        $campaign->messages()->delete();
        $campaign->delete();
        return redirect()->route('campaigns')->with('success', 'Campaign deleted successfully.');
    }

    public function history(Campaign $campaign)
    {
        $messages  = $campaign->messages()->orderBy('scheduled_at', 'desc')->get();
        return view('campaign.history', compact('campaign', 'messages'));
    }

    public function activate(Campaign $campaign)
    {
        $campaign->activate();
        return redirect()->route('campaign.history', $campaign)->with('success', 'Campaign activated successfully.');
    }

    // AJAX
    function test(Request $request)
    {
        $tz = CarbonTimeZone::createFromHourOffset($request->timezone);
        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'messenger' => 'required',
            'rate_limiting_seconds' => 'required',
            'content' => 'required',
            'subject' => 'required',
            'scheduled_at' => 'required',
            'lists' => 'required',
            'tags' => 'required|array',


        ]);
        if (!$validated) {
            return response()->json(['error' => 'There seems to be some issue with the your provided entries. Please try again.'], 400);
        }
        // Convert scheduled_at to UTC

        $scheduledAt = Carbon::parse($request->scheduled_at, $tz)->setTimezone('UTC');
        // Make sure all the lists are valid
        $listIds = json_decode($request->lists);
        if (empty($listIds)) {
            return response()->json(['error' => 'There seems to be some issue with the your selected mailing lists. Please try again.'], 400);
        }
        $lists = MailingList::whereIn('id', $listIds)->get();
        if ($lists->count() != count($listIds)) {
            return response()->json(['error' => 'There seems to be some issue with the your selected mailing lists. Please try again.'], 400);
        }
        // Make sure the messenger is valid
        $messenger = Messenger::find($request->messenger);
        if (!$messenger) {
            return response()->json(['error' => 'There seems to be some issue with messenger selected. Please try again.'], 400);
        }


        // Create message directly
        $message = new Message();
        $message->uuid = \Str::uuid();
        $message->messenger_id = $request->messenger;
        $message->type = 'test-message';
        $message->subject = $request->subject;
        $message->status = 'pending';
        $message->body = $request->content;
        $message->from = $messenger->from;
        $message->from_name = $messenger->from_name;
        $message->to = $request->email;
        $message->to_name = $request->name;
        $message->user_id = auth()->id();
        $message->scheduled_at = now();
        $message->status = 'pending';
        $message->save();
        $message->send();
        return response()->json(['message' => 'Test message sent successfully.'], 200);
    }
}
