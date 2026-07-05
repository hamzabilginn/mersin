<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\WbsItem;
class WbsSeeder extends Seeder {
    public function run(): void {
        WbsItem::insert([
            ['zzz_code'=>'60114402', 'tow'=>'TOW-02', 'stow'=>'STOW-23', 'sstow'=>'SSTOW-77', 'unit'=>'t'],
            ['zzz_code'=>'60100101', 'tow'=>'TOW-01', 'stow'=>'STOW-01', 'sstow'=>'SSTOW-01', 'unit'=>'m3'],
            ['zzz_code'=>'60155555', 'tow'=>'TOW-17', 'stow'=>'STOW-155', 'sstow'=>'SSTOW-455', 'unit'=>'m2'],
        ]);
    }
}
