<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\DataTables;
use App\Mail\Notification;
use GuzzleHttp\Client;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Decoders\DataUriImageDecoder;
use Intervention\Image\Decoders\Base64ImageDecoder;
use Intervention\Image\Decoders\FilePathImageDecoder;
use Intervention\Image\Image;
use Carbon\Carbon;
use \Mpdf\Mpdf;
use App\Models\Dosen;
use App\Models\RectorateDosen;
use FPDF\FPDF;

/**
 * Display a table based on given module or query.
 * @return Renderable
 */
function select_table($queryOrModel)
{
    if ($queryOrModel instanceof Model) {
        $query = $queryOrModel->newQuery();
    } elseif ($queryOrModel instanceof Builder) {
        $query = $queryOrModel;
    } elseif ($queryOrModel instanceof Collection) {
        if ($queryOrModel->isEmpty()) {
            return DataTables::of($queryOrModel)->make(true);
        }
        return DataTables::of($queryOrModel)
            ->addColumn('no', function ($data) {
                static $count = 1;
                return '<td><span style="margin-left: 20px !important;">' . $count++ . '.</span></td>';
            })
            ->rawColumns(['no'])
            ->make(true);
    } else {
        throw new \InvalidArgumentException('Invalid query or model provided.');
    }

    $table = $query->getModel()->getTable();
    $columns = Schema::getColumnListing($table);

    return DataTables::of($query)
        ->addColumn('no', function ($data) {
            static $count = 1;
            $primaryKeyValue = base64_encode(json_encode($data->getKey()));
            return '<td><span style="margin-left: 20px !important;">' . $count++ . '.</span><input type="checkbox" name="checkbox" data-record="' . $primaryKeyValue . '" style="display: none;"></td>';
        })
        ->rawColumns(['no'])
        ->make(true);
}

/**
 * Display an encoded view of a page.
 * @return Renderable
 */
function loadPage($page, $data = []){
    $view = view($page, $data)->render(); // Render the view as a string
    $base64 = base64_encode($view); // Encode the view as base64
    return response()->json(['page' => $base64]);
}

/**
 * Send an email.
 */
function sendEmail($data){
    $send = Mail::to($data['to'])->send(new Notification($data));
    if($send){
        return true;
    }
    return false;
}

/**
 * Generate unique code.
 */
function generateCode($prefix = 'RCDC') {
    $date = date('ymd');
    $suffix = generateSuffix();
    return $prefix . '.' . $date . '.' . $suffix;
}

/**
 * Generate unique suffix.
 */
function generateSuffix() {
    $suffix = '';
    $length = 6; // Desired length of the suffix (5 characters)

    while (strlen($suffix) < $length) {
        $randType = rand(0, 2); // Randomly choose 0 for letter, 1 for number, 2 for alphanumeric

        if ($randType === 0) {
            $suffix .= chr(rand(65, 90)); // Random letter from A to Z
        } elseif ($randType === 1) {
            $suffix .= rand(0, 9); // Random number from 0 to 9
        } else {
            $suffix .= chr(rand(65, 90)) . rand(0, 9); // Random alphanumeric combination
        }
    }

    // Trim or pad the suffix to ensure it has exactly 5 characters
    $suffix = substr($suffix, 0, $length);

    return $suffix;
}

/**
 * Unix Epoch Conversion.
 */
function ts_conv($ts){
    $timestamp_sec = $ts / 1000;
    $date = date("Y-m-d H:i:s", $timestamp_sec);
    return $date;
}

