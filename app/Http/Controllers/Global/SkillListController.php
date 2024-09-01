<?php

namespace App\Http\Controllers\Global;

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

    // List skill lists by service ID
    public function listByService($serviceId)
    {
        $skillLists = SkillList::where('service_id', $serviceId)->get();
        return response()->json($skillLists);
    }
}
