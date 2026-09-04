<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Department;
use App\Models\Locality;
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

    public function localities(Request $request): JsonResponse
    {
        $request->validate(['city' => ['required', 'string', 'max:255']]);

        $cityId = City::where('name', $request->city)->value('id');
        if (! $cityId) {
            return response()->json([]);
        }

        $localities = Locality::where('city_id', $cityId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($localities);
    }
}