function imageUploader($data = []){
    // Attributes
    $file = $data['file'];
    if(isset($data['base64']) && $data['base64'] == TRUE){
        $img = ImageManager::gd()->read($file, [
            DataUriImageDecoder::class,
            Base64ImageDecoder::class,
        ]);
    }else{
        $img = ImageManager::gd()->read($file);
    }
    $setimg = md5(base64_encode(rand(0, 100).generateCode())).'.jpg';
    if(isset($data['filename']) && $data['filename'] !== ''){
        $setimg = $data['filename'].'.jpg';
    }
    // Save the original
    $ori = $img;
    if(isset($data['to_base64']) && $data['to_base64'] == TRUE){
        if(isset($data['resize']) && $data['resize'] == TRUE){
            $ori->resize($data['width'], $data['height']);
        }
        $stat = $ori->toJpeg(50)->toDataUri();
    }else{
        $stat = true;
        if(isset($data['filepath']) && $data['filepath'] !== ''){
            if(isset($data['resize']) && $data['resize'] == TRUE){
                $ori->resize($data['width'], $data['height']);
            }
            $stat = $ori->toJpeg(50)->save($data['filepath'].$setimg);
        }else{
            if(isset($data['resize']) && $data['resize'] == TRUE){
                $ori->resize($data['width'], $data['height']);
            }
            $ori->toJpeg(50)->save(public_path('uploads/artikel/origins/').$setimg);
        }
    }
    return [
        'filename' => $setimg,
        'status' => $stat
    ];
}

function sanitizeTitle($title){
    $sanitized = rtrim(strtolower(str_replace(" ", "-", preg_replace("/[^a-zA-Z0-9\s]/", "", $title))));
    return $sanitized;
}

function cleanString($input) {
    // Use a regular expression to remove all characters except letters, numbers, spaces, hyphens, and dots
    return preg_replace("/[^a-zA-Z0-9\s\-.]/", "", $input);
}

function dateformat($tgl) {
    try {
        if ($tgl != null && $tgl != "" && $tgl != "0000-00-00") {
            // Create a DateTime object
            $date = new DateTime($tgl);

            // Get day name in Indonesian
            $day_names = array(
                "Monday" => "Monday", "Tuesday" => "Tuesday", "Wednesday" => "Wednesday",
                "Thursday" => "Thursday", "Friday" => "Friday", "Saturday" => "Saturday",
                "Sunday" => "Sunday"
            );
            $day_name = $day_names[$date->format('l')];

            // Get month name in Indonesian
            $month_names = array(
                "", "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            );
            $month_name = $month_names[intval($date->format('m'))];

            // Format date
            $tanggal = $date->format('d');
            $tahun = $date->format('Y');

            return $day_name . ", " . $tanggal . " " . $month_name . " " . $tahun;
        }
    } catch (Exception $e) {
        // Handle invalid date format
        return "Monday, 0 January 2024"; // Default date value
    }
    return ""; // Return an empty string if the input date is invalid
}

function datedff($dateString, $status = 0) {
    $currentDate = new DateTime();
    $targetDate = new DateTime($dateString);

    // Calculate difference
    $interval = $currentDate->diff($targetDate);
    $days = (int)$interval->format('%r%a'); // %r for sign, %a for absolute days

    // Check if status is > 4 (project is started)
    if ($status > 4) {
        return "Berakhir";
    }

    // Check if the date is today
    if ($days === 0) {
        return "Hari Ini";
    } elseif ($days < 0) {
        return "Kedaluarsa";
    }

    // Check for years, months, or days left
    if ($interval->y > 0) {
        return $interval->y . " Tahun Lagi";
    } elseif ($interval->m > 0) {
        return $interval->m . " Bulan Lagi";
    } else {
        return $interval->d . " Hari Lagi";
    }
}

function monthdff($date1, $date2){
    $createdAt = Carbon::parse($date1); // replace with your actual date
    $comparation = Carbon::parse($date2); // replace with your actual date

    // Calculate the difference in months
    $monthsDifference = $createdAt->diffInMonths($comparation);

    return round($monthsDifference);
}

function truncateDescription($description, $maxLength = 65) {
    if (strlen($description) > $maxLength) {
        return substr($description, 0, $maxLength) . '...';
    }
    return $description;
}

/**
 * Create a PDF based on HTML given.
 * @return Renderable
 */
function viewPDF($data, $config) {
    $pdf = \PDF::loadView('pdf', $data, [], $config);
    $pdf->save($config['filepath']);
}

function updateEnv($key, $value)
{
    $filePath = base_path('.env'); // Path to .env file
    $fileContents = file_get_contents($filePath);

    // Check if the key already exists in .env
    if (preg_match("/^{$key}=.*/m", $fileContents)) {
        // Update the existing key
        $fileContents = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $fileContents);
    } else {
        // Append if the key doesn't exist
        $fileContents .= "\n{$key}={$value}\n";
    }

    file_put_contents($filePath, $fileContents);

    // Clear and cache config to apply changes
    \Artisan::call('config:clear');
    \Artisan::call('config:cache');
}

