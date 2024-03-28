<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use Illuminate\Console\Command;

class AutoActivateDynamicCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-activate-dynamic-campaigns';

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
        // get dynamic campaigns which have lists
        $campaigns = Campaign::where('type', 'DYNAMIC')->whereHas('lists')
            ->get();
        foreach ($campaigns as $campaign) {
            // Get the subscribers of this campaign
            $subscribers = $campaign->lists->pluck('subscribers')->flatten();
            // unique subscribers
            $subscribers = $subscribers->unique('id');

            // Get the subscribers from messages
            $subscribersFromMessages = $campaign->messages->pluck('subscriber_id');
            // Get the subscribers which are not in the messages
            $subscribers = $subscribers->whereNotIn('id', $subscribersFromMessages);
            // If there are no subscribers then skip this campaign
            if ($subscribers->count() == 0) {
                continue;
            }
            $this->info('Activating campaign ' . $campaign->name);
            $campaign->activate();
        }
    }
}
