<?php

// Functional

function TP12Func($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Data Scopus
    $hasScopus = $data->contains(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus';
    });

    // Skor 6: Skor 4 terpenuhi + ada jurnal scopus
    $hasJurnalScopus = $data->contains(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus';
    });

    // Skor 5: Skor 4 terpenuhi + ada seminar/book chapter scopus
    $hasSeminarOrBookScopus = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus';
    });

    // Skor 4:
    $skor4 = $data->contains(function ($item) {
        $jenis = strtolower($item->jenis);
        $tipe = strtolower($item->tipe_publikasi);
        $quartile = strtolower($item->quartile_jurnal ?? '');
        $firstAuthor = strtoupper($item->first_author ?? '');
        return $jenis === 'jurnal' && (
            ($tipe === 'nscopus' && $quartile === 'jurnal sinta 6' && $firstAuthor === 'Y') ||
            ($tipe === 'nscopus' && $quartile === 'proceeding')
        );
    });

    // Skor 3:
    $skor3 = $data->contains(function ($item) {
        $jenis = strtolower($item->jenis);
        $tipe = strtolower($item->tipe_publikasi);
        $quartile = strtolower($item->quartile_jurnal ?? '');
        $firstAuthor = strtoupper($item->first_author ?? '');
        return in_array($jenis, ['seminar', 'jurnal']) &&
               $tipe === 'scopus' &&
               str_contains($quartile, 'q') &&
               $firstAuthor === 'N';
    });

    // Skor 2:
    $skor2 = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
               strtolower($item->tipe_publikasi) === 'nscopus' &&
               strtolower($item->quartile_jurnal ?? '') === 'proceeding';
    }) && !$hasScopus;

    // Skor 1:
    $skor1 = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
               strtolower($item->tipe_publikasi) === 'nscopus' &&
               str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
    }) && !$hasScopus;

    // Penilaian Skor
    if ($skor4 && $hasJurnalScopus) {
        $score = 6;
    } elseif ($skor4 && $hasSeminarOrBookScopus) {
        $score = 5;
    } elseif ($skor4) {
        $score = 4;
    } elseif ($skor3) {
        $score = 3;
    } elseif ($skor2) {
        $score = 2;
    } elseif ($skor1) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
    ];
}

function AA2Func($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Filter semua publikasi scopus jenis seminar/jurnal/book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
               in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });

    $totalBobot = $scopusItems->sum('bobot');

    // Skor 5: totalBobot >= 1.5
    if ($totalBobot >= 1.5) {
        $score = 5;
    }

    // Skor 6: totalBobot between 1 and 1.4 AND ada tambahan jurnal scopus bobot >= 1
    elseif ($totalBobot >= 1 && $totalBobot <= 1.4) {
        $hasExtraScopusJurnal = $scopusItems->contains(function ($item) {
            return strtolower($item->jenis) === 'jurnal' && $item->bobot >= 1;
        });

        if ($hasExtraScopusJurnal) {
            $score = 6;
        } else {
            $score = 4;
        }
    }

    // Skor 3: totalBobot = 0.5
    elseif ($totalBobot == 0.5) {
        $score = 3;
    }

    // Skor 2: totalBobot < 0.5 && totalBobot > 0
    elseif ($totalBobot > 0 && $totalBobot < 0.5) {
        $score = 2;
    }

    // Skor 1: punya seminar/jurnal nscopus atau jurnal nscopus dengan quartile_jurnal LIKE Jurnal SINTA%
    elseif ($data->contains(function ($item) {
        $jenis = strtolower($item->jenis);
        $tipe = strtolower($item->tipe_publikasi);
        $quartile = strtolower($item->quartile_jurnal ?? '');
        return in_array($jenis, ['seminar', 'jurnal']) &&
               $tipe === 'nscopus' &&
               (str_contains($quartile, 'jurnal sinta') || empty($quartile));
    })) {
        $score = 1;
    }

    // Tidak memenuhi kriteria apapun
    else {
        $score = 0;
    }

    return [
        'kpi' => $score,
        'total_bobot' => $totalBobot,
    ];
}