function formatSize($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

function getScore($scopus, $nscopus, $thresholds) {
    if ($scopus >= $thresholds[5]) return 6;
    if ($scopus >= $thresholds[4]) return 5;
    if ($scopus >= $thresholds[3]) return 4;
    if ($scopus >= $thresholds[2]) return 3;
    if ($scopus > 0) return 2;      // only if there's some Scopus
    if ($nscopus > 0) return 1;     // has only non-Scopus
    return 0;                       // no publications at all
}

function getScoreNew($kode_dosen, $scopus, $nscopus) {
    $query = Dosen::where('kode_dosen', $kode_dosen)->first();
    $ft = $query['ft_dosen'];
    $jja = $query['jja_dosen'];
    $pendidikan = $query['pendidikan_dosen'];
    $skor = 0;
    switch($ft){
        case 'Functional':
            switch($jja){
                case 'TP':
                    if($pendidikan == 'S1' || $pendidikan == 'S2'){
                        $rectorate = RectorateDosen::where('kode_dosen', $kode_dosen)->get();
                        foreach($rectorate as $dosen){
                            
                        }
                        

                    }else if($pendidikan == 'S3'){
                        
                    }
                    break;
                case 'AA':
                    if($pendidikan == 'S2'){

                    }else if($pendidikan == 'S3'){
                        
                    }
                    break;
                case 'L':
                    if($pendidikan == 'S2'){

                    }else if($pendidikan == 'S3'){
                        
                    }
                    break;
                case 'LK':
                    if($pendidikan == 'S2'){

                    }else if($pendidikan == 'S3'){
                        
                    }
                    break;
                case 'GB':
                    break;
                default:
                    return null;
            }
            break;
        case 'Professional':
            switch($jja){
                case 'TP':
                    break;
                case 'AA':
                    break;
                case 'L':
                    break;
                case 'LK':
                    break;
                case 'GB':
                    break;
                default:
                    return null;
            }
            break;
        default:
            return null;
    }
}

function tableKPI($kode_dosen, $nscopus, $scopus) {
    // Convert #N/A or non-numeric to 0
    if ($nscopus === '#N/A') $nscopus = 0;
    if ($scopus === '#N/A') $scopus = 0;

    if (Dosen::where('kode_dosen', $kode_dosen)->exists()) {
        $dosen = Dosen::where('kode_dosen', $kode_dosen)->first();
        $ft = $dosen['ft_dosen'];
        $jja = strtoupper($dosen['jja_dosen']);
        $pendidikan = strtoupper($dosen['pendidikan_dosen']);

        $score = 0;

        if($scopus == 0 && $nscopus == 0) {
            $score = 0;
        }else{
            // FUNCTIONAL
            if ($ft === 'Functional') {
                if (($jja === 'TP' && in_array($pendidikan, ['S1', 'S2']))) {
                    $score = getScore($nscopus, $scopus, [0, 0, 0.5, 1, 1.5, 1.5]);
                }
                else if ($jja === 'AA' && $pendidikan === 'S2') {
                    $score = getScore($nscopus, $scopus, [0, 0, 0.5, 1, 1, 1]);
                }
                else if ($jja === 'L' && $pendidikan === 'S2') {
                    $score = getScore($nscopus, $scopus, [0, 0, 0.5, 1, 1, 1]);
                }
                else if (
                    ($jja === 'AA' && $pendidikan === 'S3') ||
                    ($jja === 'TP' && $pendidikan === 'S3') ||
                    ($jja === 'LK' && $pendidikan === 'S2')
                ) {
                    $score = getScore($nscopus, $scopus, [0, 0, 1, 2, 2, 2]);
                }
                else if (
                    ($jja === 'L' && $pendidikan === 'S3') ||
                    ($jja === 'LK' && $pendidikan === 'S3')
                ) {
                    $score = getScore($nscopus, $scopus, [0, 0, 1.5, 2, 2, 2]);
                }
                else if ($jja === 'GB') {
                    $score = getScore($nscopus, $scopus, [0, 0, 2, 4, 4, 6]);
                }
            }

            // PROFESSIONAL
            else if ($ft === 'Professional') {
                if ($jja === 'TP' && in_array($pendidikan, ['S1', 'S2'])) {
                    $score = getScore($nscopus, $scopus, [0, 0, 0.25, 0.5, 0.5, 0.5]);
                }
                else if ($jja === 'AA' && $pendidikan === 'S2') {
                    $score = getScore($nscopus, $scopus, [0, 0, 0.25, 0.5, 0.5, 0.5]);
                }
                else if ($jja === 'L' && $pendidikan === 'S2') {
                    $score = getScore($nscopus, $scopus, [0, 0, 0.25, 0.5, 0.5, 0.5]);
                }
                else if (
                    ($jja === 'AA' && $pendidikan === 'S3') ||
                    ($jja === 'TP' && $pendidikan === 'S3') ||
                    ($jja === 'LK' && $pendidikan === 'S2')
                ) {
                    $score = getScore($nscopus, $scopus, [0, 0, 0.75, 1, 1, 1]);
                }
                else if ($jja === 'L' && $pendidikan === 'S3') {
                    $score = getScore($nscopus, $scopus, [0, 0, 1, 2, 2, 2]);
                }
            }

            // Others fallback
            else {
                $score = 1;
            }
        }

        return [
            'score' => $score,
            'nscopus' => $nscopus,
            'scopus' => $scopus,
            'dosen' => $dosen,
        ];
    }

    return null; // dosen not found
}

// Functional
function TP12Func($kode_dosen) {
    $publications = RectorateDosen::where('kode_dosen', $kode_dosen)->get();

    $has_scopus = false;
    $has_seminar_nscopus_sinta = false;
    $has_seminar_nscopus_proceeding = false;
    $has_scopus_q_nonfirst = false;
    $has_nscopus_sinta6_first = false;
    $has_nscopus_proceeding = false;
    $has_seminar_scopus = false;
    $has_journal_scopus = false;

    foreach ($publications as $pub) {
        $jenis = strtolower($pub->jenis);
        $tipe = strtolower($pub->tipe_publikasi);
        $quartile = strtolower($pub->quartile_jurnal ?? '');
        $first_author = strtoupper($pub->first_author) === 'Y';

        $is_jurnal_or_seminar = $jenis === 'Jurnal' || $jenis === 'Seminar';
        $is_scopus = $tipe === 'Scopus';
        $is_nscopus = $tipe === 'Nscopus';

        // Any scopus publication
        if ($is_scopus) {
            $has_scopus = true;
        }

        // Skor 1
        if ($is_jurnal_or_seminar && $is_nscopus && str_contains($quartile, 'Jurnal Sinta')) {
            $has_seminar_nscopus_sinta = true;
        }

        // Skor 2
        if ($is_jurnal_or_seminar && $is_nscopus && $quartile === 'Proceeding') {
            $has_seminar_nscopus_proceeding = true;
        }

        // Skor 3
        if ($is_jurnal_or_seminar && $is_scopus && str_contains($quartile, 'Q') && !$first_author) {
            $has_scopus_q_nonfirst = true;
        }

        // Skor 4
        if ($jenis === 'Jurnal') {
            if ($is_nscopus && $quartile === 'Jurnal Sinta 6' && $first_author) {
                $has_nscopus_sinta6_first = true;
            }
            if ($is_nscopus && $quartile === 'Proceeding') {
                $has_nscopus_proceeding = true;
            }
        }

        // Skor 5
        if ($jenis === 'Seminar' && $is_scopus || $jenis === 'Book Chapter' && $is_scopus) {
            $has_seminar_scopus = true;
        }

        // Skor 6
        if ($jenis === 'Jurnal' && $is_scopus) {
            $has_journal_scopus = true;
        }
    }

    if (($has_nscopus_sinta6_first || $has_nscopus_proceeding) && $has_journal_scopus) return 6;
    if (($has_nscopus_sinta6_first || $has_nscopus_proceeding) && $has_seminar_scopus) return 5;
    if ($has_nscopus_sinta6_first || $has_nscopus_proceeding) return 4;
    if ($has_scopus_q_nonfirst) return 3;
    if ($has_seminar_nscopus_proceeding && !$has_scopus) return 2;
    if ($has_seminar_nscopus_sinta && !$has_scopus) return 1;

    return 0; // Default score if no match
}

function AA2Func($kodeDosen){
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();
    // Get total bobot from all scopus-type seminar/jurnal/book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
            in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });
    $totalBobot = $scopusItems->sum('bobot');
    // Skor 5
    if ($scopusItems->contains(fn($item) => $item->bobot >= 1.5)) {
        $score = 5;
    }
    // Skor 4
    elseif ($scopusItems->contains(fn($item) => $item->bobot >= 1 && $item->bobot <= 1.4)) {
        // Check for Skor 6:
        $hasExtraScopusJurnal = $scopusItems->filter(function ($item) {
            return strtolower($item->jenis) === 'jurnal';
        })->count() > 1;
        if ($hasExtraScopusJurnal) {
            $score = 6;
        }else{
            $score = 4;
        }
    }
    // Skor 3
    elseif ($scopusItems->contains(fn($item) => $item->bobot == 0.5)) {
        $score = 3;
    }
    // Skor 2
    elseif ($scopusItems->contains(fn($item) => $item->bobot < 0.5)) {
        $score = 2;
    }
    // Skor 1
    elseif ($data->contains(function ($item) {
        return in_array(strtolower($item->jenis), ['seminar', 'jurnal']) &&
            strtolower($item->tipe_publikasi) === 'nscopus' &&
            str_contains(strtolower($item->quartile_jurnal), 'jurnal sinta');
    })) {
        $score = 1;
    }
    // No matching data
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

    // Total bobot from all scopus seminar/jurnal/book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
            in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });

    $totalBobot = $scopusItems->sum('bobot');

    // Base for Skor 4 requirement
    $mainScopus = $scopusItems->filter(function ($item) {
        return $item->bobot >= 1 && $item->bobot <= 1.4;
    });

    // Additional for Skor 5: Jurnal Scopus with bobot >= 0.25 and < 1
    $additionalJurnalFor5 = $scopusItems->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' && 
               $item->bobot >= 0.25 && $item->bobot < 1;
    });

    // Additional for Skor 6: Jurnal Scopus with bobot >= 1
    $additionalJurnalFor6 = $scopusItems->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' && 
               $item->bobot >= 1;
    });

    if ($mainScopus->isNotEmpty() && $additionalJurnalFor6->count() > 1) {
        $score = 6;
    } elseif ($mainScopus->isNotEmpty() && $additionalJurnalFor5->isNotEmpty()) {
        $score = 5;
    } elseif ($mainScopus->isNotEmpty()) {
        $score = 4;
    } elseif ($scopusItems->contains(fn($item) => $item->bobot == 0.5)) {
        $score = 3;
    } elseif ($scopusItems->contains(fn($item) => $item->bobot < 0.5)) {
        $score = 2;
    } elseif (
        $data->contains(function ($item) {
            $jenis = strtolower($item->jenis);
            $tipe = strtolower($item->tipe_publikasi);
            $quartile = strtolower($item->quartile_jurnal ?? '');
            return (
                in_array($jenis, ['seminar', 'jurnal']) &&
                $tipe === 'nscopus' &&
                (str_contains($quartile, 'jurnal sinta') || $jenis === 'seminar')
            );
        })
    ) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
        'total_bobot' => $totalBobot,
    ];
}

