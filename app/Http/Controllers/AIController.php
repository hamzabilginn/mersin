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
        $messageLower = mb_strtolower($message, 'UTF-8');

        // ==========================================
        // ACTION AGENT: INTERCEPT COMMANDS
        // ==========================================
        
        // 1. Tech Office Action: Assign Task (İş Ata)
        if ($user->role === 'tech_office' && (str_contains($messageLower, 'ata') || str_contains($messageLower, 'planla'))) {
            preg_match('/(\d{8})/', $messageLower, $matches);
            $zzzCode = $matches[1] ?? '60114402'; // Default if not found but intent is matched
            
            $hom = User::where('role', 'hom')->first();
            $sc = User::where('role', 'sc')->first();
            $pm = User::where('role', 'pm')->first();

            if ($hom) {
                Task::create([
                    'zzz_code' => $zzzCode,
                    'tow' => 'TOW-AI',
                    'stow' => 'STOW-AI',
                    'sstow' => 'SSTOW-AI',
                    'planned_qty' => 10,
                    'planned_man_day' => 2,
                    'status' => 'assigned',
                    'tech_office_id' => $user->id,
                    'hom_id' => $hom->id,
                    'sc_id' => $sc ? $sc->id : null,
                    'pm_id' => $pm ? $pm->id : null,
                    'due_date' => Carbon::tomorrow()->format('Y-m-d'),
                ]);
                return response()->json([
                    'reply' => "✅ İşleminiz tamamlandı! **{$zzzCode}** numaralı ZZZ iş paketi yarın için sahadaki **{$hom->name}** (HoM) ustasına başarıyla atandı.",
                    'source' => 'action-agent'
                ]);
            }
        }

        // 2. HoM Action: Complete Task (İşi Bitir)
        if ($user->role === 'hom' && (str_contains($messageLower, 'bitir') || str_contains($messageLower, 'tamamla'))) {
            $task = Task::where('hom_id', $user->id)->whereIn('status', ['assigned', 'in_progress'])->first();
            if ($task) {
                $task->update(['status' => 'pending_sc', 'fact_qty' => $task->planned_qty ?? 10, 'fact_man_day' => $task->planned_man_day ?? 2]);
                return response()->json([
                    'reply' => "✅ Tebrikler! Üzerinizdeki **{$task->zzz_code}** numaralı iş paketi 'Gerçekleşen' olarak sisteme girildi ve Şantiye Şefi (SC) onayına sunuldu.",
                    'source' => 'action-agent'
                ]);
            }
        }

        // 3. SC / PM Action: Approve Task (Onayla)
        if (in_array($user->role, ['sc', 'pm']) && (str_contains($messageLower, 'onayla') || str_contains($messageLower, 'kabul et'))) {
            $statusToLookFor = $user->role === 'sc' ? 'pending_sc' : 'pending_pm';
            $nextStatus = $user->role === 'sc' ? 'pending_pm' : 'approved';
            
            $tasksToApprove = Task::where('status', $statusToLookFor)->get();
            if ($tasksToApprove->count() > 0) {
                foreach ($tasksToApprove as $t) {
                    $t->update(['status' => $nextStatus]);
                }
                return response()->json([
                    'reply' => "✅ Başarılı! Onay bekleyen **{$tasksToApprove->count()}** adet görev incelendi ve sizin tarafınızdan onaylanarak bir sonraki aşamaya geçirildi.",
                    'source' => 'action-agent'
                ]);
            } else {
                return response()->json([
                    'reply' => "Şu an masanızda onay bekleyen herhangi bir görev bulunmuyor.",
                    'source' => 'action-agent'
                ]);
            }
        }

        // Gather real-time project statistics & status context
        $today = Carbon::today()->format('Y-m-d');
        $tasks = Task::with(['hom', 'techOffice', 'sc', 'pm'])->get();
        
        $totalCount = $tasks->count();
        $assignedCount = $tasks->where('status', 'assigned')->count();
        $inProgressCount = $tasks->where('status', 'in_progress')->count();
        $waitingCount = $tasks->whereIn('status', ['pending_sc', 'pending_pm'])->count();
        $approvedCount = $tasks->where('status', 'approved')->count();
        $rejectedCount = $tasks->where('status', 'rejected')->count();

        // Identify delayed tasks (T0 date passed)
        $delayedTasks = $tasks->where('status', '!=', 'approved')->where('due_date', '<', $today);
        $delayedCount = $delayedTasks->count();
        $delayedList = $delayedTasks->map(function ($t) {
            $name = $t->zzz_code !== '0' ? $t->zzz_code : $t->tow;
            $hom = $t->hom ? $t->hom->name : 'Atanmamış';
            return "- \"{$name}\" (HoM: {$hom}, T0: {$t->due_date})";
        })->implode("\n");

        // Identify tasks waiting approval
        $waitingTasks = $tasks->whereIn('status', ['pending_sc', 'pending_pm']);
        $waitingList = $waitingTasks->map(function ($t) {
            $name = $t->zzz_code !== '0' ? $t->zzz_code : $t->tow;
            $hom = $t->hom ? $t->hom->name : 'Atanmamış';
            return "- \"{$name}\" (HoM: {$hom}, T0: {$t->due_date})";
        })->implode("\n");

        // Team list
        $team = User::all()->map(function ($u) {
            return "- {$u->name} (Rol: {$u->role}, E-posta: {$u->email})";
        })->implode("\n");

        // Construct System Instruction context
        $systemPrompt = "Sen 'Workforce Execution Platform' (Saha Gücü Otomasyon Sistemi) asistanısın. Adın 'Saha Asistanı AI'. Kullanıcının şantiye sahası, görevler, gecikmeler ve iş akışları hakkındaki sorularını elindeki güncel verilere dayanarak cevaplamalısın. Türkçe konuşmalı ve profesyonel, net, yapıcı cevaplar vermelisin. Uygulama ICN Vaka Çalışması mantığıyla çalışmaktadır (Tech Office, HoM, SC, PM rolleri ve ZZZ kodları vardır).
        
