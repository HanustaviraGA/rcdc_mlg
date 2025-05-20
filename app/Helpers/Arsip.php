<?php

// function TP12Func($kode_dosen) {
//     $publications = RectorateDosen::where('kode_dosen', $kode_dosen)->get();

//     $has_scopus = false;
//     $has_seminar_nscopus_sinta = false;
//     $has_seminar_nscopus_proceeding = false;
//     $has_scopus_q_nonfirst = false;
//     $has_nscopus_sinta6_first = false;
//     $has_nscopus_proceeding = false;
//     $has_seminar_scopus = false;
//     $has_journal_scopus = false;

//     foreach ($publications as $pub) {
//         $jenis = strtolower($pub->jenis);
//         $tipe = strtolower($pub->tipe_publikasi);
//         $quartile = strtolower($pub->quartile_jurnal ?? '');
//         $first_author = strtoupper($pub->first_author) === 'Y';

//         $is_jurnal_or_seminar = $jenis === 'Jurnal' || $jenis === 'Seminar';
//         $is_scopus = $tipe === 'Scopus';
//         $is_nscopus = $tipe === 'Nscopus';

//         // Any scopus publication
//         if ($is_scopus) {
//             $has_scopus = true;
//         }

//         // Skor 1
//         if ($is_jurnal_or_seminar && $is_nscopus && str_contains($quartile, 'Jurnal Sinta')) {
//             $has_seminar_nscopus_sinta = true;
//         }

//         // Skor 2
//         if ($is_jurnal_or_seminar && $is_nscopus && $quartile === 'Proceeding') {
//             $has_seminar_nscopus_proceeding = true;
//         }

//         // Skor 3
//         if ($is_jurnal_or_seminar && $is_scopus && str_contains($quartile, 'Q') && !$first_author) {
//             $has_scopus_q_nonfirst = true;
//         }

//         // Skor 4
//         if ($jenis === 'Jurnal') {
//             if ($is_nscopus && $quartile === 'Jurnal Sinta 6' && $first_author) {
//                 $has_nscopus_sinta6_first = true;
//             }
//             if ($is_nscopus && $quartile === 'Proceeding') {
//                 $has_nscopus_proceeding = true;
//             }
//         }

//         // Skor 5
//         if ($jenis === 'Seminar' && $is_scopus || $jenis === 'Book Chapter' && $is_scopus) {
//             $has_seminar_scopus = true;
//         }

//         // Skor 6
//         if ($jenis === 'Jurnal' && $is_scopus) {
//             $has_journal_scopus = true;
//         }
//     }

//     if (($has_nscopus_sinta6_first || $has_nscopus_proceeding) && $has_journal_scopus) return ['kpi' => 6];
//     if (($has_nscopus_sinta6_first || $has_nscopus_proceeding) && $has_seminar_scopus) return ['kpi' => 5];
//     if ($has_nscopus_sinta6_first || $has_nscopus_proceeding) return ['kpi' => 4];
//     if ($has_scopus_q_nonfirst) return ['kpi' => 3];
//     if ($has_seminar_nscopus_proceeding && !$has_scopus) return ['kpi' => 2];
//     if ($has_seminar_nscopus_sinta && !$has_scopus) return ['kpi' => 1];

//     return ['kpi' => 0]; // Default score if no match
// }

// function AA2Func($kodeDosen){
//     $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();
//     // Get total bobot from all scopus-type seminar/jurnal/book chapter
//     $scopusItems = $data->filter(function ($item) {
//         return strtolower($item->tipe_publikasi) === 'scopus' &&
//             in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
//     });
//     $totalBobot = $scopusItems->sum('bobot');
//     // Skor 5
//     if ($scopusItems->contains(fn($item) => $item->bobot >= 1.5)) {
//         $score = 5;
//     }
//     // Skor 4
//     elseif ($scopusItems->contains(fn($item) => $item->bobot >= 1 && $item->bobot <= 1.4)) {
//         // Check for Skor 6:
//         $hasExtraScopusJurnal = $scopusItems->filter(function ($item) {
//             return strtolower($item->jenis) === 'jurnal';
//         })->count() > 1;
//         if ($hasExtraScopusJurnal) {
//             $score = 6;
//         }else{
//             $score = 4;
//         }
//     }
//     // Skor 3
//     elseif ($scopusItems->contains(fn($item) => $item->bobot == 0.5)) {
//         $score = 3;
//     }
//     // Skor 2
//     elseif ($scopusItems->contains(fn($item) => $item->bobot < 0.5)) {
//         $score = 2;
//     }
//     // Skor 1
//     elseif ($data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
//             strtolower($item->tipe_publikasi) === 'nscopus' &&
//             str_contains(strtolower($item->quartile_jurnal), 'jurnal sinta');
//     })) {
//         $score = 1;
//     }
//     // No matching data
//     else {
//         $score = 0;
//     }
//     return [
//         'kpi' => $score,
//         'total_bobot' => $totalBobot,
//     ];
// }

