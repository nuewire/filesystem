<?php

declare(strict_types=1);

return [
    'title' => 'Filesystem',
    'lead' => 'Pilih lokasi penyimpanan file.',

    'language' => [
        'label' => 'Bahasa',
        'id' => 'Indonesia',
        'en' => 'Inggris',
    ],

    'meta' => [
        'active_disk' => 'Disk: :disk',
        'base_directory' => 'Direktori: :directory',
        'root' => 'root',
        'config' => 'Sumber: :source',
        'encrypted_file' => 'file terenkripsi',
        'package_default' => 'default package',
    ],

    'providers' => [
        'aria_label' => 'Penyedia filesystem',
        'local' => [
            'name' => 'Local',
            'description' => 'Simpan file di server aplikasi.',
        ],
        's3' => [
            'name' => 'Amazon S3',
            'description' => 'Simpan file di bucket S3.',
        ],
        'bunnycdn' => [
            'name' => 'Bunny CDN',
            'description' => 'Simpan file di Bunny Storage.',
        ],
    ],

    'local' => [
        'title' => 'Local',
        'description' => 'File disimpan di storage publik.',
        'directory' => 'Direktori dasar',
        'directory_hint' => 'Opsional. Contoh: media/uploads.',
        'storage_link_missing' => 'Storage link belum tersedia. Jalankan php artisan storage:link.',
    ],

    's3' => [
        'title' => 'Amazon S3',
        'description' => 'Kosongkan secret key jika tidak diubah.',
        'key' => 'Access key',
        'secret' => 'Secret key',
        'region' => 'Region',
        'bucket' => 'Bucket',
        'directory' => 'Direktori dasar',
        'directory_hint' => 'Opsional. Jangan sertakan nama bucket.',
        'url' => 'URL publik',
        'url_hint' => 'Opsional. Gunakan URL dasar.',
        'endpoint' => 'Endpoint',
        'path_style' => 'Gunakan path-style endpoint',
    ],

    'bunny' => [
        'title' => 'Bunny CDN',
        'description' => 'Kosongkan password jika tidak diubah.',
        'zone' => 'Storage Zone',
        'zone_hint' => 'Dipakai sebagai access key dan bucket.',
        'password' => 'Password',
        'region' => 'Region',
        'region_hint' => 'Gunakan auto jika tidak diperlukan.',
        'endpoint' => 'Endpoint S3',
        'directory' => 'Direktori dasar',
        'directory_hint' => 'Opsional. Semua file disimpan di direktori ini.',
        'cdn_url' => 'URL Pull Zone',
        'cdn_url_hint' => 'Gunakan URL dasar Pull Zone.',
    ],

    'secrets' => [
        'saved' => 'Tersimpan. Isi untuk mengganti.',
        'required' => 'Wajib diisi',
    ],

    'default_disk' => [
        'label' => 'Jadikan sebagai disk default Laravel',
        'hint' => 'Berlaku untuk request berikutnya.',
    ],

    'notices' => [
        'test_url' => 'URL uji: :url',
        'settings_file' => 'File konfigurasi: :path',
    ],

    'actions' => [
        'save' => 'Simpan',
        'test' => 'Uji koneksi',
        'switch_local' => 'Pakai Local',
    ],

    'status' => [
        'saved' => 'Pengaturan disimpan.',
        'connection_success' => 'Koneksi berhasil.',
    ],

    'errors' => [
        'load_failed' => 'Gagal memuat pengaturan. Local digunakan. :message',
        'save_failed' => 'Gagal menyimpan. :message',
        'test_failed' => 'Uji koneksi gagal. :message',
        'test_write' => 'Gagal menulis file uji.',
        'test_missing' => 'File uji tidak ditemukan.',
        'test_content' => 'Isi file uji tidak sesuai.',
        'unsupported_provider' => 'Provider [:provider] tidak didukung.',
        'directory_too_long' => 'Direktori maksimal 1024 karakter.',
        'directory_invalid_segment' => 'Path direktori tidak valid.',
        'directory_segment_too_long' => 'Segmen direktori maksimal 255 karakter.',
        'directory_invalid_characters' => 'Karakter direktori tidak valid.',
    ],

    'validation' => [
        'required' => ':attribute wajib diisi.',
        'string' => ':attribute harus berupa teks.',
        'max' => ':attribute maksimal :max karakter.',
        'url' => ':attribute harus berupa URL yang valid.',
        'https_url' => ':attribute harus memakai HTTPS.',
        'boolean' => ':attribute tidak valid.',
        'in' => ':attribute tidak valid.',
        'regex' => 'Format :attribute tidak valid.',
        'attributes' => [
            'driver' => 'Provider',
            'set_as_default' => 'Disk default',
            'local_directory' => 'Direktori Local',
            's3_key' => 'S3 access key',
            's3_secret' => 'S3 secret key',
            's3_region' => 'S3 region',
            's3_bucket' => 'S3 bucket',
            's3_url' => 'URL S3',
            's3_endpoint' => 'Endpoint S3',
            's3_path_style' => 'Path-style S3',
            's3_directory' => 'Direktori S3',
            'bunny_zone' => 'Bunny Storage Zone',
            'bunny_password' => 'Password Bunny',
            'bunny_region' => 'Region Bunny',
            'bunny_endpoint' => 'Endpoint Bunny',
            'bunny_cdn_url' => 'URL Pull Zone',
            'bunny_directory' => 'Direktori Bunny',
        ],
    ],
];