function AA3TP3LK2Func($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Filter Scopus publications of type seminar/jurnal/book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
            in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });

    $totalBobot = $scopusItems->sum('bobot');

    // Main scopus item with bobot >= 2 (Skor 4 requirement)
    $mainScopus = $scopusItems->filter(function ($item) {
        return $item->bobot >= 2;
    });

    // Additional jurnal scopus with bobot >= 1.5 (Skor 6)
    $additionalJurnalFor6 = $scopusItems->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' && $item->bobot >= 1.5;
    });

    // Additional jurnal scopus with bobot >= 0.25 and < 1.5 (Skor 5)
    $additionalJurnalFor5 = $scopusItems->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               $item->bobot >= 0.25 && $item->bobot < 1.5;
    });

    if ($mainScopus->isNotEmpty() && $additionalJurnalFor6->count() > 1) {
        $score = 6;
    } elseif ($mainScopus->isNotEmpty() && $additionalJurnalFor5->isNotEmpty()) {
        $score = 5;
    } elseif ($mainScopus->isNotEmpty()) {
        $score = 4;
    } elseif ($scopusItems->contains(fn($item) => $item->bobot >= 1 && $item->bobot < 2)) {
        $score = 3;
    } elseif ($scopusItems->contains(fn($item) => $item->bobot < 1)) {
        $score = 2;
    } elseif (
        $data->contains(function ($item) {
            $jenis = strtolower($item->jenis);
            $tipe = strtolower($item->tipe_publikasi);
            $quartile = strtolower($item->quartile_jurnal ?? '');
            return (
                in_array($jenis, ['seminar', 'jurnal']) &&
                $tipe === 'nscopus' &&
                (str_contains($quartile, 'jurnal sinta') || $jenis === 'seminar')
            );
        })
    ) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
        'total_bobot' => $totalBobot,
    ];
}

