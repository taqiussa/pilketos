<?php

namespace App\Http\Controllers\Pilketos;

use App\Exports\PilketosTeacherTokenExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pilketos\TeacherTokenReportRequest;
use App\Services\PilketosTeacherTokenReportService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeacherTokenReportController extends Controller
{
    public function print(TeacherTokenReportRequest $request, PilketosTeacherTokenReportService $reports): Response
    {
        $report = $reports->forPeriod(
            $request->integer('periode'),
            $request->string('status')->toString(),
            $request->string('search')->toString(),
        );
        $report['logoUrl'] = $report['periode']->logo_path
            ? Storage::disk(config('pilketos.photo_disk'))->url($report['periode']->logo_path)
            : null;
        $report['statusLabel'] = $request->filled('status')
            ? PilketosTeacherTokenReportService::statusLabel($request->string('status')->toString())
            : null;

        return response()->view('pilketos.tokens.teacher-print', $report)->header('Cache-Control', 'private, no-store');
    }

    public function export(TeacherTokenReportRequest $request, PilketosTeacherTokenReportService $reports): BinaryFileResponse
    {
        $report = $reports->forPeriod(
            $request->integer('periode'),
            $request->string('status')->toString(),
            $request->string('search')->toString(),
        );
        $filename = 'token-guru-'.str($report['periode']->tahun)->slug();
        if ($request->filled('status')) {
            $filename .= '-'.str($request->string('status')->toString())->slug();
        }

        $response = Excel::download(new PilketosTeacherTokenExport($report['rows']), "{$filename}.xlsx");
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
