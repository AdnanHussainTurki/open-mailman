<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriberController extends Controller
{
    public function create()
    {
        return view('subscriber.create');
    }

    public function store(Request $request)
    {
        // {
        //     "_token": "9SV09LjyKhE4kFgJfR8y252lWBa6wCOTBoqSTJiZ",
        //     "name": "John Doe",
        //     "email": "adnanhussainturki@gmail.com",
        //     "secondary_email": null,
        //     "mobile": null,
        //     "secondary_mobile": null,
        //     "telegram_username": null,
        //     "secondary_telegram_username": null
        // }
        $keys = [
            'email',
            'secondary_email',
            'mobile',
            'secondary_mobile',
            'telegram_username',
            'secondary_telegram_username',
        ];
        // Atleast one of the keys should have a value
        $hasValue = false;
        foreach ($keys as $key) {
            if ($request->has($key)) {
                $hasValue = true;
                break;
            }
        }
        if (!$hasValue) {
            return redirect()->back()->with('error', 'Atleast one of the fields should have a value');
        }


        $subscriber = new Subscriber();
        $subscriber->name = $request->name;
        $subscriber->email = $request->email;
        $subscriber->secondary_email = $request->secondary_email;
        $subscriber->mobile = $request->mobile;
        $subscriber->secondary_mobile = $request->secondary_mobile;
        $subscriber->telegram_username = $request->telegram_username;
        $subscriber->secondary_telegram_username = $request->secondary_telegram_username;
        $subscriber->tags = json_encode($request->tags);
        $subscriber->save();

        return redirect()->route('subscribers')->withSuccess('Subscriber created successfully');
    }

    public function deactivate(Subscriber $subscriber)
    {
        $subscriber->active = false;
        $subscriber->save();

        return redirect()->route('subscribers');
    }

    // API
    // POST /api/subscribers/updateOrCreate
    public function updateOrCreate(Request $request)
    {
        $keys = [
            'email',
            'secondary_email',
            'mobile',
            'secondary_mobile',
            'telegram_username',
            'secondary_telegram_username',
        ];
        // Atleast one of the keys should have a value
        $hasValue = false;
        foreach ($keys as $key) {
            if ($request->has($key)) {
                $hasValue = true;
                break;
            }
        }
        if (!$hasValue) {
            return response()->json(['error' => 'Atleast one of the fields should have a value'], 400);
        }

        $subscriber = Subscriber::updateOrCreate(
            ['external_id' => $request->external_id],
            [
                'name' => $request->name,
                'email' => $request->email,
                'secondary_email' => $request->secondary_email,
                'mobile' => $request->mobile,
                'secondary_mobile' => $request->secondary_mobile,
                'telegram_username' => $request->telegram_username,
                'secondary_telegram_username' => $request->secondary_telegram_username,
                'tags' => json_encode($request->tags),
            ]
        );
        return response()->json($subscriber, 200);
    }
    public function differenceExternalIds(Request $request)
    {

        $externalIds = $request->external_ids;
        // Make chunks of 100 external ids
        $externalIdChunked = array_chunk($externalIds, 100);

        Log::info('External Ids', $externalIdChunked);
        $subscribers = [];
        foreach ($externalIdChunked as $key => $externalIdsChunk) {
            $subscribersChunk = Subscriber::whereIn('external_id', ($externalIdsChunk))->get(['id']);
            $subscribers = array_merge($subscribers, array_column($subscribersChunk->toArray(), 'id'));
        }


        $newExternalIds = array_diff($externalIds, $subscribers);

        return response()->json(($newExternalIds), 200);
    }
}