function L2Func($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Filter publikasi Scopus dengan jenis seminar, jurnal, atau book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
               in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });

    $totalBobot = $scopusItems->sum('bobot');

    // Skor 4: totalBobot between 1 and 1.4
    if ($totalBobot >= 1 && $totalBobot <= 1.4) {
        // Cek jurnal tambahan bobot >= 1 (Skor 6)
        $hasJurnalBobot1Up = $scopusItems->contains(function ($item) {
            return strtolower($item->jenis) === 'jurnal' && $item->bobot >= 1;
        });

        if ($hasJurnalBobot1Up) {
            $score = 6;
        } else {
            // Cek jurnal tambahan bobot >= 0.25 < 1 (Skor 5)
            $hasJurnalBobot025to1 = $scopusItems->contains(function ($item) {
                return strtolower($item->jenis) === 'jurnal' &&
                       $item->bobot >= 0.25 && $item->bobot < 1;
            });

            if ($hasJurnalBobot025to1) {
                $score = 5;
            } else {
                $score = 4;
            }
        }
    }

    // Skor 3: totalBobot = 0.5
    elseif ($totalBobot == 0.5) {
        $score = 3;
    }

    // Skor 2: totalBobot < 0.5 && > 0
    elseif ($totalBobot > 0 && $totalBobot < 0.5) {
        $score = 2;
    }

    // Skor 1: punya seminar/jurnal nscopus OR jurnal nscopus dengan quartile_jurnal LIKE 'Jurnal SINTA%'
    elseif ($data->contains(function ($item) {
        $jenis = strtolower($item->jenis);
        $tipe = strtolower($item->tipe_publikasi);
        $quartile = strtolower($item->quartile_jurnal ?? '');
        return in_array($jenis, ['seminar', 'jurnal']) &&
               $tipe === 'nscopus' &&
               (str_contains($quartile, 'jurnal sinta') || empty($quartile));
    })) {
        $score = 1;
    }

    // Tidak memenuhi kriteria apapun
    else {
        $score = 0;
    }

    return [
        'kpi' => $score,
        'total_bobot' => $totalBobot,
    ];
}

function AA3TP3LK2Func($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Filter publikasi scopus dengan jenis seminar, jurnal, atau book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
               in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });

    $totalBobot = $scopusItems->sum('bobot');

    // Skor 4: totalBobot >= 2
    if ($totalBobot >= 2) {
        // Skor 6: ada jurnal scopus bobot >= 1.5
        $hasJurnalScopusB15Up = $scopusItems->contains(function ($item) {
            return strtolower($item->jenis) === 'jurnal' && $item->bobot >= 1.5;
        });

        if ($hasJurnalScopusB15Up) {
            $score = 6;
        } else {
            // Skor 5: ada jurnal scopus bobot >= 0.25 dan < 1.5
            $hasJurnalScopus025to149 = $scopusItems->contains(function ($item) {
                return strtolower($item->jenis) === 'jurnal' &&
                       $item->bobot >= 0.25 && $item->bobot < 1.5;
            });

            if ($hasJurnalScopus025to149) {
                $score = 5;
            } else {
                $score = 4;
            }
        }
    }
    // Skor 3: totalBobot >= 1 dan < 2
    elseif ($totalBobot >= 1 && $totalBobot < 2) {
        $score = 3;
    }
    // Skor 2: totalBobot < 1 dan > 0
    elseif ($totalBobot > 0 && $totalBobot < 1) {
        $score = 2;
    }
    // Skor 1: punya publikasi nscopus dengan jenis seminar/jurnal atau jurnal dengan "jurnal sinta"
    elseif ($data->contains(function ($item) {
        $jenis = strtolower($item->jenis);
        $tipe = strtolower($item->tipe_publikasi);
        $quartile = strtolower($item->quartile_jurnal ?? '');
        return in_array($jenis, ['seminar', 'jurnal']) &&
               $tipe === 'nscopus' &&
               (str_contains($quartile, 'jurnal sinta') || empty($quartile));
    })) {
        $score = 1;
    }
    // Tidak memenuhi kriteria apapun
    else {
        $score = 0;
    }

    return [
        'kpi' => $score,
        'total_bobot' => $totalBobot,
    ];
}