// function L2Func($kodeDosen) {
//     $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

//     // Total bobot from all scopus seminar/jurnal/book chapter
//     $scopusItems = $data->filter(function ($item) {
//         return strtolower($item->tipe_publikasi) === 'scopus' &&
//             in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
//     });

//     $totalBobot = $scopusItems->sum('bobot');

//     // Base for Skor 4 requirement
//     $mainScopus = $scopusItems->filter(function ($item) {
//         return $item->bobot >= 1 && $item->bobot <= 1.4;
//     });

//     // Additional for Skor 5: Jurnal Scopus with bobot >= 0.25 and < 1
//     $additionalJurnalFor5 = $scopusItems->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' && 
//                $item->bobot >= 0.25 && $item->bobot < 1;
//     });

//     // Additional for Skor 6: Jurnal Scopus with bobot >= 1
//     $additionalJurnalFor6 = $scopusItems->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' && 
//                $item->bobot >= 1;
//     });

//     if ($mainScopus->isNotEmpty() && $additionalJurnalFor6->count() > 1) {
//         $score = 6;
//     } elseif ($mainScopus->isNotEmpty() && $additionalJurnalFor5->isNotEmpty()) {
//         $score = 5;
//     } elseif ($mainScopus->isNotEmpty()) {
//         $score = 4;
//     } elseif ($scopusItems->contains(fn($item) => $item->bobot == 0.5)) {
//         $score = 3;
//     } elseif ($scopusItems->contains(fn($item) => $item->bobot < 0.5)) {
//         $score = 2;
//     } elseif (
//         $data->contains(function ($item) {
//             $jenis = strtolower($item->jenis);
//             $tipe = strtolower($item->tipe_publikasi);
//             $quartile = strtolower($item->quartile_jurnal ?? '');
//             return (
//                 in_array($jenis, ['seminar', 'jurnal']) &&
//                 $tipe === 'nscopus' &&
//                 (str_contains($quartile, 'jurnal sinta') || $jenis === 'seminar')
//             );
//         })
//     ) {
//         $score = 1;
//     } else {
//         $score = 0;
//     }

//     return [
//         'kpi' => $score,
//         'total_bobot' => $totalBobot,
//     ];
// }

// function AA3TP3LK2Func($kodeDosen) {
//     $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

//     // Filter Scopus publications of type seminar/jurnal/book chapter
//     $scopusItems = $data->filter(function ($item) {
//         return strtolower($item->tipe_publikasi) === 'scopus' &&
//             in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
//     });

//     $totalBobot = $scopusItems->sum('bobot');

//     // Main scopus item with bobot >= 2 (Skor 4 requirement)
//     $mainScopus = $scopusItems->filter(function ($item) {
//         return $item->bobot >= 2;
//     });

//     // Additional jurnal scopus with bobot >= 1.5 (Skor 6)
//     $additionalJurnalFor6 = $scopusItems->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' && $item->bobot >= 1.5;
//     });

//     // Additional jurnal scopus with bobot >= 0.25 and < 1.5 (Skor 5)
//     $additionalJurnalFor5 = $scopusItems->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                $item->bobot >= 0.25 && $item->bobot < 1.5;
//     });

//     if ($mainScopus->isNotEmpty() && $additionalJurnalFor6->count() > 1) {
//         $score = 6;
//     } elseif ($mainScopus->isNotEmpty() && $additionalJurnalFor5->isNotEmpty()) {
//         $score = 5;
//     } elseif ($mainScopus->isNotEmpty()) {
//         $score = 4;
//     } elseif ($scopusItems->contains(fn($item) => $item->bobot >= 1 && $item->bobot < 2)) {
//         $score = 3;
//     } elseif ($scopusItems->contains(fn($item) => $item->bobot < 1)) {
//         $score = 2;
//     } elseif (
//         $data->contains(function ($item) {
//             $jenis = strtolower($item->jenis);
//             $tipe = strtolower($item->tipe_publikasi);
//             $quartile = strtolower($item->quartile_jurnal ?? '');
//             return (
//                 in_array($jenis, ['seminar', 'jurnal']) &&
//                 $tipe === 'nscopus' &&
//                 (str_contains($quartile, 'jurnal sinta') || $jenis === 'seminar')
//             );
//         })
//     ) {
//         $score = 1;
//     } else {
//         $score = 0;
//     }

