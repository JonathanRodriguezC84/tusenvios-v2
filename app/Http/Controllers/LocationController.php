<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function departments(): JsonResponse
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return response()->json($departments);
    }

    public function cities(Request $request): JsonResponse
    {
        $request->validate(['department_id' => ['required', 'integer', 'exists:departments,id']]);

        $cities = City::where('department_id', $request->department_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }
}
