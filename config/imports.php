<?php

return [
    'force' => [
        'rectorate_fm' => env('IMPORT_RECTORATE_FM_FORCE', false),
        'rectorate_mhs' => env('IMPORT_RECTORATE_MHS_FORCE', false),
        'rectorate_hibah' => env('IMPORT_RECTORATE_HIBAH_FORCE', false),
        'dosen_data_center' => env('IMPORT_DOSEN_DATA_CENTER_FORCE', false),
        'dosen_status_pendidikan' => env('IMPORT_DOSEN_STATUS_PENDIDIKAN_FORCE', false),
        'pkm' => env('IMPORT_PKM_FORCE', false),
    ],
];
