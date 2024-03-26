<?php

namespace App\Console\Commands;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all the 10 messages that are scheduled to be sent in next 60 seconds
        $messages = Message::where('scheduled_at', '<=', Carbon::now()->addSeconds(60))
            ->where('status', 'pending')
            ->orderBy('campaign_id')
            ->limit(10)
            ->get();
        foreach ($messages as $message) {
            // If the there 7 days passed since the message was scheduled
            if ($message->scheduled_at->diffInDays(Carbon::now()) >= 7) {
                // Mark the message as failed
                $message->status = 'failed';
                $message->save();
                continue;
            }
            // If the message try_count is greater than max_try_count
            if ($message->try_count >= $message->max_try_count) {
                // Mark the message as failed
                $message->status = 'failed';
                $message->save();
                continue;
            }
            // Increment the try_count
            $message->try_count++;
            // Send the message
            $message->send();
            // Wait till the rate_limiting_in_seconds before sending the next message
            $this->info('Waiting for ' . $message->campaign->rate_limiting_in_seconds . ' seconds before sending the next message');
            sleep($message->campaign->rate_limiting_in_seconds);
        }
    }
}
