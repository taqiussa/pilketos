<?php

namespace App\Http\Controllers\Pilketos;

use App\Exports\PilketosTeacherTokenExport;
use App\Http\Controllers\Controller;
use App\Services\PilketosTeacherTokenReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeacherTokenReportController extends Controller
{
    public function print(Request $request, PilketosTeacherTokenReportService $reports): Response
    {
        $report = $reports->forPeriod($request->integer('periode'), $request->string('status')->toString());

        return response()->view('pilketos.tokens.teacher-print', $report)->header('Cache-Control', 'private, no-store');
    }

    public function export(Request $request, PilketosTeacherTokenReportService $reports): BinaryFileResponse
    {
        $report = $reports->forPeriod($request->integer('periode'), $request->string('status')->toString());
        $response = Excel::download(new PilketosTeacherTokenExport($report['rows']), 'token-guru-'.str($report['periode']->tahun)->slug().'.xlsx');
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