Mevcut şantiye durumu ve verileri şöyledir:
- Toplam Görev Sayısı: {$totalCount}
  * Atanan (Planlanan): {$assignedCount}
  * Sahada Devam Eden: {$inProgressCount}
  * SC/PM Onayı Bekleyen: {$waitingCount}
  * Onaylanan (Kapatılan): {$approvedCount}
  * Reddedilen: {$rejectedCount}
- Gecikmiş (T0 tarihi geçmiş) Görev Sayısı: {$delayedCount}
Gecikmiş Görevler Listesi:
" . ($delayedList ?: "Gecikmiş görev bulunmuyor.") . "

- Onay Bekleyen Görevler Listesi:
" . ($waitingList ?: "Onay bekleyen görev bulunmuyor.") . "

Ekip Üyeleri:
{$team}

Kullanıcı Bilgisi: {$user->name} (Rolü: {$user->role}). Soru sorarken bu kullanıcının rolüne göre de hitap edebilirsin.

Cevaplarında Markdown biçimlendirmesi kullanabilirsin. İnternete erişimin yok, 100% lokal çalışıyorsun.";

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
                    $name = $t->zzz_code !== '0' ? $t->zzz_code : $t->tow;
                    $hom = $t->hom ? $t->hom->name : 'Atanmamış';
                    return "- **{$name}** (HoM: *{$hom}*, T0 Bitiş: {$t->due_date})";
                })->implode("\n");
                return "Şu an sahada gecikmiş durumda olan **{$delayedCount}** adet görev var:\n\n{$list}\n\nBu görevlerin Fact Qty verilerinin girilmesi için ilgili HoM'lar ile iletişime geçmenizi öneririm.";
            } else {
                return "Harika haber! Şu an T0 tarihi geçmiş gecikmiş bir görev bulunmuyor.";
            }
        }

        if (str_contains($messageLower, 'onay') || str_contains($messageLower, 'bekleyen')) {
            if ($waitingCount > 0) {
                $list = $waitingTasks->map(function ($t) {
                    $name = $t->zzz_code !== '0' ? $t->zzz_code : $t->tow;
                    $hom = $t->hom ? $t->hom->name : 'Atanmamış';
                    return "- **{$name}** (HoM: *{$hom}*, Onay Bekliyor)";
                })->implode("\n");
                return "Şu anda SC veya PM onayını bekleyen **{$waitingCount}** adet görev var:\n\n{$list}\n\nLütfen ilgili yöneticilerin bu görevleri kontrol edip onaylamasını sağlayın.";
            } else {
                return "Şu anda onay bekleyen herhangi bir görev bulunmuyor. Tüm iş akışları güncel.";
            }
        }

        if (str_contains($messageLower, 'görev') || str_contains($messageLower, 'durum') || str_contains($messageLower, 'istatistik')) {
            return "Sahadaki genel ZZZ iş paketi durumu şu şekildedir:\n" .
                   "- Toplam paket sayısı: **{$totalCount}**\n" .
                   "- Onay bekleyenler: **{$waitingCount}**\n" .
                   "- Gecikenler: **{$delayedCount}**\n\n" .
                   "Daha detaylı bilgi almak için 'geciken görevler' veya 'onay bekleyen görevler' yazabilirsiniz.";
        }

        if (str_contains($messageLower, 'merhaba') || str_contains($messageLower, 'selam')) {
            return "Merhaba **{$user->name}**! Ben şantiye yapay zeka asistanıyım. Çevrimdışı yerel modda çalışıyorum. Sana ZZZ kodları, gecikmeler ve onay bekleyen iş paketleri konusunda bilgi verebilirim. Nasıl yardımcı olabilirim?";
        }

        // Default offline chat helper message
        return "Çevrimdışı (Offline) yerel modda çalışıyorum ve sorduğunuz soruyu tam olarak analiz edemedim. Ancak bana şu konularda sorular sorabilirsiniz:\n\n" .
               "- *'Geciken görevler hangileri?'*\n" .
               "- *'Onay bekleyenler neler?'*\n" .
               "- *'Sahadaki genel durum nedir?'*\n\n" .
               "Gemini API bağlantınız etkinleştirildiğinde çok daha akıllı cevaplar verebilirim.";
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
