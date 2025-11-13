<?php

namespace App\Providers;

use App\Models\Gateway;
use App\Services\Processors\PaymentProcessor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentProcessor::class, function () {
            $gateways = Gateway::query()
                ->where('is_active', true)
                ->orderByDesc('priority')
                ->get();
            $instances = [];
            foreach ($gateways as $gateway) {
                if (!class_exists("\App\Services\Providers\\{$gateway->class_name}")) {
                    Log::warning("Implementation for {$gateway->name} not found.");
                    continue;
                }
                $instance = app("\App\Services\Providers\\{$gateway->class_name}");
                $instances[] = $instance;
            }
            return new PaymentProcessor($instances);
        });
    }
}
