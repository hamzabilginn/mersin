<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users for each Role based on Case Study
        $pm = User::create([
            'name' => 'Project Manager',
            'email' => 'pm@icn.com',
            'password' => Hash::make('password'),
            'role' => 'pm',
        ]);

        $scA = User::create([
            'name' => 'A Bolge Site Chief',
            'email' => 'abolge_sc@icn.com',
            'password' => Hash::make('password'),
            'role' => 'sc',
        ]);

        $scB = User::create([
            'name' => 'B Bolge Site Chief',
            'email' => 'bbolge_sc@icn.com',
            'password' => Hash::make('password'),
            'role' => 'sc',
        ]);

        $techA = User::create([
            'name' => 'A Bolge Tech Office',
            'email' => 'abolgetechoffice@icn.com',
            'password' => Hash::make('password'),
            'role' => 'tech_office',
        ]);

        $techB = User::create([
            'name' => 'B Bolge Tech Office',
            'email' => 'bbolgetechoffice@icn.com',
            'password' => Hash::make('password'),
            'role' => 'tech_office',
        ]);

        $homAAA = User::create([
            'name' => 'HoM 30AAA',
            'email' => '30AAA-hom@icn.com',
            'password' => Hash::make('password'),
            'role' => 'hom',
        ]);

        $homDDD = User::create([
            'name' => 'HoM 30DDD',
            'email' => '30DDD-hom@icn.com',
            'password' => Hash::make('password'),
            'role' => 'hom',
        ]);

        // 2. Create sample tasks in different states
        $task1 = DB::table('tasks')->insertGetId([
            'zzz_code' => '60114402',
            'tow' => 'TOW-02',
            'stow' => 'STOW-23',
            'sstow' => 'SSTOW-77',
            'planned_qty' => 4.92,
            'planned_man_day' => 33,
            'fact_qty' => 3.92,
            'fact_man_day' => 31,
            'overtime' => 0,
            'comment' => null,
            'status' => 'approved',
            'tech_office_id' => $techB->id,
            'hom_id' => $homDDD->id,
            'sc_id' => $scB->id,
            'pm_id' => $pm->id,
            'due_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subHours(5),
        ]);

        $task2 = DB::table('tasks')->insertGetId([
            'zzz_code' => '0',
            'tow' => 'TOW-17',
            'stow' => 'STOW-155',
            'sstow' => 'SSTOW-455',
            'planned_qty' => 0,
            'planned_man_day' => 4,
            'fact_qty' => -1,
            'fact_man_day' => 2,
            'overtime' => 0,
            'comment' => 'Malzeme tedariği geciktiği için işe başlanamadı.',
            'status' => 'pending_pm',
            'tech_office_id' => $techA->id,
            'hom_id' => $homAAA->id,
            'sc_id' => $scA->id,
            'pm_id' => $pm->id,
            'due_date' => Carbon::now()->addDay()->format('Y-m-d'),
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subHours(2),
        ]);

        $task3 = DB::table('tasks')->insertGetId([
            'zzz_code' => '60101508',
            'tow' => 'TOW-03',
            'stow' => 'STOW-51',
            'sstow' => 'SSTOW-146',
            'planned_qty' => 10,
            'planned_man_day' => 16,
            'fact_qty' => null,
            'fact_man_day' => null,
            'overtime' => null,
            'comment' => null,
            'status' => 'assigned',
            'tech_office_id' => $techA->id,
            'hom_id' => $homAAA->id,
            'sc_id' => $scA->id,
            'pm_id' => $pm->id,
            'due_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'created_at' => Carbon::now()->subDays(3),
            'updated_at' => Carbon::now()->subHours(4),
        ]);

        $task4 = DB::table('tasks')->insertGetId([
            'zzz_code' => '110101101',
            'tow' => 'TOW-01',
            'stow' => 'STOW-01',
            'sstow' => 'SSTOW-18',
            'planned_qty' => 50,
            'planned_man_day' => 10,
            'fact_qty' => 50,
            'fact_man_day' => 10,
            'overtime' => 2,
            'comment' => null,
            'status' => 'pending_sc',
            'tech_office_id' => $techB->id,
            'hom_id' => $homDDD->id,
            'sc_id' => $scB->id,
            'pm_id' => $pm->id,
            'due_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        // Seed logs for status transitions
        DB::table('task_logs')->insert([
            [
                'task_id' => $task4,
                'user_id' => $homDDD->id,
                'old_status' => 'in_progress',
                'new_status' => 'pending_sc',
                'comment' => 'Gün sonu verileri girildi, onaya sunuldu.',
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'task_id' => $task2,
                'user_id' => $scA->id,
                'old_status' => 'pending_sc',
                'new_status' => 'pending_pm',
                'comment' => 'Mazeret kabul edildi, PM onayına gönderildi.',
                'created_at' => Carbon::now()->subDays(2)->subHours(2),
            ],
            [
                'task_id' => $task1,
                'user_id' => $pm->id,
                'old_status' => 'pending_pm',
                'new_status' => 'approved',
                'comment' => 'Uygun bulunmuştur.',
                'created_at' => Carbon::now()->subDays(2),
            ]
        ]);

        // Seed channels messages
        DB::table('messages')->insert([
            [
                'sender_id' => $techA->id,
                'receiver_id' => null,
                'channel' => 'general',
                'content' => 'Hoş geldiniz! Workforce Execution Platform yayında.',
                'offline_id' => 'seed-msg-1',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ]
        ]);

        // Seed some notifications
        DB::table('notifications')->insert([
            [
                'user_id' => $homAAA->id,
                'title' => 'Yeni Plan Atandı',
                'message' => 'TOW-03 STOW-51 görevi Tech Office tarafından atandı.',
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(5),
                'updated_at' => Carbon::now()->subHours(5),
            ]
        ]);
    }
}
