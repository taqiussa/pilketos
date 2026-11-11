<?php

return [
    'token_key' => env('PILKETOS_TOKEN_KEY'),
    'token_alphabet' => 'ABCDEFGHJKLMNPQRSTUVWXYZ123456789',
    'token_length' => 6,
    'max_token_attempts' => 20,
    'photo_disk' => env('PILKETOS_PHOTO_DISK', 'public'),
];
