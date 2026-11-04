<?php

namespace Modules\CharacterBuilding\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CharacterBuilding\CharacterBuildingProgress;
use App\Services\CharacterBuilding\CharacterBuildingTargets;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CharacterBuildingController extends Controller
{
    public function __construct(private CharacterBuildingTargets $targets, private CharacterBuildingProgress $progress) {}

    /**
     * Five tabs: target scores, then Publikasi, Pengajaran, Community Development and Pengembangan Diri.
     */
    public function index()
    {
        [$publicationYear, $publicationQuarter] = $this->progress->defaultQuarter('publikasi');
        [$comdevYear, $comdevQuarter] = $this->progress->defaultQuarter('comdev');
        $start = (int) now()->year - (now()->month < 9 ? 1 : 0);

        return loadPage('characterbuilding::index', [
            'categories' => CharacterBuildingTargets::CATEGORIES,
            'targets' => $this->targets->grouped(),
            'years' => $this->progress->years(),
            'academicYears' => range($start + 1, $start - 4),
            'defaultAcademicYear' => $start,
            'currentQuarter' => (int) ceil(now()->month / 3),
            'publication' => $this->progress->publication($publicationYear, $publicationQuarter),
            'comdev' => $this->progress->comdev($comdevYear, $comdevQuarter),
        ]);
    }

    /** Sets the target and score of a period, replacing a saved one. */
    public function create(Request $request): JsonResponse
    {
        abort_unless(Auth::check(), 403);
        $target = $this->targets->save($request->only(['category', 'year', 'quarter', 'academic_year', 'semester', 'target', 'score']));

        return $this->targetResponse($target, "Target {$this->name($target)} disimpan.");
    }

    /**
     * The progress of a tab (publikasi or comdev) for a quarter.
     */
    public function read(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tab' => ['required', 'in:publikasi,comdev'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'quarter' => ['required', 'integer', 'between:1,4'],
        ]);
        $data = $this->progress->{$validated['tab'] === 'publikasi' ? 'publication' : 'comdev'}((int) $validated['year'], (int) $validated['quarter']);

        return response()->json(['html' => view('characterbuilding::'.($validated['tab'] === 'publikasi' ? 'publication' : 'comdev'), ['data' => $data])->render()]);
    }

    /** Changes the period, target or score of a saved target. */
    public function update(Request $request): JsonResponse
    {
        abort_unless(Auth::check(), 403);
        $validated = $request->validate(['id' => ['required', 'integer']]);
        $target = $this->targets->save($request->only(['year', 'quarter', 'academic_year', 'semester', 'target', 'score']), (int) $validated['id']);

        return $this->targetResponse($target, "Target {$this->name($target)} diperbarui.");
    }

    public function delete(Request $request): JsonResponse
    {
        abort_unless(Auth::check(), 403);
        $validated = $request->validate(['id' => ['required', 'integer']]);
        $target = $this->targets->delete((int) $validated['id']);

        return $this->targetResponse($target, "Target {$this->name($target)} dihapus.");
    }

    private function targetResponse(object $target, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message, 'category' => $target->category,
            'list_html' => view('characterbuilding::target_list', [
                'category' => $target->category, 'rows' => $this->targets->grouped()[$target->category],
            ])->render(),
        ]);
    }

    private function name(object $target): string
    {
        $definition = CharacterBuildingTargets::CATEGORIES[$target->category];

        return $definition['label'].' '.$target->label.' ('.CharacterBuildingTargets::number($target->target)
            .($definition['unit'] ? ' '.$definition['unit'] : '').' → skor '.CharacterBuildingTargets::number($target->score).')';
    }
}
