<?php

namespace Modules\OutletPublikasi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\OutletPublikasi\Models\OutletPublikasi;
use Modules\OutletPublikasi\Models\OutletPublikasiReferensi;
use Yajra\DataTables\Facades\DataTables;

class OutletPublikasiController extends Controller
{
    private const CATEGORIES = [
        'publikasi' => 'Publikasi',
        'book_chapter' => 'Book Chapter',
        'scopus_journals' => 'Scopus Journals',
        'sinta' => 'SINTA',
        'penelitian_hibah' => 'Penelitian & Hibah',
    ];

    public function index()
    {
        return loadPage('outletpublikasi::index');
    }

    public function publicIndex(Request $request)
    {
        $requestedTab = (string) $request->query('tab', 'publikasi');
        $activeTab = array_key_exists($requestedTab, self::CATEGORIES) ? $requestedTab : 'publikasi';
        $search = trim((string) $request->query('search', ''));
        $sort = $this->resolvePublicSort($activeTab, (string) $request->query('sort', ''));

        $query = $this->categoryQuery($activeTab);
        $this->applyPublicSearch($query, $activeTab, $search);
        $this->applyPublicSort($query, $activeTab, $sort);

        $records = $query
            ->paginate(10, ['*'], $activeTab.'_page')
            ->withQueryString();
        $tabs = self::CATEGORIES;

        return view('outletpublikasi::public', compact(
            'activeTab',
            'records',
            'search',
            'sort',
            'tabs'
        ));
    }

    public function init_table(Request $request)
    {
        $category = $this->validatedCategory($request);

        return DataTables::eloquent($this->categoryQuery($category))
            ->addIndexColumn()
            ->toJson();
    }