function L3LK3Func($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Ambil semua publikasi scopus dengan jenis seminar, jurnal, atau book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
               in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });

    $totalBobot = $scopusItems->sum('bobot');

    // Skor 4: totalBobot >= 4
    if ($totalBobot >= 4) {
        // Skor 6: ada jurnal scopus dengan bobot >= 2
        $hasJurnalScopusB2Up = $scopusItems->contains(function ($item) {
            return strtolower($item->jenis) === 'jurnal' && $item->bobot >= 2;
        });

        if ($hasJurnalScopusB2Up) {
            $score = 6;
        } else {
            // Skor 5: ada jurnal scopus dengan bobot >= 0.25 dan < 2
            $hasJurnalScopus025to199 = $scopusItems->contains(function ($item) {
                return strtolower($item->jenis) === 'jurnal' &&
                       $item->bobot >= 0.25 && $item->bobot < 2;
            });

            if ($hasJurnalScopus025to199) {
                $score = 5;
            } else {
                $score = 4;
            }
        }
    }
    // Skor 3: totalBobot >= 3 dan < 4
    elseif ($totalBobot >= 3 && $totalBobot < 4) {
        $score = 3;
    }
    // Skor 2: totalBobot < 3 dan > 0
    elseif ($totalBobot > 0 && $totalBobot < 3) {
        $score = 2;
    }
    // Skor 1: ada seminar/jurnal nscopus, atau jurnal nscopus & quartile LIKE "Jurnal SINTA%"
    elseif ($data->contains(function ($item) {
        $jenis = strtolower($item->jenis);
        $tipe = strtolower($item->tipe_publikasi);
        $quartile = strtolower($item->quartile_jurnal ?? '');
        return in_array($jenis, ['seminar', 'jurnal']) &&
               $tipe === 'nscopus' &&
               (str_contains($quartile, 'jurnal sinta') || empty($quartile));
    })) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
        'total_bobot' => $totalBobot,
    ];
}

function GBFunc($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Ambil semua publikasi scopus dengan jenis seminar, jurnal, atau book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
               in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });

    $totalBobot = $scopusItems->sum('bobot');

    // Skor 4: totalBobot >= 6
    if ($totalBobot >= 6) {
        // Skor 6:
        // - Ada jurnal scopus dengan bobot >= 2
        // OR
        // - Ada 1 book chapter scopus
        $hasJurnalScopusB2Up = $scopusItems->contains(function ($item) {
            return strtolower($item->jenis) === 'jurnal' && $item->bobot >= 2;
        });

        $hasBookChapter = $scopusItems->contains(function ($item) {
            return strtolower($item->jenis) === 'book chapter';
        });

        if ($hasJurnalScopusB2Up || $hasBookChapter) {
            $score = 6;
        } else {
            // Skor 5: ada jurnal scopus dengan bobot >= 0.25 < 2
            $hasJurnalScopus025to199 = $scopusItems->contains(function ($item) {
                return strtolower($item->jenis) === 'jurnal' &&
                       $item->bobot >= 0.25 && $item->bobot < 2;
            });

            if ($hasJurnalScopus025to199) {
                $score = 5;
            } else {
                $score = 4;
            }
        }
    }
    // Skor 3: totalBobot >= 5 dan < 6
    elseif ($totalBobot >= 5 && $totalBobot < 6) {
        $score = 3;
    }
    // Skor 2: totalBobot > 0 dan < 5
    elseif ($totalBobot > 0 && $totalBobot < 5) {
        $score = 2;
    }
    // Skor 1: ada seminar/jurnal nscopus, atau jurnal nscopus & quartile LIKE "Jurnal SINTA%"
    elseif ($data->contains(function ($item) {
        $jenis = strtolower($item->jenis);
        $tipe = strtolower($item->tipe_publikasi);
        $quartile = strtolower($item->quartile_jurnal ?? '');
        return in_array($jenis, ['seminar', 'jurnal']) &&
               $tipe === 'nscopus' &&
               (str_contains($quartile, 'jurnal sinta') || empty($quartile));
    })) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
        'total_bobot' => $totalBobot,
    ];
}

