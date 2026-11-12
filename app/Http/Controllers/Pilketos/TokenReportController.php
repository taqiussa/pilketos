<?php

namespace App\Http\Controllers\Pilketos;

use App\Exports\PilketosTokenKelasExport;
use App\Http\Controllers\Controller;
use App\Services\PilketosTokenClassReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TokenReportController extends Controller
{
    public function print(Request $request, PilketosTokenClassReportService $reports): Response
    {
        $report = $reports->forClass($request->integer('periode'), $request->integer('kelas'));
        $report['logoUrl'] = $report['periode']->logo_path
            ? Storage::disk(config('pilketos.photo_disk'))->url($report['periode']->logo_path)
            : null;

        return response()->view('pilketos.tokens.print', $report)->header('Cache-Control', 'private, no-store');
    }

    public function export(Request $request, PilketosTokenClassReportService $reports): BinaryFileResponse
    {
        $report = $reports->forClass($request->integer('periode'), $request->integer('kelas'));
        $filename = sprintf(
            'token-pilketos-%s-kelas-%s.xlsx',
            str($report['periode']->tahun)->lower()->replace([' / ', '/', ' '], '-')->trim('-'),
            str(trim($report['kelas']->tingkat.' '.$report['kelas']->nama))->lower()->replace(' ', '-')->slug(),
        );

        $response = Excel::download(new PilketosTokenKelasExport($report['rows']), $filename);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
