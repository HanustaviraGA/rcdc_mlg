<?php

namespace Tests\Unit;

use App\Services\Publications\FacultyResearchInsights;
use PHPUnit\Framework\TestCase;

class FacultyResearchInsightsTest extends TestCase
{
    public function test_projects_count_once_across_members_and_preserve_actual_topics_and_sdgs(): void
    {
        $service = new FacultyResearchInsights;
        $rows = collect([
            $this->grant(['kode_dosen_nim' => 'D1', 'sdg_numbers' => '[8,9,9,99]', 'subtopik_research_roadmap' => 'Sustainable business']),
            $this->grant(['kode_dosen_nim' => 'D2', 'peran' => 'Anggota 1', 'sdg_numbers' => '[9,11]']),
            $this->grant(['kode_dosen_nim' => 'MHS', 'kategori_fm_eksternal_mahasiswa' => 'Mahasiswa']),
            $this->grant(['project_key' => 'older', 'budget_year' => 2025, 'judul' => 'Older project', 'sdg_numbers' => '[]', 'keyword_sdgs' => 'Education']),
        ]);
        $projects = $service->projects($rows);
        $this->assertCount(2, $projects);
        $this->assertSame(['D1', 'D2'], $projects[0]['codes']);
        $this->assertSame([8, 9, 11], $projects[0]['sdgs']);
        $this->assertSame(['Sustainable business'], $projects[0]['topics']);
        $this->assertSame('Subtopik Research Roadmap', $projects[0]['topic_source']);
        $this->assertSame(['Older project'], $projects[1]['topics']);
        $this->assertSame([], $projects[1]['sdgs']);
        $this->assertArrayNotHasKey('nidn', $projects[0]);
        $this->assertArrayNotHasKey('email_mitra', $projects[0]);
    }

    public function test_cluster_a_requires_profile_scopus_and_external_leadership(): void
    {
        $service = new FacultyResearchInsights;
        $result = $service->cluster(2026, 'S3', 'AA', collect([$this->paper()]), collect(), collect([$this->grant()]), true);
        $this->assertSame('A', $result['cluster']);
        $this->assertSame('Otomatis dari import', $result['source']);
        $this->assertSame(1, $result['evidence']['external_chair_projects']);
        $this->assertSame(1, $result['evidence']['scopus_titles']);
        $this->assertSame('A', $service->cluster(2026, 'S2', 'L', collect([$this->paper()]), collect(), collect([$this->grant()]), true)['cluster']);
    }

    public function test_internal_pib_and_member_roles_do_not_create_cluster_a(): void
    {
        $service = new FacultyResearchInsights;
        $pib = $this->grant(['sumber_dana' => 'Internasional - PIB', 'sumber_pemberi_hibah' => 'BINUS']);
        $member = $this->grant(['project_key' => 'member', 'peran' => 'Anggota 1']);
        $this->assertSame('internal', $service->funding($pib));
        $result = $service->cluster(2026, 'S2', 'L', collect([$this->paper()]), collect(), collect([$pib, $member]), true);
        $this->assertSame('B', $result['cluster']);
        $this->assertSame(0, $result['evidence']['external_chair_projects']);
    }

    public function test_cluster_uses_rtto_evidence_and_excludes_future_records(): void
    {
        $service = new FacultyResearchInsights;
        $entries = collect([(object) ['year' => 2025, 'rtto_scopus' => 0.25]]);
        $grants = collect([$this->grant()]);
        $result = $service->cluster(2025, 'S3', null, collect([$this->paper()]), $entries, $grants, true);
        $this->assertSame('B', $result['cluster']);
        $this->assertSame(0, $result['evidence']['scopus_titles']);
        $this->assertSame(0.25, $result['evidence']['rtto_scopus_max']);
        $this->assertSame(0, $result['evidence']['external_chair_projects']);
        $this->assertNull($service->cluster(2024, 'S3', null, collect([$this->paper()]), $entries, $grants, false)['cluster']);
    }

    public function test_cluster_c_missing_profiles_and_manual_assignments_remain_distinct(): void
    {
        $service = new FacultyResearchInsights;
        $result = $service->cluster(2026, 'S2', 'AA', collect([$this->paper()]), collect(), collect([$this->grant()]), true);
        $this->assertSame('C', $result['cluster']);
        $this->assertStringContainsString('Keanggotaan RIG/Research Center belum tercatat', $result['reason']);
        $this->assertNull($service->cluster(2026, null, null, collect(), collect(), collect(), false)['cluster']);
        $manual = $service->cluster(2026, 'S2', 'L', collect([$this->paper()]), collect(), collect([$this->grant()]), true, (object) ['cluster' => 'B']);
        $this->assertSame('B', $manual['cluster']);
        $this->assertSame('A', $manual['calculated_cluster']);
        $this->assertSame('Penetapan', $manual['source']);
    }

    private function grant(array $overrides = []): object
    {
        return (object) array_replace([
            'project_key' => 'project', 'budget_year' => 2026, 'kd_prop' => 'P1', 'judul' => 'Grant title',
            'kode_dosen_nim' => 'D1', 'kategori_fm_eksternal_mahasiswa' => 'FM', 'peran' => 'Ketua',
            'sumber_dana' => 'Nasional - DIKTI', 'sumber_pemberi_hibah' => 'Dalam Negeri (Nasional)',
            'sdg_numbers' => '[8]', 'sdgs' => null, 'subtopik_research_roadmap' => null,
            'keyword_sdgs' => null, 'rig_bdsrc_fbrc' => null, 'nidn' => 'private', 'email_mitra' => 'private',
        ], $overrides);
    }

    private function paper(): object
    {
        return (object) ['year' => 2026, 'request_code' => 'R1', 'tipe_publikasi' => 'Scopus', 'submitted' => 'Scopus FM'];
    }
}
