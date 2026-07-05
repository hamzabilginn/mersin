<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * List messages in a specific channel or user DMs.
     */
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $limit = (int) $request->query('limit', 20);
        $offset = (int) $request->query('offset', 0);

        $query = Message::with(['sender', 'receiver']);

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('channel', 'general')
                  ->orWhere('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            });
        } else {
            $channel = $request->query('channel', 'general');
            $query->where('channel', $channel);
        }

        // Paginate latest messages, then reverse to display chronologically
        $messages = $query->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    /**
     * Store/sync a message. Handles receiver_id and offline_id.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sender_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'channel' => 'nullable|string',
            'receiver_id' => 'nullable|exists:users,id',
            'offline_id' => 'nullable|string',
        ]);

        // Prevent duplicates due to sync retries
        if (!empty($validated['offline_id'])) {
            $existing = Message::where('offline_id', $validated['offline_id'])->first();
            if ($existing) {
                return response()->json($existing->load(['sender', 'receiver']), 200);
            }
        }

        $message = Message::create([
            'sender_id' => $validated['sender_id'],
            'content' => $validated['content'],
            'channel' => $validated['receiver_id'] ? null : ($validated['channel'] ?? 'general'),
            'receiver_id' => $validated['receiver_id'] ?? null,
            'offline_id' => $validated['offline_id'] ?? null,
        ]);

        return response()->json($message->load(['sender', 'receiver']), 201);
    }
}
