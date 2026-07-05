<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WbsItem;
use App\Models\DailyPlan;
use App\Models\DailyFact;

class WorkforceController extends Controller
{
    // Frontend UI
    public function index()
    {
        return view('workforce');
    }

    // Get WBS Items (Lookup)
    public function getWbs()
    {
        return response()->json(WbsItem::all());
    }

    // Get Plans with Facts
    public function getPlans(Request $request)
    {
        $query = DailyPlan::with(['wbs', 'fact']);
        if ($request->has('role') && $request->role === 'hom') {
            $query->where('assigned_hom', $request->email);
        }
        return response()->json($query->orderBy('id', 'desc')->get());
    }

    // T-1: Create Plan
    public function storePlan(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'kkk' => 'required|string',
            'zzz_code' => 'required|exists:wbs_items,zzz_code',
            'planned_qty' => 'required|numeric',
            'planned_manday' => 'required|numeric',
            'hom' => 'required|email'
        ]);

        $plan = DailyPlan::create([
            'report_date' => $request->date,
            'kkk' => $request->kkk,
            'zzz_code' => $request->zzz_code,
            'planned_qty' => $request->planned_qty,
            'planned_manday' => $request->planned_manday,
            'assigned_hom' => $request->hom,
            'status' => 'ASSIGNED'
        ]);

        return response()->json(['success' => true, 'plan' => $plan]);
    }

    // T0: Offline Sync or Single Fact Entry
    public function syncFacts(Request $request)
    {
        $facts = $request->input('facts', []);
        
        foreach ($facts as $f) {
            // Idempotency check: if local_id exists, skip or update
            $existing = DailyFact::where('local_id', $f['local_id'])->first();
            if ($existing) continue;

            $fact = DailyFact::create([
                'plan_id' => $f['plan_id'],
                'fact_qty' => $f['fact_qty'],
                'overtime' => $f['overtime'],
                'crew_type' => $f['crew_type'],
                'comment' => $f['comment'] ?? null,
                'status' => 'PENDING_SC',
                'local_id' => $f['local_id']
            ]);

            // Update plan status
            $plan = DailyPlan::find($f['plan_id']);
            if ($plan) {
                $plan->update(['status' => 'PENDING_SC']);
            }
        }

        return response()->json(['success' => true]);
    }

    // Approve Fact
    public function approveFact($id)
    {
        $plan = DailyPlan::findOrFail($id);
        $plan->update(['status' => 'APPROVED']);
        
        if ($plan->fact) {
            $plan->fact->update(['status' => 'APPROVED']);
        }

        return response()->json(['success' => true]);
    }
}
