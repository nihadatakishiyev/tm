<?php

namespace App\Http\Controllers;

use App\ActivityLog;
use App\Http\Resources\Project as ProjectResource;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProjectController extends Controller
{

    public function __construct()
    {
//        $this->middleware('auth:api');

        $this->middleware('testo')->only('show');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $projects = Project::all();

        return ProjectResource::collection($projects);
    }

    public function update(Request $request, $id) {
        $project = Project::findOrFail($id);

        $project->owner_id = $request->input('owner_id');
        $project->name = $request->input('name');
        $project->description = $request->input('description');
        $project->color_code = $request->input('color_code');
        $project->is_completed = $request->input('is_completed');
        $project->deadline = $request->input('deadline');

        ActivityLog::create([
            'owner_id' => $project->owner_id,
            'action_name' => 'Updated Project'
        ])->save();

        if($project->save()){
            return new ProjectResource($project);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ProjectResource
     */
    public function store(Request $request)
    {
        $project = new Project;

        $project->owner_id = $request->input('owner_id');
        $project->name = $request->input('name');
        $project->description = $request->input('description');
        $project->color_code = $request->input('color_code');
        $project->is_completed = $request->input('is_completed');
        $project->deadline = $request->input('deadline');

        ActivityLog::create([
            'owner_id' => $project->owner_id,
            'action_name' => 'Created Project'
        ])->save();

        if($project->save()){
            return new ProjectResource($project);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return ProjectResource
     */
    public function show($id)
    {
//        $project = Project::with('owner','tasks', 'tasks.comments')->where('id', $id)->get();
        $project = Project::with(['tasks' => function($query){
            $query->withCount('comments');
        }, 'tasks.user', 'tasks.assigned_to', 'owner', 'ups.user'])->where('id', $id)->get();

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return ProjectResource
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        ActivityLog::create([
            'owner_id' => $project->owner_id,
            'action_name' => 'Deleted Project'
        ])->save();

        if ($project->delete()){
            return new ProjectResource($project);
        }
    }
}
