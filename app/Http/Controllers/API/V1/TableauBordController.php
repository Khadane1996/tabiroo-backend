<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TableauBordController extends Controller
{
     public function index($user_id)
    {
        $countPending = Reservation::where('chef_id', $user_id)
        ->where('status', 'pending')
        ->count();

        $now = Carbon::now();

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $totalRevenue = Reservation::where('chef_id', $user_id)
            ->where('status', 'completed')
            ->whereRaw("TO_DATE(SUBSTRING(date_prestation, 1, 10), 'YYYY-MM-DD') BETWEEN ? AND ?", [
                $startOfMonth,
                $endOfMonth
            ])
            ->sum('sous_total');

        $averageNote = DB::table('avis_clients')
            ->whereNotNull('menu_id')
            ->join('menus', 'avis_clients.menu_id', '=', 'menus.id')
            ->where('menus.user_id', $user_id)
            ->avg('avis_clients.note_client');

        $noteFormatted = $averageNote ? round($averageNote, 1) . '/5' : '0/5';

        return response()->json([
        'status' => true,
        'message' => 'Données du tableau de bord',
            'data' => [
                'pending' => $countPending,
                'totalRevenue' => $totalRevenue,
                'noteClient' => $noteFormatted
            ]
        ]);
    }
}
