<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AIController extends Controller
{
    /**
     * Handle chatbot queries using Gemini API or offline fallback.
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $message = $validated['message'];
        $user = User::findOrFail($validated['user_id']);

        // Gather real-time project statistics & status context
        $today = Carbon::today()->format('Y-m-d');
        $tasks = Task::with(['worker', 'planner', 'manager'])->get();
        
        $totalCount = $tasks->count();
        $pendingCount = $tasks->where('status', 'pending')->count();
        $inProgressCount = $tasks->where('status', 'in_progress')->count();
        $waitingCount = $tasks->where('status', 'waiting_approval')->count();
        $approvedCount = $tasks->where('status', 'approved')->count();
        $rejectedCount = $tasks->where('status', 'rejected')->count();

        // Identify delayed tasks
        $delayedTasks = $tasks->where('status', '!=', 'approved')->where('due_date', '<', $today);
        $delayedCount = $delayedTasks->count();
        $delayedList = $delayedTasks->map(function ($t) {
            return "- \"{$t->title}\" (Atanan: {$t->worker->name}, Bitiş: {$t->due_date})";
        })->implode("\n");

        // Identify tasks waiting approval
        $waitingTasks = $tasks->where('status', 'waiting_approval');
        $waitingList = $waitingTasks->map(function ($t) {
            return "- \"{$t->title}\" (Atanan: {$t->worker->name}, Bitiş: {$t->due_date})";
        })->implode("\n");

        // Team list
        $team = User::all()->map(function ($u) {
            return "- {$u->name} (Rol: {$u->role}, E-posta: {$u->email})";
        })->implode("\n");

        // Construct System Instruction context
        $systemPrompt = "Sen 'Saha Görev Yönetim ve Otomasyon Sistemi' asistanısın. Adın 'Saha Asistanı AI'. Kullanıcının şantiye sahası, görevler, gecikmeler ve iş akışları hakkındaki sorularını elindeki güncel verilere dayanarak cevaplamalısın. Türkçe konuşmalı ve şantiye şefi tonunda profesyonel, net, yapıcı ve kısa cevaplar vermelisin.
        
Mevcut şantiye durumu ve verileri şöyledir:
- Toplam Görev Sayısı: {$totalCount}
  * Bekleyen: {$pendingCount}
  * Devam Eden: {$inProgressCount}
  * Onay Bekleyen: {$waitingCount}
  * Onaylanan (Kapatılan): {$approvedCount}
  * Reddedilen: {$rejectedCount}
- Gecikmiş (Teslim tarihi geçmiş ama onaylanmamış) Görev Sayısı: {$delayedCount}
Gecikmiş Görevler Listesi:
" . ($delayedList ?: "Gecikmiş görev bulunmuyor.") . "

- Onay Bekleyen Görevler Listesi:
" . ($waitingList ?: "Onay bekleyen görev bulunmuyor.") . "

Ekip Üyeleri:
{$team}

Kullanıcı Bilgisi: {$user->name} (Rolü: {$user->role}). Soru sorarken bu kullanıcının rolüne göre de hitap edebilirsin.

Cevaplarında Markdown biçimlendirmesi (kalın yazı, listeler vb.) kullanabilirsin. Kısa ve doğrudan cevaba odaklan. İnternete erişimin yok, 100% lokal çalışıyorsun.";

        $apiKey = env('GEMINI_API_KEY');

        // Check if API Key is set and we can make an HTTP request
        if (!empty($apiKey)) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => "System Instructions:\n" . $systemPrompt . "\n\nUser Question: " . $message]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($reply) {
                        return response()->json([
                            'reply' => trim($reply),
                            'source' => 'gemini-api'
                        ]);
                    }
                }
                
                Log::warning('Gemini API call returned unsuccessful response: ' . $response->body());
            } catch (\Exception $e) {
                Log::error('Failed to communicate with Gemini API: ' . $e->getMessage());
            }
        }

        // Fallback: Rules-based Offline Assistant Generator
        $reply = $this->generateOfflineFallback($message, $user, $delayedCount, $delayedTasks, $waitingCount, $waitingTasks, $totalCount);

        return response()->json([
            'reply' => $reply,
            'source' => 'offline-fallback'
        ]);
    }

    /**
     * Rules-based responses to handle common queries without internet/API connection.
     */
    private function generateOfflineFallback($message, $user, $delayedCount, $delayedTasks, $waitingCount, $waitingTasks, $totalCount)
    {
        $messageLower = mb_strtolower($message, 'UTF-8');

        if (str_contains($messageLower, 'gecik') || str_contains($messageLower, 'geciken') || str_contains($messageLower, 'gecikmiş')) {
            if ($delayedCount > 0) {
                $list = $delayedTasks->map(function ($t) {
                    return "- **{$t->title}** (Atanan: *{$t->worker->name}*, Bitiş Tarihi: {$t->due_date})";
                })->implode("\n");
                return "Şu an sahada gecikmiş durumda olan **{$delayedCount}** adet görev var:\n\n{$list}\n\nBu görevlerin durumlarını güncellemeleri için saha ekibiyle iletişime geçmenizi öneririm.";
            } else {
                return "Harika haber! Şu an teslim tarihi geçmiş gecikmiş bir görev bulunmuyor.";
            }
        }

        if (str_contains($messageLower, 'onay') || str_contains($messageLower, 'bekleyen')) {
            if ($waitingCount > 0) {
                $list = $waitingTasks->map(function ($t) {
                    return "- **{$t->title}** (Çalışan: *{$t->worker->name}*, Yönetici Onayı Bekliyor)";
                })->implode("\n");
                return "Şu anda yöneticilerin onaylamasını bekleyen **{$waitingCount}** adet görev var:\n\n{$list}\n\nLütfen ilgili yöneticilerin bu görevleri onaylamasını veya reddetmesini sağlayın.";
            } else {
                return "Şu anda onay bekleyen herhangi bir görev bulunmuyor. Tüm iş akışları güncel.";
            }
        }

        if (str_contains($messageLower, 'görev') || str_contains($messageLower, 'durum') || str_contains($messageLower, 'istatistik')) {
            return "Sahadaki genel durum şu şekildedir:\n" .
                   "- Toplam görev sayısı: **{$totalCount}**\n" .
                   "- Onay bekleyen görevler: **{$waitingCount}**\n" .
                   "- Geciken görevler: **{$delayedCount}**\n\n" .
                   "Daha detaylı bilgi almak için 'geciken görevler' veya 'onay bekleyen görevler' yazabilirsiniz.";
        }

        if (str_contains($messageLower, 'merhaba') || str_contains($messageLower, 'selam')) {
            return "Merhaba **{$user->name}**! Ben şantiye yapay zeka asistanıyım. Çevrimdışı yerel modda çalışıyorum. Sana sahadaki görevlerin durumları, gecikmeler ve onay bekleyen işler konusunda bilgi verebilirim. Nasıl yardımcı olabilirim?";
        }

        // Default offline chat helper message
        return "Çevrimdışı yerel modda çalışıyorum ve sorduğunuz soruyu tam olarak analiz edemedim. Ancak bana şu konularda sorular sorabilirsiniz:\n\n" .
               "- *'Geciken görevler hangileri?'*\n" .
               "- *'Onay bekleyen görevler neler?'*\n" .
               "- *'Sahadaki genel görev durumu nedir?'*\n\n" .
               "Gemini API bağlantınız etkinleştirildiğinde genel sorularınızı da yanıtlayabilirim.";
    }

    /**
     * Get AI chat messages for a user.
     */
    public function getMessages(Request $request)
    {
        $userId = $request->query('user_id');
        
        if (!$userId) {
            return response()->json(['error' => 'user_id is required'], 400);
        }

        $messages = \App\Models\AIMessage::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Store an AI chat message.
     */
    public function storeMessage(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'sender' => 'required|in:self,ai',
            'text' => 'required|string',
            'time' => 'required|string',
        ]);

        $message = \App\Models\AIMessage::create($validated);

        return response()->json($message, 201);
    }
}
