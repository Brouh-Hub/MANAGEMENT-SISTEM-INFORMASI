<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogLogin;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LogLoginController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(LogLogin::with('user')->latest('id'))
                ->addIndexColumn()
                ->addColumn('waktu', fn ($r) => $r->created_at->format('d/m/Y H:i:s'))
                ->addColumn('nama', fn ($r) => e($r->user?->name ?? '-'))
                ->addColumn('status_badge', function ($r) {
                    $color = ['berhasil' => 'success', 'gagal' => 'warning', 'terkunci' => 'danger', 'logout' => 'secondary'][$r->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . e(ucfirst($r->status)) . '</span>';
                })
                ->editColumn('user_agent', fn ($r) => e(\Illuminate\Support\Str::limit($r->user_agent, 60)))
                ->rawColumns(['status_badge', 'nama', 'user_agent'])
                ->make(true);
        }

        return view('admin.log-login.index', [
            'title'    => 'Log Aktivitas',
            'subtitle' => 'Catatan login, percobaan gagal, penguncian, dan logout.',
        ]);
    }
}
