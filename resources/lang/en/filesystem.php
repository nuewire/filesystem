<?php

declare(strict_types=1);

return [
    'title' => 'Filesystem',
    'lead' => 'Choose where files are stored.',

    'language' => [
        'label' => 'Language',
        'id' => 'Indonesian',
        'en' => 'English',
    ],

    'meta' => [
        'active_disk' => 'Disk: :disk',
        'base_directory' => 'Directory: :directory',
        'root' => 'root',
        'config' => 'Source: :source',
        'encrypted_file' => 'encrypted file',
        'package_default' => 'package default',
    ],

    'providers' => [
        'aria_label' => 'Filesystem provider',
        'local' => [
            'name' => 'Local',
            'description' => 'Store files on the app server.',
        ],
        's3' => [
            'name' => 'Amazon S3',
            'description' => 'Store files in an S3 bucket.',
        ],
        'bunnycdn' => [
            'name' => 'Bunny CDN',
            'description' => 'Store files in Bunny Storage.',
        ],
    ],

    'local' => [
        'title' => 'Local',
        'description' => 'Files are stored on the public disk.',
        'directory' => 'Base directory',
        'directory_hint' => 'Optional. Example: media/uploads.',
        'storage_link_missing' => 'Storage link is missing. Run php artisan storage:link.',
    ],

    's3' => [
        'title' => 'Amazon S3',
        'description' => 'Leave the secret key empty to keep it.',
        'key' => 'Access key',
        'secret' => 'Secret key',
        'region' => 'Region',
        'bucket' => 'Bucket',
        'directory' => 'Base directory',
        'directory_hint' => 'Optional. Do not include the bucket name.',
        'url' => 'Public URL',
        'url_hint' => 'Optional. Use the base URL.',
        'endpoint' => 'Endpoint',
        'path_style' => 'Use path-style endpoint',
    ],

    'bunny' => [
        'title' => 'Bunny CDN',
        'description' => 'Leave the password empty to keep it.',
        'zone' => 'Storage Zone',
        'zone_hint' => 'Used as the access key and bucket.',
        'password' => 'Password',
        'region' => 'Region',
        'region_hint' => 'Use auto when not required.',
        'endpoint' => 'S3 endpoint',
        'directory' => 'Base directory',
        'directory_hint' => 'Optional. Files are stored in this directory.',
        'cdn_url' => 'Pull Zone URL',
        'cdn_url_hint' => 'Use the Pull Zone base URL.',
    ],

    'secrets' => [
        'saved' => 'Saved. Enter to replace.',
        'required' => 'Required',
    ],

    'default_disk' => [
        'label' => 'Set as Laravel default disk',
        'hint' => 'Applies to new requests.',
    ],

    'notices' => [
        'test_url' => 'Test URL: :url',
        'settings_file' => 'Config file: :path',
    ],

    'actions' => [
        'save' => 'Save',
        'test' => 'Test connection',
        'switch_local' => 'Use Local',
    ],

    'status' => [
        'saved' => 'Settings saved.',
        'connection_success' => 'Connection successful.',
    ],

    'errors' => [
        'load_failed' => 'Failed to load settings. Local is used. :message',
        'save_failed' => 'Failed to save. :message',
        'test_failed' => 'Connection test failed. :message',
        'test_write' => 'Failed to write the test file.',
        'test_missing' => 'Test file was not found.',
        'test_content' => 'Test file content does not match.',
        'unsupported_provider' => 'Provider [:provider] is not supported.',
        'directory_too_long' => 'Directory may not exceed 1024 characters.',
        'directory_invalid_segment' => 'Directory path is invalid.',
        'directory_segment_too_long' => 'A directory segment may not exceed 255 characters.',
        'directory_invalid_characters' => 'Directory contains invalid characters.',
    ],

    'validation' => [
        'required' => ':attribute is required.',
        'string' => ':attribute must be text.',
        'max' => ':attribute may not exceed :max characters.',
        'url' => ':attribute must be a valid URL.',
        'https_url' => ':attribute must use HTTPS.',
        'boolean' => ':attribute is invalid.',
        'in' => ':attribute is invalid.',
        'regex' => ':attribute format is invalid.',
        'attributes' => [
            'driver' => 'Provider',
            'set_as_default' => 'Default disk',
            'local_directory' => 'Local directory',
            's3_key' => 'S3 access key',
            's3_secret' => 'S3 secret key',
            's3_region' => 'S3 region',
            's3_bucket' => 'S3 bucket',
            's3_url' => 'S3 URL',
            's3_endpoint' => 'S3 endpoint',
            's3_path_style' => 'S3 path-style',
            's3_directory' => 'S3 directory',
            'bunny_zone' => 'Bunny Storage Zone',
            'bunny_password' => 'Bunny password',
            'bunny_region' => 'Bunny region',
            'bunny_endpoint' => 'Bunny endpoint',
            'bunny_cdn_url' => 'Pull Zone URL',
            'bunny_directory' => 'Bunny directory',
        ],
    ],
];