//     return [
//         'kpi' => $score,
//         'total_bobot' => $totalBobot,
//     ];
// }

// function L3LK3Func($kodeDosen) {
//     $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

//     // Filter Scopus publications of type seminar/jurnal/book chapter
//     $scopusItems = $data->filter(function ($item) {
//         return strtolower($item->tipe_publikasi) === 'scopus' &&
//             in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
//     });

//     $totalBobot = $scopusItems->sum('bobot');

//     // Main Scopus item with bobot >= 4 (Skor 4 requirement)
//     $mainScopus = $scopusItems->filter(function ($item) {
//         return $item->bobot >= 4;
//     });

//     // Additional jurnal scopus with bobot >= 2 (Skor 6)
//     $additionalJurnalFor6 = $scopusItems->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' && $item->bobot >= 2;
//     });

//     // Additional jurnal scopus with bobot >= 0.25 and < 2 (Skor 5)
//     $additionalJurnalFor5 = $scopusItems->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                $item->bobot >= 0.25 && $item->bobot < 2;
//     });

//     if ($mainScopus->isNotEmpty() && $additionalJurnalFor6->count() > 1) {
//         $score = 6;
//     } elseif ($mainScopus->isNotEmpty() && $additionalJurnalFor5->isNotEmpty()) {
//         $score = 5;
//     } elseif ($mainScopus->isNotEmpty()) {
//         $score = 4;
//     } elseif ($scopusItems->contains(fn($item) => $item->bobot >= 3 && $item->bobot < 4)) {
//         $score = 3;
//     } elseif ($scopusItems->contains(fn($item) => $item->bobot < 3)) {
//         $score = 2;
//     } elseif (
//         $data->contains(function ($item) {
//             $jenis = strtolower($item->jenis);
//             $tipe = strtolower($item->tipe_publikasi);
//             $quartile = strtolower($item->quartile_jurnal ?? '');
//             return (
//                 in_array($jenis, ['seminar', 'jurnal']) &&
//                 $tipe === 'nscopus' &&
//                 (str_contains($quartile, 'jurnal sinta') || $jenis === 'seminar')
//             );
//         })
//     ) {
//         $score = 1;
//     } else {
//         $score = 0;
//     }

//     return [
//         'kpi' => $score,
//         'total_bobot' => $totalBobot,
//     ];
// }

// function GBFunc($kodeDosen) {
//     $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

//     // Filter Scopus publications of type seminar/jurnal/book chapter
//     $scopusItems = $data->filter(function ($item) {
//         return strtolower($item->tipe_publikasi) === 'scopus' &&
//             in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
//     });

//     $totalBobot = $scopusItems->sum('bobot');

//     // Main Scopus item with bobot >= 6 (Skor 4 requirement)
//     $mainScopus = $scopusItems->filter(function ($item) {
//         return $item->bobot >= 6;
//     });

//     // Additional jurnal scopus with bobot >= 2 OR any book chapter (Skor 6)
//     $additionalFor6 = $scopusItems->filter(function ($item) {
//         return (strtolower($item->jenis) === 'jurnal' && $item->bobot >= 2) ||
//                strtolower($item->jenis) === 'book chapter';
//     });

//     // Additional jurnal scopus with bobot >= 0.25 and < 2 (Skor 5)
//     $additionalFor5 = $scopusItems->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                $item->bobot >= 0.25 && $item->bobot < 2;
//     });

//     if ($mainScopus->isNotEmpty() && $additionalFor6->count() > 1) {
//         $score = 6;
//     } elseif ($mainScopus->isNotEmpty() && $additionalFor5->isNotEmpty()) {
//         $score = 5;
//     } elseif ($mainScopus->isNotEmpty()) {
//         $score = 4;
//     } elseif ($scopusItems->contains(fn($item) => $item->bobot >= 5 && $item->bobot < 6)) {
//         $score = 3;
//     } elseif ($scopusItems->contains(fn($item) => $item->bobot < 5)) {
//         $score = 2;
//     } elseif (
//         $data->contains(function ($item) {
//             $jenis = strtolower($item->jenis);
//             $tipe = strtolower($item->tipe_publikasi);
//             $quartile = strtolower($item->quartile_jurnal ?? '');
//             return (
//                 in_array($jenis, ['seminar', 'jurnal']) &&
//                 $tipe === 'nscopus' &&
//                 (str_contains($quartile, 'jurnal sinta') || $jenis === 'seminar')
//             );
//         })
//     ) {
//         $score = 1;
//     } else {
//         $score = 0;
//     }

