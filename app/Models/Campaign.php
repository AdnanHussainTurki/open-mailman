<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function lists()
    {
        return $this->belongsToMany(MailingList::class, 'campaign_list', 'campaign_id', 'mailing_list_id');
    }

    public function messenger()
    {
        return $this->belongsTo(Messenger::class);
    }

    public function getTagsAttribute($value)
    {
        return json_decode($value);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function activate()
    {
        $type = $this->type;
        if ($type == 'STANDARD') {
            $this->activateStandard();
        } else if ($type == 'DYNAMIC') {
            $this->activateDynamic();
        } else {
            throw new \Exception('Invalid this type');
        }
    }

    public function activateStandard()
    {
        $subscribers = $this->lists->pluck('subscribers')->flatten();
        // unique subscribers
        $subscribers = $subscribers->unique('id');
        $startFrom = $this->scheduled_at;
        if ($startFrom < now()) {
            $startFrom = now()->addSeconds(60);
        }
        $subscribers->each(function ($subscriber) use ($startFrom) {
            $message = new Message();
            $message->uuid = \Str::uuid();
            $message->messenger_id = $this->messenger_id;
            $message->type = 'this';
            $message->subject = $this->subject;

            $message->status = 'pending';
            $message->body = $this->content;
            $message->from = $this->messenger->from;
            $message->from_name = $this->messenger->from_name;
            $message->to = $subscriber->email;
            $message->to_name = $subscriber->name;
            $message->user_id = auth()->id();
            $message->scheduled_at = $startFrom;
            $message->campaign_id = $this->id;
            $message->subscriber_id = $subscriber->id;
            $message->status = 'pending';
            $message->save();
            $startFrom = $startFrom->addSeconds($this->rate_limiting_in_seconds);
        });

        $this->status = 'sending';
        $this->save();
    }



    public function activateDynamic()
    {
        $subscribers = $this->lists->pluck('subscribers')->flatten();
        $subscribers = $subscribers->unique('id');

        $startFrom = now()->addSeconds(60);

        $subscribers->each(function ($subscriber) use ($startFrom) {
            // If the subscriber's message is already sent, skip
            if ($this->messages()->where('subscriber_id', $subscriber->id)->count() > 0) {
                return;
            }

            $message = new Message();
            $message->uuid = \Str::uuid();
            $message->messenger_id = $this->messenger_id;
            $message->type = 'this';
            $message->subject = $this->subject;

            $message->status = 'pending';
            $message->body = $this->content;
            $message->from = $this->messenger->from;
            $message->from_name = $this->messenger->from_name;
            $message->to = $subscriber->email;
            $message->to_name = $subscriber->name;
            $message->user_id = auth()->id();
            $message->scheduled_at = $startFrom;
            $message->campaign_id = $this->id;
            $message->subscriber_id = $subscriber->id;
            $message->status = 'pending';
            $message->save();
            $startFrom = $startFrom->addSeconds($this->rate_limiting_in_seconds);
        });

        $this->status = 'sending';
        $this->save();
    }
}
