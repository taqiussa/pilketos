<?php

namespace App\Http\Controllers\Pilketos;

use App\Http\Controllers\Controller;
use App\Services\PilketosPhotoUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresignPasanganPhotoController extends Controller
{
    public function __invoke(Request $request, PilketosPhotoUploadService $photos): JsonResponse
    {
        $data = $request->validate([
            'content_type' => ['required', 'in:image/webp'],
            'size' => ['required', 'integer', 'min:1', 'max:10485760'],
        ]);

        return response()->json($photos->presign($request->user(), (int) $data['size']));
    }
}
