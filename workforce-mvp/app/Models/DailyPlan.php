<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DailyPlan extends Model {
    protected $guarded = [];
    public function wbs() { return $this->belongsTo(WbsItem::class, 'zzz_code', 'zzz_code'); }
    public function fact() { return $this->hasOne(DailyFact::class, 'plan_id'); }
}