function L3LK3Func($kodeDosen) {
    $data = RectorateDosen::where('kode_dosen', $kodeDosen)->get();

    // Filter Scopus publications of type seminar/jurnal/book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
            in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });

    $totalBobot = $scopusItems->sum('bobot');

    // Main Scopus item with bobot >= 4 (Skor 4 requirement)
    $mainScopus = $scopusItems->filter(function ($item) {
        return $item->bobot >= 4;
    });

    // Additional jurnal scopus with bobot >= 2 (Skor 6)
    $additionalJurnalFor6 = $scopusItems->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' && $item->bobot >= 2;
    });

    // Additional jurnal scopus with bobot >= 0.25 and < 2 (Skor 5)
    $additionalJurnalFor5 = $scopusItems->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               $item->bobot >= 0.25 && $item->bobot < 2;
    });

    if ($mainScopus->isNotEmpty() && $additionalJurnalFor6->count() > 1) {
        $score = 6;
    } elseif ($mainScopus->isNotEmpty() && $additionalJurnalFor5->isNotEmpty()) {
        $score = 5;
    } elseif ($mainScopus->isNotEmpty()) {
        $score = 4;
    } elseif ($scopusItems->contains(fn($item) => $item->bobot >= 3 && $item->bobot < 4)) {
        $score = 3;
    } elseif ($scopusItems->contains(fn($item) => $item->bobot < 3)) {
        $score = 2;
    } elseif (
        $data->contains(function ($item) {
            $jenis = strtolower($item->jenis);
            $tipe = strtolower($item->tipe_publikasi);
            $quartile = strtolower($item->quartile_jurnal ?? '');
            return (
                in_array($jenis, ['seminar', 'jurnal']) &&
                $tipe === 'nscopus' &&
                (str_contains($quartile, 'jurnal sinta') || $jenis === 'seminar')
            );
        })
    ) {
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

    // Filter Scopus publications of type seminar/jurnal/book chapter
    $scopusItems = $data->filter(function ($item) {
        return strtolower($item->tipe_publikasi) === 'scopus' &&
            in_array(strtolower($item->jenis), ['seminar', 'jurnal', 'book chapter']);
    });

    $totalBobot = $scopusItems->sum('bobot');

    // Main Scopus item with bobot >= 6 (Skor 4 requirement)
    $mainScopus = $scopusItems->filter(function ($item) {
        return $item->bobot >= 6;
    });

    // Additional jurnal scopus with bobot >= 2 OR any book chapter (Skor 6)
    $additionalFor6 = $scopusItems->filter(function ($item) {
        return (strtolower($item->jenis) === 'jurnal' && $item->bobot >= 2) ||
               strtolower($item->jenis) === 'book chapter';
    });

    // Additional jurnal scopus with bobot >= 0.25 and < 2 (Skor 5)
    $additionalFor5 = $scopusItems->filter(function ($item) {
        return strtolower($item->jenis) === 'jurnal' &&
               $item->bobot >= 0.25 && $item->bobot < 2;
    });

    if ($mainScopus->isNotEmpty() && $additionalFor6->count() > 1) {
        $score = 6;
    } elseif ($mainScopus->isNotEmpty() && $additionalFor5->isNotEmpty()) {
        $score = 5;
    } elseif ($mainScopus->isNotEmpty()) {
        $score = 4;
    } elseif ($scopusItems->contains(fn($item) => $item->bobot >= 5 && $item->bobot < 6)) {
        $score = 3;
    } elseif ($scopusItems->contains(fn($item) => $item->bobot < 5)) {
        $score = 2;
    } elseif (
        $data->contains(function ($item) {
            $jenis = strtolower($item->jenis);
            $tipe = strtolower($item->tipe_publikasi);
            $quartile = strtolower($item->quartile_jurnal ?? '');
            return (
                in_array($jenis, ['seminar', 'jurnal']) &&
                $tipe === 'nscopus' &&
                (str_contains($quartile, 'jurnal sinta') || $jenis === 'seminar')
            );
        })
    ) {
        $score = 1;
    } else {
        $score = 0;
    }

    return [
        'kpi' => $score,
        'total_bobot' => $totalBobot,
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

// Utility function to center text
function centerTextX($imageWidth, $fontSize, $font, $text) {
    $bbox = imagettfbbox($fontSize, 0, $font, $text);
    $textWidth = $bbox[2] - $bbox[0];
    return ($imageWidth - $textWidth) / 2;
}

function fndformat($tgl) {
    try {
        if ($tgl != null && $tgl != "" && $tgl != "0000-00-00") {
            // Create a DateTime object
            $date = new DateTime($tgl);

            // Get day name in Indonesian
            $day_names = array(
                "Monday" => "Senin", "Tuesday" => "Selasa", "Wednesday" => "Rabu",
                "Thursday" => "Kamis", "Friday" => "Jumat", "Saturday" => "Sabtu",
                "Sunday" => "Minggu"
            );
            $day_name = $day_names[$date->format('l')];

            // Get month name in Indonesian
            $month_names = array(
                "", "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            );
            $month_name = $month_names[intval($date->format('m'))];

            // Format date
            $tanggal = $date->format('d');
            $tahun = $date->format('Y');

            return $tanggal . " " . $month_name . " " . $tahun;
        }
    } catch (Exception $e) {
        // Handle invalid date format
        return "Senin, 0 Januari 2024"; // Default date value
    }
    return ""; // Return an empty string if the input date is invalid
}

function createSertif($config){
    // Load the certificate template
    $image = @imagecreatefrompng(public_path('assets/sertif_gen/certi2.png'));

    // Set text color
    $black = imagecolorallocate($image, 0, 0, 0);

    // Font paths
    $font = public_path('assets/sertif_gen/calibri-regular.ttf');
    $font_bold = public_path('assets/sertif_gen/calibri-bold.ttf');

    // Dynamic data
    // $name = 'Hanustavira Guru Acarya, S.Kom.';
    // $eventname = '"Strategi Bedah dan Menulis Paper Scopus dengan Data Sekunder"';
    // $date = 'Malang, ' . fndformat(date('Y-m-d'));
    // $director = 'Dr. Robertus Tang Herman, S.E, M.M';
    // $position = 'BINUS @ Malang Campus Director';
    // $certificateNumber = 'No.285/DIR/MLG/III/2025';

    $name = $config['name'];
    $eventname = '"'.$config['eventname'].'"';
    $date = 'Malang, ' . fndformat($config['date']);
    $director = 'Dr. Robertus Tang Herman, S.E, M.M';
    $position = 'BINUS @ Malang Campus Director';
    $certificateId = $config['number'].'/DIR/MLG/III/'.date('Y');
    $certificateNumber = 'No.'.$certificateId.'';

    // Load and resize signature
    $signature = imagecreatefrompng(public_path('assets/sertif_gen/pak_robert.png'));
    $signatureResized = imagecreatetruecolor(120, 75);
    imagealphablending($signatureResized, false);
    imagesavealpha($signatureResized, true);
    imagecopyresampled(
        $signatureResized, $signature,
        0, 0, 0, 0,
        120, 75,
        imagesx($signature), imagesy($signature)
    );

    // Certificate width
    $imageWidth = imagesx($image);

    // Draw certificate content
    imagettftext($image, 20, 0, centerTextX($imageWidth, 20, $font_bold, 'Certificate of Appreciation'), 130, $black, $font_bold, 'Certificate of Appreciation');
    imagettftext($image, 11, 0, centerTextX($imageWidth, 11, $font, $certificateNumber), 155, $black, $font, $certificateNumber);
    imagettftext($image, 14, 0, centerTextX($imageWidth, 14, $font, 'This Certificate is Presented to:'), 200, $black, $font, 'This Certificate is Presented to:');

    // Draw name (centered and underlined)
    $fontSizeName = 16;
    $yName = 230;
    $xName = centerTextX($imageWidth, $fontSizeName, $font_bold, $name);
    imagettftext($image, $fontSizeName, 0, $xName, $yName, $black, $font_bold, $name);
    imageline($image, $xName, $yName + 5, $xName + (imagettfbbox($fontSizeName, 0, $font_bold, $name)[2] - imagettfbbox($fontSizeName, 0, $font_bold, $name)[0]), $yName + 5, $black);

    // Participation line
    imagettftext($image, 11, 0, centerTextX($imageWidth, 11, $font, 'for actively participating in the'), 260, $black, $font, 'for actively participating in the');

    // Event name (centered)
    imagettftext($image, 11, 0, centerTextX($imageWidth, 11, $font_bold, $eventname), 280, $black, $font_bold, $eventname);

    // Date (centered)
    imagettftext($image, 11, 0, centerTextX($imageWidth, 11, $font, $date), 320, $black, $font, $date);

    // Signature image
    imagecopy($image, $signatureResized, 270, 325, 0, 0, 120, 75);

    // Director name (centered and underlined)
    $fontSizeDir = 12;
    $yDir = 417;
    $xDir = centerTextX($imageWidth, $fontSizeDir, $font_bold, $director);
    imagettftext($image, $fontSizeDir, 0, $xDir, $yDir, $black, $font_bold, $director);
    imageline($image, $xDir, $yDir + 5, $xDir + (imagettfbbox($fontSizeDir, 0, $font_bold, $director)[2] - imagettfbbox($fontSizeDir, 0, $font_bold, $director)[0]), $yDir + 5, $black);

    // Director position (centered below the name)
    imagettftext($image, 11, 0, centerTextX($imageWidth, 11, $font, $position), $yDir + 20, $black, $font, $position);

    // Output final image
    $filename = 'Sertifikat-'.$config['name'].'-'.$config['number'];
    $output = public_path('uploads/sertifikat/'.$filename.'.png');
    imagepng($image, $output, 0);

    // Create a new PDF
    $pdf = new \FPDF('L', 'mm', 'A4'); // L = Landscape
    $pdf->AddPage();

    // Get image dimensions
    list($width, $height) = getimagesize($output);

    // Convert pixels to mm (assuming 96 dpi)
    $widthMm = $width * 25.4 / 56;
    $heightMm = $height * 25.4 / 56;

    // Fit image to page (optional: center it)
    $pdf->Image($output, (297 - $widthMm) / 2, (210 - $heightMm) / 2, $widthMm, $heightMm);

    // Output to file or browser
    unlink(public_path('uploads/sertifikat/'.$filename.'.png'));
    $pdf->Output('F', public_path('uploads/sertifikat/'.$filename.'.pdf')); // Save as file
    // $string = $pdf->Output('S', '');
    // $retdata = "data:application/pdf;base64,".base64_encode($string);
    // return $retdata;
}