// Professional

function TP12Prof($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    $hasScopus = $data->contains(fn($item) => strtolower($item->tipe_publikasi) === 'scopus');

    // Skor 6: Syarat skor 4 terpenuhi + jurnal scopus
    $skor4Eligible = $data->contains(function ($item) {
        $jenis = strtolower($item->jenis);
        $tipe = strtolower($item->tipe_publikasi);
        $quartile = strtolower($item->quartile_jurnal ?? '');
        $firstAuthor = strtoupper($item->first_author) === 'Y';

        return (
            ($jenis === 'jurnal' && $tipe === 'nscopus' && $quartile === 'jurnal sinta 6' && $firstAuthor) ||
            ($jenis === 'jurnal' && $tipe === 'nscopus' && $firstAuthor)
        );
    });

    $hasJurnalScopus = $data->contains(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus';
    });

    $hasSeminarOrBookScopus = $data->contains(function ($item) {
        return (
            (strtolower($item->jenis) === 'seminar' && strtolower($item->tipe_publikasi) === 'scopus') ||
            (strtolower($item->jenis) === 'book chapter' && strtolower($item->tipe_publikasi) === 'scopus')
        );
    });

    $hasScopusNotFirstAuthor = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               strtoupper($item->first_author) === 'N';
    });

    $hasNScopusProceeding = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
               strtolower($item->tipe_publikasi) === 'nscopus' &&
               strtolower($item->quartile_jurnal ?? '') === 'proceeding';
    });

    $hasNScopusSinta = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
               strtolower($item->tipe_publikasi) === 'nscopus' &&
               str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
    });

    // Determine score
    if ($skor4Eligible && $hasJurnalScopus) {
        $score = 6;
    } elseif ($skor4Eligible && $hasSeminarOrBookScopus) {
        $score = 5;
    } elseif ($skor4Eligible) {
        $score = 4;
    } elseif ($hasScopusNotFirstAuthor) {
        $score = 3;
    } elseif ($hasNScopusProceeding && !$hasScopus) {
        $score = 2;
    } elseif ($hasNScopusSinta && !$hasScopus) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
    ];
}

function AA2Prof($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Skor 6: Skor 4 terpenuhi + ada jurnal scopus dengan bobot >= 0.25
    $skor4Eligible = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 0.5;
    });

    $hasJurnalScopus025 = $data->contains(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 0.25;
    });

    $hasJurnalScopus = $data->contains(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus';
    });

    $hasScopusGTE025LT05 = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 0.25 &&
               floatval($item->bobot) < 0.5;
    });

    $hasScopusLT025 = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) < 0.25;
    });

    $hasNScopusSinta = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
               strtolower($item->tipe_publikasi) === 'nscopus' &&
               str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
    });

    // Determine score
    if ($skor4Eligible && $hasJurnalScopus025) {
        $score = 6;
    } elseif ($skor4Eligible && $hasJurnalScopus) {
        $score = 5;
    } elseif ($skor4Eligible) {
        $score = 4;
    } elseif ($hasScopusGTE025LT05) {
        $score = 3;
    } elseif ($hasScopusLT025) {
        $score = 2;
    } elseif ($hasNScopusSinta) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
    ];
}

