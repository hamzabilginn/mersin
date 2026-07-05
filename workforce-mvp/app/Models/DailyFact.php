<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DailyFact extends Model {
    protected $guarded = [];
    public function plan() { return $this->belongsTo(DailyPlan::class, 'plan_id'); }
}