//     return [
//         'kpi' => $score,
//         'total_bobot' => $totalBobot,
//     ];
// }

// function AA2Prof($kodeDosen) {
//     $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

//     // Skor 6: Skor 4 terpenuhi + ada jurnal scopus dengan bobot >= 0.25
//     $skor4Eligible = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 0.5;
//     });

//     $hasJurnalScopus025 = $data->contains(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 0.25;
//     });

//     $hasJurnalScopus = $data->contains(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                strtolower($item->tipe_publikasi) === 'scopus';
//     });

//     $hasScopusGTE025LT05 = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 0.25 &&
//                floatval($item->bobot) < 0.5;
//     });

//     $hasScopusLT025 = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) < 0.25;
//     });

//     $hasNScopusSinta = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
//                strtolower($item->tipe_publikasi) === 'nscopus' &&
//                str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
//     });

//     // Determine score
//     if ($skor4Eligible && $hasJurnalScopus025) {
//         $score = 6;
//     } elseif ($skor4Eligible && $hasJurnalScopus) {
//         $score = 5;
//     } elseif ($skor4Eligible) {
//         $score = 4;
//     } elseif ($hasScopusGTE025LT05) {
//         $score = 3;
//     } elseif ($hasScopusLT025) {
//         $score = 2;
//     } elseif ($hasNScopusSinta) {
//         $score = 1;
//     } else {
//         $score = 0;
//     }

//     return [
//         'kpi' => $score,
//     ];
// }

// function L2Prof($kodeDosen) {
//     $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

//     // Skor 4: minimal ada 1 publikasi (seminar/jurnal/book chapter) scopus dengan bobot >= 0.5
//     $skor4Eligible = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 0.5;
//     });

//     // Skor 6: Skor 4 terpenuhi + ada tambahan jurnal scopus dengan bobot >= 0.5
//     $skor6AdditionalJurnal = $data->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 0.5;
//     });

//     // Skor 5: Skor 4 terpenuhi + ada jurnal scopus (tanpa syarat bobot)
//     $hasJurnalScopus = $data->contains(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                strtolower($item->tipe_publikasi) === 'scopus';
//     });

//     $hasScopusGTE025LT05 = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 0.25 &&
//                floatval($item->bobot) < 0.5;
//     });

//     $hasScopusLT025 = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) < 0.25;
//     });

//     $hasNScopusSinta = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
//                strtolower($item->tipe_publikasi) === 'nscopus' &&
//                str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
//     });

//     // Determine score
//     if ($skor4Eligible && $skor6AdditionalJurnal->count() > 1) {
//         $score = 6;
//     } elseif ($skor4Eligible && $hasJurnalScopus) {
//         $score = 5;
//     } elseif ($skor4Eligible) {
//         $score = 4;
//     } elseif ($hasScopusGTE025LT05) {
//         $score = 3;
//     } elseif ($hasScopusLT025) {
//         $score = 2;
//     } elseif ($hasNScopusSinta) {
//         $score = 1;
//     } else {
//         $score = 0;
//     }

//     return [
//         'kpi' => $score,
//     ];
// }

// function AA3TP3LK2Prof($kodeDosen) {
//     $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

//     // Skor 4: ada Scopus (seminar/jurnal/book chapter) dengan bobot >= 1
//     $skor4Eligible = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 1;
//     });

//     // Skor 6: Skor 4 terpenuhi + jurnal Scopus tambahan bobot >= 1
//     $skor6Additional = $data->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 1;
//     });

//     // Skor 5: Skor 4 terpenuhi + jurnal Scopus (tanpa syarat bobot)
//     $hasJurnalScopus = $data->contains(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                strtolower($item->tipe_publikasi) === 'scopus';
//     });

//     // Skor 3: Scopus dengan bobot >= 0.75 dan < 1
//     $hasScopus075To099 = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 0.75 &&
//                floatval($item->bobot) < 1;
//     });

//     // Skor 2: Scopus dengan bobot < 0.75
//     $hasScopusLT075 = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) < 0.75;
//     });

//     // Skor 1: nscopus atau jurnal dengan quartile LIKE 'Jurnal SINTA%'
//     $hasNScopusSinta = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
//                strtolower($item->tipe_publikasi) === 'nscopus' &&
//                str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
//     });

//     // Determine score
//     if ($skor4Eligible && $skor6Additional->count() > 1) {
//         $score = 6;
//     } elseif ($skor4Eligible && $hasJurnalScopus) {
//         $score = 5;
//     } elseif ($skor4Eligible) {
//         $score = 4;
//     } elseif ($hasScopus075To099) {
//         $score = 3;
//     } elseif ($hasScopusLT075) {
//         $score = 2;
//     } elseif ($hasNScopusSinta) {
//         $score = 1;
//     } else {
//         $score = 0;
//     }