function L2Prof($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Skor 4: minimal ada 1 publikasi (seminar/jurnal/book chapter) scopus dengan bobot >= 0.5
    $skor4Eligible = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 0.5;
    });

    // Skor 6: Skor 4 terpenuhi + ada tambahan jurnal scopus dengan bobot >= 0.5
    $skor6AdditionalJurnal = $data->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 0.5;
    });

    // Skor 5: Skor 4 terpenuhi + ada jurnal scopus (tanpa syarat bobot)
    $hasJurnalScopus = $data->contains(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus';
    });

    $hasScopusGTE025LT05 = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 0.25 &&
               floatval($item->bobot) < 0.5;
    });

    $hasScopusLT025 = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) < 0.25;
    });

    $hasNScopusSinta = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
               strtolower($item->tipe_publikasi) === 'nscopus' &&
               str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
    });

    // Determine score
    if ($skor4Eligible && $skor6AdditionalJurnal->count() > 1) {
        $score = 6;
    } elseif ($skor4Eligible && $hasJurnalScopus) {
        $score = 5;
    } elseif ($skor4Eligible) {
        $score = 4;
    } elseif ($hasScopusGTE025LT05) {
        $score = 3;
    } elseif ($hasScopusLT025) {
        $score = 2;
    } elseif ($hasNScopusSinta) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
    ];
}

function AA3TP3LK2Prof($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Skor 4: ada Scopus (seminar/jurnal/book chapter) dengan bobot >= 1
    $skor4Eligible = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 1;
    });

    // Skor 6: Skor 4 terpenuhi + jurnal Scopus tambahan bobot >= 1
    $skor6Additional = $data->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 1;
    });

    // Skor 5: Skor 4 terpenuhi + jurnal Scopus (tanpa syarat bobot)
    $hasJurnalScopus = $data->contains(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus';
    });

    // Skor 3: Scopus dengan bobot >= 0.75 dan < 1
    $hasScopus075To099 = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 0.75 &&
               floatval($item->bobot) < 1;
    });

    // Skor 2: Scopus dengan bobot < 0.75
    $hasScopusLT075 = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) < 0.75;
    });

    // Skor 1: nscopus atau jurnal dengan quartile LIKE 'Jurnal SINTA%'
    $hasNScopusSinta = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
               strtolower($item->tipe_publikasi) === 'nscopus' &&
               str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
    });

    // Determine score
    if ($skor4Eligible && $skor6Additional->count() > 1) {
        $score = 6;
    } elseif ($skor4Eligible && $hasJurnalScopus) {
        $score = 5;
    } elseif ($skor4Eligible) {
        $score = 4;
    } elseif ($hasScopus075To099) {
        $score = 3;
    } elseif ($hasScopusLT075) {
        $score = 2;
    } elseif ($hasNScopusSinta) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
    ];
}

function L3Prof($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Skor 4: seminar/jurnal/book chapter Scopus dengan bobot >= 2
    $skor4Eligible = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 2;
    });

    // Skor 6: Skor 4 terpenuhi + jurnal Scopus dengan bobot >= 1
    $skor6Additional = $data->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) >= 1;
    });

    // Skor 5: Skor 4 terpenuhi + ada jurnal Scopus (tanpa bobot)
    $hasJurnalScopus = $data->contains(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               strtolower($item->tipe_publikasi) === 'scopus';
    });

    // Skor 3: Scopus dengan bobot >= 1 < 2
    $hasScopus1To2 = $data->contains(function ($item) {
        $bobot = floatval($item->bobot);
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               $bobot >= 1 && $bobot < 2;
    });

    // Skor 2: Scopus dengan bobot < 1
    $hasScopusLT1 = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']) &&
               strtolower($item->tipe_publikasi) === 'scopus' &&
               floatval($item->bobot) < 1;
    });

    // Skor 1: tipe nscopus atau quartile_jurnal LIKE 'Jurnal SINTA%'
    $hasNScopusSinta = $data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
               strtolower($item->tipe_publikasi) === 'nscopus' &&
               str_contains(strtolower($item->quartile_jurnal ?? ''), 'jurnal sinta');
    });

    // Penentuan skor
    if ($skor4Eligible && $skor6Additional->count() > 1) {
        $score = 6;
    } elseif ($skor4Eligible && $hasJurnalScopus) {
        $score = 5;
    } elseif ($skor4Eligible) {
        $score = 4;
    } elseif ($hasScopus1To2) {
        $score = 3;
    } elseif ($hasScopusLT1) {
        $score = 2;
    } elseif ($hasNScopusSinta) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
    ];
}


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