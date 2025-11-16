<?php

namespace App\Services;

use App\Enums\Priority;
use App\Models\Gateway;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class GatewayService
{
    public function change_priority(int $gateway_id, int $priority): void
    {
        DB::transaction(function () use ($gateway_id, $priority) {
            $gateway = Gateway::find($gateway_id);
            if (!$gateway) throw new BadRequestException('Gateway not found.');
            $gateway->update([
                'priority' => Priority::from($priority),
            ]);
        });
    }

    public function activate_or_deactivate(int $gateway_id, bool $activate): void
    {
        DB::transaction(function () use ($gateway_id, $activate) {
            $gateway = Gateway::find($gateway_id);
            if (!$gateway) throw new BadRequestException('Gateway not found.');
            if ($gateway->is_active === $activate) throw new BadRequestException('Gateway is already in that status.');
            $gateway->update([
                'is_active' => $activate,
            ]);
        });
    }
}
