<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Tailoring\ProductionDashboardController;
use App\Support\Permissions;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->can('dashboard.view')) {
            return app(ProductionDashboardController::class)->index($request);
        }

        // send the user to the first screen their role allows
        foreach (Permissions::homeCandidates() as $route => $permission) {
            if ($route !== 'production.dashboard' && $user->can($permission)) {
                return redirect()->route($route);
            }
        }

        return view('no_access');
    }
}
