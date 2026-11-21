<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoutingController extends Controller
{
    public function index(Request $request)
    {

        $user = Auth::user(); // logged-in user
        return view('index', compact('user'));
    }

    /**
     * Display a view based on first route param
     *
     * @return \Illuminate\Http\Response
     */
    public function root(Request $request, $first)
    {
        return $this->page($first);
    }

    /**
     * second level route
     */
    public function secondLevel(Request $request, $first, $second)
    {
        return $this->page($first . '.' . $second);
    }

    /**
     * third level route
     */
    public function thirdLevel(Request $request, $first, $second, $third)
    {
        return $this->page($first . '.' . $second . '.' . $third);
    }

    // unknown address -> normal "404 Not Found" page instead of a server error
    private function page(string $view)
    {
        abort_unless(preg_match('/^[A-Za-z0-9_\-.]+$/', $view) && view()->exists($view), 404);

        return view($view);
    }
}
