<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use App\Models\Task;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    public function index()
    {
        $stats = [
            'total_projects' => Project::count(),
            'total_teams' => Team::count(),
            'completed_tasks' => Task::where('status', 'done')->count(),
            'open_tasks' => Task::where('status', '!=', 'done')->count(),
        ];
        
        return view('admin.reporting', compact('stats'));
    }
}
