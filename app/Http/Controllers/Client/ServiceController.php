<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DichVu;
use Illuminate\View\View;

/**
 * Danh sách dịch vụ tiện ích.
 */
class ServiceController extends Controller
{
    public function index(): View
    {
        return view('client.services', ['services' => DichVu::orderBy('MaDichVu')->get()]);
    }
}
