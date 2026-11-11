# Upload foto pasangan kandidat

Foto diproses di browser menjadi WebP, lalu diunggah langsung dengan presigned PUT ke prefix `pilketos/pasangan-kandidat/`. Laravel menyimpan hanya object key setelah upload intent milik admin yang sama, prefix, keberadaan objek, MIME type, dan ukuran diverifikasi.

Bucket S3 perlu mengizinkan `PUT` dari origin aplikasi, header `Content-Type` (serta `x-amz-acl` hanya bila `PILKETOS_PHOTO_PUBLIC_READ=true`), dan metode `GET` untuk URL foto publik. Set `PILKETOS_PHOTO_DISK=s3`; ACL public-read bersifat opt-in lewat `PILKETOS_PHOTO_PUBLIC_READ=true`.

Upload yang selesai lalu form dibatalkan tidak direferensikan dan tidak dihapus otomatis agar tidak menghapus objek yang mungkin sedang dipakai. Bersihkan orphan secara terjadwal dan terbatas pada prefix tersebut, hanya untuk object yang lebih lama dari masa upload intent dan tidak ada di `pilketos_pasangan.foto_pasangan`.
