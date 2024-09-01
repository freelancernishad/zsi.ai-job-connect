<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SkillList;
use Illuminate\Http\Request;

class SkillListController extends Controller
{
    // Get all skill lists
    public function index()
    {
        return response()->json(SkillList::all());
    }

    // Get a single skill list by ID
    public function show($id)
    {
        $skillList = SkillList::find($id);
        if (!$skillList) {
            return response()->json(['message' => 'SkillList not found'], 404);
        }
        return response()->json($skillList);
    }

    // Create a new skill list
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
        ]);

        $skillList = SkillList::create($request->all());
        return response()->json($skillList, 201);
    }

    // Update an existing skill list
    public function update(Request $request, $id)
    {
        $skillList = SkillList::find($id);
        if (!$skillList) {
            return response()->json(['message' => 'SkillList not found'], 404);
        }

        $request->validate([
            'service_id' => 'sometimes|required|exists:services,id',
            'name' => 'sometimes|required|string|max:255',
        ]);

        $skillList->update($request->all());
        return response()->json($skillList);
    }

    // Delete a skill list
    public function destroy($id)
    {
        $skillList = SkillList::find($id);
        if (!$skillList) {
            return response()->json(['message' => 'SkillList not found'], 404);
        }

        $skillList->delete();
        return response()->json(['message' => 'SkillList deleted successfully']);
    }

    // List skill lists by service ID
    public function listByService($serviceId)
    {
        $skillLists = SkillList::where('service_id', $serviceId)->get();
        return response()->json($skillLists);
    }
}

