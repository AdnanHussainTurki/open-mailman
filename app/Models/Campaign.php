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
}
