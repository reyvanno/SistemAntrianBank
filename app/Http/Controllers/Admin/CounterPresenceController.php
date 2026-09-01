<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CounterPresenceController extends Controller
{
    /**
     * Presence dianggap valid selama 90 detik.
     *
     * Heartbeat dikirim setiap 30 detik.
     */
    private const PRESENCE_TTL = 90;

    /**
     * Tandai petugas sebagai online.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $request->user();

        /*
         * User tidak ditemukan.
         */
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
         * Admin tidak mempunyai counter.
         * Admin tidak perlu presence.
         */
        if (!$user->counter_id) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki loket.',
            ], 422);
        }

        /*
         * User yang dinonaktifkan oleh admin
         * tidak boleh dianggap online.
         */
        if (!$user->is_active) {
            Redis::del(
                $this->presenceKey($user->id)
            );

            return response()->json([
                'success' => false,
                'message' => 'User tidak aktif.',
            ], 403);
        }

        /*
         * Counter juga harus aktif.
         */
        if (!$user->counter?->is_active) {
            Redis::del(
                $this->presenceKey($user->id)
            );

            return response()->json([
                'success' => false,
                'message' => 'Loket tidak aktif.',
            ], 403);
        }

        /*
         * Simpan presence user di Redis.
         *
         * Setiap user mempunyai key sendiri.
         */
        Redis::setex(
            $this->presenceKey($user->id),
            self::PRESENCE_TTL,
            'online'
        );

        return response()->json([
            'success' => true,
            'counter_id' => $user->counter_id,
            'ttl' => self::PRESENCE_TTL,
        ]);
    }

    /**
     * Tandai petugas sebagai offline.
     */
    public function offline(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
            ], 401);
        }

        Redis::del(
            $this->presenceKey($user->id)
        );

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Redis key untuk presence user.
     */
    private function presenceKey(int $userId): string
    {
        return "counter_presence:user:{$userId}";
    }
}