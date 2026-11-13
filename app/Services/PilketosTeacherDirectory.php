<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class PilketosTeacherDirectory
{
    /** @return Builder<User> */
    public function eligibleQuery(): Builder
    {
        return User::query()
            ->where('is_active', true)
            ->whereDoesntHave('roles', fn (Builder $query): Builder => $query->where('name', 'Admin')->where('guard_name', 'web'));
    }

    /** @return array{user_id:int,name:string,username:?string,roles:string,kategori:string} */
    public function eligibleTeacher(int $userId): array
    {
        $user = $this->eligibleQuery()->with('roles:id,name,guard_name')->find($userId);
        $kategori = $user === null ? null : $this->normalizeGender($user->jenis_kelamin);

        if ($user === null || $kategori === null) {
            throw ValidationException::withMessages(['user_id' => 'Guru tidak memenuhi syarat pada periode ini.']);
        }

        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'roles' => $user->roles->pluck('name')->join(', '),
            'kategori' => $kategori,
        ];
    }

    public function normalizeGender(?string $gender): ?string
    {
        return match (strtoupper(trim((string) $gender))) {
            'L', 'LAKI-LAKI', 'LAKI LAKI', 'PUTRA', 'MALE', 'M' => 'L',
            'P', 'PEREMPUAN', 'PUTRI', 'FEMALE', 'F' => 'P',
            default => null,
        };
    }
}