    public function create(Request $request)
    {
        $category = $this->validatedCategory($request);
        $data = $this->validatedData($request, $category);

        if ($category === 'publikasi') {
            $record = OutletPublikasi::create($data);
        } else {
            $record = OutletPublikasiReferensi::create([
                ...$data,
                'kategori' => $category,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => self::CATEGORIES[$category].' berhasil ditambahkan.',
            'data' => $record,
        ], 201);
    }

    public function read(Request $request)
    {
        $category = $this->validatedCategory($request);
        $record = $this->resolveRecord($request, $category);

        return response()->json([
            'success' => true,
            'data' => $record,
        ]);
    }

    public function update(Request $request)
    {
        $category = $this->validatedCategory($request);
        $record = $this->resolveRecord($request, $category);
        $record->update($this->validatedData($request, $category));

        return response()->json([
            'success' => true,
            'message' => self::CATEGORIES[$category].' berhasil diperbarui.',
            'data' => $record->fresh(),
        ]);
    }

    public function delete(Request $request)
    {
        $category = $this->validatedCategory($request);
        $record = $this->resolveRecord($request, $category);
        $record->delete();

        return response()->json([
            'success' => true,
            'message' => self::CATEGORIES[$category].' berhasil dihapus.',
        ]);
    }

    private function categoryQuery(string $category): Builder
    {
        if ($category === 'publikasi') {
            return OutletPublikasi::query();
        }

        return OutletPublikasiReferensi::query()->where('kategori', $category);
    }

    private function applyPublicSearch(Builder $query, string $category, string $search): void
    {
        if ($search === '') {
            return;
        }

        $columns = match ($category) {
            'publikasi' => ['nama_conference', 'scope'],
            'book_chapter' => ['nama', 'publication_frequency'],
            'scopus_journals' => ['nama', 'issn', 'quartile_sjr', 'publication_frequency', 'scope'],
            'sinta' => ['nama', 'issn', 'sinta', 'publication_frequency', 'scope'],
            'penelitian_hibah' => ['nama'],
        };

        $query->where(function (Builder $searchQuery) use ($columns, $search) {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $searchQuery->where($column, 'like', '%'.$search.'%');
                } else {
                    $searchQuery->orWhere($column, 'like', '%'.$search.'%');
                }
            }
        });
    }

    private function resolvePublicSort(string $category, string $requestedSort): string
    {
        if ($requestedSort === 'asc') {
            return $category === 'penelitian_hibah' ? 'deadline_asc' : 'update_asc';
        }

        if ($requestedSort === 'desc') {
            return $category === 'penelitian_hibah' ? 'deadline_desc' : 'update_desc';
        }

        $allowedSorts = match ($category) {
            'publikasi' => ['deadline_desc', 'deadline_asc', 'update_desc', 'update_asc', 'name_asc', 'name_desc'],
            'book_chapter', 'scopus_journals', 'sinta' => ['update_desc', 'update_asc', 'name_asc', 'name_desc'],
            'penelitian_hibah' => ['deadline_asc', 'deadline_desc', 'name_asc', 'name_desc'],
        };

        if (in_array($requestedSort, $allowedSorts, true)) {
            return $requestedSort;
        }

        return $category === 'penelitian_hibah' ? 'deadline_asc' : 'update_desc';
    }

    private function applyPublicSort(Builder $query, string $category, string $sort): void
    {
        $nameColumn = $category === 'publikasi' ? 'nama_conference' : 'nama';
        [$column, $direction] = match ($sort) {
            'deadline_desc' => ['deadline_submission', 'desc'],
            'deadline_asc' => ['deadline_submission', 'asc'],
            'update_asc' => ['created_at', 'asc'],
            'name_asc' => [$nameColumn, 'asc'],
            'name_desc' => [$nameColumn, 'desc'],
            default => ['created_at', 'desc'],
        };

        $query->orderBy($column, $direction)->orderBy('id', $direction);
    }

    private function validatedCategory(Request $request): string
    {
        return $request->validate([
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
        ])['category'];
    }

    private function resolveRecord(Request $request, string $category): Model
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
        ]);

        return $this->categoryQuery($category)->findOrFail($validated['id']);
    }

    private function validatedData(Request $request, string $category): array
    {
        $this->normalizeWebsiteUrl($request);

        $rules = match ($category) {
            'publikasi' => [
                'nama_conference' => ['required', 'string', 'max:255'],
                'tipe_kerjasama' => ['required', Rule::in(['BJIC', 'Co-Host', '-'])],
                'deadline_submission' => ['required', 'date'],
                'scope' => ['required', 'string', 'max:20000'],
                'contact_pic' => ['required', 'string', 'max:255'],
                'url_website' => ['required', 'url:http,https', 'max:2048'],
            ],
            'book_chapter' => [
                'nama' => ['required', 'string', 'max:255'],
                'publication_frequency' => ['required', 'string', 'max:255'],
                'url_website' => ['required', 'url:http,https', 'max:2048'],
            ],
            'scopus_journals' => [
                'nama' => ['required', 'string', 'max:255'],
                'issn' => ['required', 'string', 'max:50'],
                'quartile_sjr' => ['required', 'string', 'max:100'],
                'publication_frequency' => ['required', 'string', 'max:255'],
                'scope' => ['required', 'string', 'max:20000'],
                'url_website' => ['required', 'url:http,https', 'max:2048'],
            ],
            'sinta' => [
                'nama' => ['required', 'string', 'max:255'],
                'issn' => ['required', 'string', 'max:50'],
                'sinta' => ['required', 'string', 'max:50'],
                'publication_frequency' => ['required', 'string', 'max:255'],
                'scope' => ['required', 'string', 'max:20000'],
                'url_website' => ['required', 'url:http,https', 'max:2048'],
            ],
            'penelitian_hibah' => [
                'nama' => ['required', 'string', 'max:255'],
                'deadline_submission' => ['required', 'date'],
                'url_website' => ['required', 'url:http,https', 'max:2048'],
            ],
        };

        return $request->validate($rules, [], [
            'nama' => 'nama',
            'nama_conference' => 'nama conference',
            'publication_frequency' => 'publication frequency',
            'quartile_sjr' => 'quartile - SJR',
            'deadline_submission' => 'deadline submission',
            'url_website' => 'URL website',
        ]);
    }

    private function normalizeWebsiteUrl(Request $request): void
    {
        $url = trim((string) $request->input('url_website', ''));

        if ($url !== '' && ! preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://'.$url;
        }

        $request->merge(['url_website' => $url]);
    }
}
