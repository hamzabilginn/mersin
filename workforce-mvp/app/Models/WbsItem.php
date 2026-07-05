<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WbsItem extends Model {
    protected $primaryKey = 'zzz_code';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
