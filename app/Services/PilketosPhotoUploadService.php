<?php

namespace App\Services;

use App\Models\PilketosPhotoUpload;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PilketosPhotoUploadService
{
    public const PREFIX = 'pilketos/pasangan-kandidat/';

    public const MAX_BYTES = 10_485_760;

    /** @return array{upload_url: string, object_key: string, content_type: string} */
    public function presign(Authenticatable $user, int $size): array
    {
        $key = self::PREFIX.Str::uuid().'.webp';
        $disk = Storage::disk(config('pilketos.photo_disk', 's3'));
        $client = $disk->getClient();
        $arguments = [
            'Bucket' => config('filesystems.disks.'.config('pilketos.photo_disk', 's3').'.bucket'),
            'Key' => $key,
            'ContentType' => 'image/webp',
        ];
        $arguments['ACL'] = 'public-read';
        $request = $client->createPresignedRequest($client->getCommand('PutObject', $arguments), '+5 minutes');

        PilketosPhotoUpload::query()->create([
            'user_id' => $user->getAuthIdentifier(),
            'object_key' => $key,
            'size' => $size,
            'content_type' => 'image/webp',
            'expires_at' => now()->addMinutes(5),
        ]);

        return ['upload_url' => (string) $request->getUri(), 'object_key' => $key, 'content_type' => 'image/webp'];
    }

    public function verifyFor(Authenticatable $user, string $key): PilketosPhotoUpload
    {
        if (! str_starts_with($key, self::PREFIX)) {
            throw ValidationException::withMessages(['foto_pasangan' => 'Referensi foto tidak valid.']);
        }
        $intent = PilketosPhotoUpload::query()
            ->where('object_key', $key)
            ->where('user_id', $user->getAuthIdentifier())
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->first();
        if ($intent === null) {
            throw ValidationException::withMessages(['foto_pasangan' => 'Upload foto tidak dikenal atau sudah kedaluwarsa.']);
        }
        $disk = Storage::disk(config('pilketos.photo_disk', 's3'));
        if (! $disk->exists($key)) {
            throw ValidationException::withMessages(['foto_pasangan' => 'Objek foto belum ditemukan di penyimpanan.']);
        }
        $size = (int) $disk->size($key);
        $mime = strtolower((string) $disk->mimeType($key));
        if ($size < 1 || $size > self::MAX_BYTES || $mime !== 'image/webp') {
            throw ValidationException::withMessages(['foto_pasangan' => 'Objek foto di penyimpanan tidak valid.']);
        }

        return $intent;
    }

    public function consume(PilketosPhotoUpload $intent): void
    {
        $intent->update(['used_at' => now()]);
    }
}
