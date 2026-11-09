<?php

return [
    // Basis realisasi Scopus FM pada /kpi-publikasi dan laporan bulanan (ExportReport).
    // realization: Realization Template workbook. Total kampus = baris "BINUS @ Malang Campus",
    //              dan hanya unit Malang yang tercantum di template yang dihitung/ditampilkan.
    // campus:      bobot asli seluruh publikasi Scopus FM pada sheet MALANG per Prodi KPI
    //              (sama dengan pivot per Kampus), termasuk dosen CBDC/English Literature.
    'fm_basis' => env('PUBLICATION_FM_BASIS', 'realization'),
];
