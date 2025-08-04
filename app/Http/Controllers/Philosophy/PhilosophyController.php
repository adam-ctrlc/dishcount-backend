<?php

namespace App\Http\Controllers\Philosophy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Philosophy;

class PhilosophyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mission = Philosophy::where('type', 'mission')->first();
        $vision = Philosophy::where('type', 'vision')->first();

        if (!$mission && !$vision) {
            return response()->json([
                'message' => 'Philosophy not found',
            ], 200);
        }

        return response()->json([
            'message' => 'Success',
            'mission' => $mission,
            'vision' => $vision
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
