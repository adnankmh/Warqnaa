<?php
namespace App\Http\Controllers;

use App\Services\WarqnaPro\EngineCoverageService;

class EngineAuditController
{
    public function index(EngineCoverageService $coverage)
    {
        $score=$coverage->summary();
        return view('admin.engine_audit_v124',['rows'=>$score['rows'],'score'=>$score]);
    }
}
