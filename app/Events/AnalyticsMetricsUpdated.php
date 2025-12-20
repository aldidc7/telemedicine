<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalyticsMetricsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $metrics;
    public $period;
    public $data;

    public function __construct($metrics, $period = 'hourly', $data = [])
    {
        $this->metrics = $metrics;
        $this->period = $period;
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('analytics.metrics'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'analytics-metrics-updated';
    }
}