//     return [
//         'kpi' => $score,
//     ];
// }

// function L3Prof($kodeDosen) {
//     $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

//     // Skor 4: seminar/jurnal/book chapter Scopus dengan bobot >= 2
//     $skor4Eligible = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 2;
//     });

//     // Skor 6: Skor 4 terpenuhi + jurnal Scopus dengan bobot >= 1
//     $skor6Additional = $data->filter(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) >= 1;
//     });

//     // Skor 5: Skor 4 terpenuhi + ada jurnal Scopus (tanpa bobot)
//     $hasJurnalScopus = $data->contains(function ($item) {
//         return strtolower($item->jenis) === 'jurnal' &&
//                strtolower($item->tipe_publikasi) === 'scopus';
//     });

//     // Skor 3: Scopus dengan bobot >= 1 < 2
//     $hasScopus1To2 = $data->contains(function ($item) {
//         $bobot = floatval($item->bobot);
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                $bobot >= 1 && $bobot < 2;
//     });

//     // Skor 2: Scopus dengan bobot < 1
//     $hasScopusLT1 = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
//                strtolower($item->tipe_publikasi) === 'scopus' &&
//                floatval($item->bobot) < 1;
//     });

//     // Skor 1: tipe nscopus atau quartile_jurnal LIKE 'Jurnal SINTA%'
//     $hasNScopusSinta = $data->contains(function ($item) {
//         return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
//                strtolower($item->tipe_publikasi) === 'nscopus' &&
//                str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
//     });

//     // Penentuan skor
//     if ($skor4Eligible && $skor6Additional->count() > 1) {
//         $score = 6;
//     } elseif ($skor4Eligible && $hasJurnalScopus) {
//         $score = 5;
//     } elseif ($skor4Eligible) {
//         $score = 4;
//     } elseif ($hasScopus1To2) {
//         $score = 3;
//     } elseif ($hasScopusLT1) {
//         $score = 2;
//     } elseif ($hasNScopusSinta) {
//         $score = 1;
//     } else {
//         $score = 0;
//     }

//     return [
//         'kpi' => $score,
//     ];
// }

// OLD

// function AAFunc($kode_dosen) {
//     $publications = RectorateDosen::where('kode_dosen', $kode_dosen)->get();

//     $has_sinta_nscopus = false;
//     $scopus_under_05 = 0;
//     $scopus_eq_05 = 0;
//     $scopus_1_to_1_4 = 0;
//     $scopus_gte_1_5 = 0;
//     $extra_scopus_journal = 0;

//     foreach ($publications as $pub) {
//         $jenis = strtolower($pub->jenis);
//         $tipe = strtolower($pub->tipe_publikasi);
//         $quartile = strtolower($pub->quartile_jurnal ?? '');
//         $bobot = floatval($pub->bobot ?? 0);

//         $is_jurnal_or_seminar_or_bc = in_array($jenis, ['seminar', 'jurnal', 'book chapter']);
//         $is_nscopus = $tipe === 'nscopus';
//         $is_scopus = $tipe === 'scopus';

//         // Skor 1: nscopus publications
//         if (
//             ($jenis === 'seminar' || $jenis === 'jurnal') && $is_nscopus ||
//             ($jenis === 'jurnal' && $is_nscopus && str_contains($quartile, 'jurnal sinta'))
//         ) {
//             $has_sinta_nscopus = true;
//         }

//         // Skor 2–6: scopus-based scoring
//         if ($is_scopus && $is_jurnal_or_seminar_or_bc) {
//             if ($bobot < 0.5) $scopus_under_05++;
//             elseif ($bobot == 0.5) $scopus_eq_05++;
//             elseif ($bobot >= 1 && $bobot <= 1.4) $scopus_1_to_1_4++;
//             elseif ($bobot >= 1.5) $scopus_gte_1_5++;

//             // Count scopus *journals* separately for Skor 6 condition
//             if ($jenis === 'jurnal') $extra_scopus_journal++;
//         }
//     }

//     // Evaluate score in priority order
//     if ($scopus_1_to_1_4 >= 1 && $extra_scopus_journal >= 2) return 6;
//     if ($scopus_gte_1_5 >= 1) return 5;
//     if ($scopus_1_to_1_4 >= 1) return 4;
//     if ($scopus_eq_05 >= 1) return 3;
//     if ($scopus_under_05 >= 1) return 2;
//     if ($has_sinta_nscopus) return 1;

//     return 0; // No score
// }