<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailingList extends Model
{
    use HasFactory;
    protected $table = 'mailing_lists';
    protected $fillable = ['name', 'description', 'tags'];

    public function subscribers()
    {
        return $this->belongsToMany(Subscriber::class, 'mailing_list_subscriber', 'mailing_list_id', 'subscriber_id');
    }

    public function getTagsAttribute($value)
    {
        return json_decode($value);
    }
}
