<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompareController extends Controller
{
    public function index()
    {
        return Compare::where('user_id', Auth::id())->with('property')->get();
    }

    public function store(Request $request)
    {
        return Compare::create([
            'user_id' => Auth::id(),
            'property_id' => $request->property_id
        ]);
    }

    public function destroy(Compare $compare)
    {
        $compare->delete();
        return response()->noContent();
    }
}
