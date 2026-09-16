<?php

namespace App\Http\Controllers;

use App\Services\Research\ResearchGallery;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ResearchGalleryController extends Controller
{
    public function index(Request $request, ResearchGallery $gallery)
    {
        $filters = $request->validate([
            'source' => ['nullable', 'in:rectorate,system,all'], 'year' => ['nullable', 'integer', 'between:1900,2100'],
            'search' => ['nullable', 'string', 'max:200'], 'field' => ['nullable', 'string', 'max:200'],
            'faculty' => ['nullable', 'string', 'max:200'], 'person' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:new,old,az'], 'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $filters['source'] = $filters['source'] ?? 'rectorate';
        $filters['sort'] = $filters['sort'] ?? 'new';
        $all = $gallery->projects();
        $source = $filters['source'] === 'all' ? $all : $all->where('source', $filters['source']);
        $options = [
            'years' => $source->pluck('year')->filter()->unique()->sortDesc()->values(),
            'fields' => $source->pluck('fields')->flatten()->unique()->sort()->values(),
            'faculties' => $source->pluck('faculties')->flatten()->unique()->sort()->values(),
            'people' => $source->pluck('people')->flatten(1)->filter(fn ($person) => $person['code'] !== '')->unique('code')->sortBy('name')->values(),
        ];
        $matches = $source->filter(function ($project) use ($filters) {
            if (! empty($filters['year']) && $project['year'] !== (int) $filters['year']) {
                return false;
            }
            foreach (['field' => 'fields', 'faculty' => 'faculties'] as $filter => $field) {
                if (! empty($filters[$filter]) && ! in_array($filters[$filter], $project[$field], true)) {
                    return false;
                }
            }
            if (! empty($filters['person']) && ! collect($project['people'])->contains('code', $filters['person'])) {
                return false;
            }
            $text = implode(' ', [$project['title'], $project['code'], implode(' ', $project['fields']),
                implode(' ', array_column($project['people'], 'name')), implode(' ', $project['programs']), implode(' ', $project['sdgs'])]);

            return empty($filters['search']) || str_contains(mb_strtolower($text), mb_strtolower($filters['search']));
        });
        $matches = match ($filters['sort']) {
            'az' => $matches->sortBy(fn ($p) => mb_strtolower($p['title'])),
            'old' => $matches->sortBy([['year', 'asc'], ['title', 'asc']]),
            default => $matches->sortBy([['year', 'desc'], ['title', 'asc']]),
        };
        $page = LengthAwarePaginator::resolveCurrentPage();
        $projects = new LengthAwarePaginator($matches->forPage($page, 12)->values(), $matches->count(), 12, $page, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);
        $stats = ['projects' => $source->count(), 'people' => $source->pluck('people')->flatten(1)->pluck('code')->filter()->unique()->count(),
            'years' => $options['years']->count(), 'rectorate' => $all->where('source', 'rectorate')->count(), 'system' => $all->where('source', 'system')->count()];
        $featured = $source->sortByDesc('year')->take(3)->values();

        return view('landing.research_gallery.index', compact('projects', 'filters', 'options', 'stats', 'featured'));
    }

    public function show(string $source, string $id, ResearchGallery $gallery)
    {
        $project = $gallery->projects()->first(fn ($p) => $p['source'] === $source && $p['id'] === $id);
        abort_unless($project, 404);

        return view('landing.research_gallery.detail', compact('project'));
    }
}
