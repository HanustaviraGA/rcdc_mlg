<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\OutletPublikasi\Models\OutletPublikasi;
use Modules\OutletPublikasi\Models\OutletPublikasiReferensi;
use Tests\TestCase;

class OutletPublikasiTest extends TestCase
{
    private bool $outletTableReady = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Ekstensi pdo_sqlite diperlukan untuk feature test database.');
        }

        if (config('database.default') !== 'sqlite') {
            $this->markTestSkipped('Feature test ini hanya boleh dijalankan pada database SQLite terisolasi.');
        }

        Schema::create('outlet_publikasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_conference');
            $table->string('tipe_kerjasama', 20)->default('-');
            $table->date('deadline_submission');
            $table->text('scope');
            $table->string('contact_pic');
            $table->string('url_website', 2048)->nullable();
            $table->timestamps();
        });

        Schema::create('outlet_publikasi_referensi', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 40);
            $table->string('nama');
            $table->string('issn', 50)->nullable();
            $table->string('quartile_sjr', 100)->nullable();
            $table->string('sinta', 50)->nullable();
            $table->string('publication_frequency')->nullable();
            $table->text('scope')->nullable();
            $table->date('deadline_submission')->nullable();
            $table->string('url_website', 2048);
            $table->timestamps();
        });

        $this->outletTableReady = true;
    }

    protected function tearDown(): void
    {
        if ($this->outletTableReady) {
            Schema::dropIfExists('outlet_publikasi_referensi');
            Schema::dropIfExists('outlet_publikasi');
        }

        parent::tearDown();
    }

    public function test_public_page_can_search_by_conference_name_and_scope(): void
    {
        $this->createOutlet([
            'nama_conference' => 'International AI Conference',
            'scope' => 'Artificial intelligence and machine learning',
        ]);
        $this->createOutlet([
            'nama_conference' => 'Quantum Computing Forum',
            'scope' => 'Quantum algorithms and information systems',
        ]);

        $this->get(route('outletpublikasi.public', ['search' => 'International AI']))
            ->assertOk()
            ->assertSee('Book Chapter')
            ->assertSee('Scopus Journals')
            ->assertSee('SINTA')
            ->assertSee('Penelitian &amp; Hibah', false)
            ->assertSee('International AI Conference')
            ->assertDontSee('Quantum Computing Forum');

        $this->get(route('outletpublikasi.public', ['search' => 'Quantum algorithms']))
            ->assertOk()
            ->assertSee('Quantum Computing Forum')
            ->assertDontSee('International AI Conference');
    }

    public function test_public_page_sorts_update_column_using_created_at(): void
    {
        $older = $this->createOutlet(['nama_conference' => 'Older Conference']);
        $newer = $this->createOutlet(['nama_conference' => 'Newer Conference']);

        $older->forceFill(['created_at' => now()->subDays(2)])->saveQuietly();
        $newer->forceFill(['created_at' => now()->subDay()])->saveQuietly();

        $this->get(route('outletpublikasi.public'))
            ->assertOk()
            ->assertSeeInOrder(['Newer Conference', 'Older Conference']);

        $this->get(route('outletpublikasi.public', ['sort' => 'update_asc']))
            ->assertOk()
            ->assertSeeInOrder(['Older Conference', 'Newer Conference']);
    }

    public function test_public_page_can_sort_by_deadline_and_contextual_name(): void
    {
        $alpha = $this->createOutlet([
            'nama_conference' => 'Alpha Conference',
            'deadline_submission' => '2026-09-01',
        ]);
        $zulu = $this->createOutlet([
            'nama_conference' => 'Zulu Conference',
            'deadline_submission' => '2026-12-01',
        ]);

        $this->get(route('outletpublikasi.public', ['sort' => 'deadline_desc']))
            ->assertOk()
            ->assertSeeInOrder([$zulu->nama_conference, $alpha->nama_conference])
            ->assertSee('Deadline Submission')
            ->assertSee('Nama Conference');

        $this->get(route('outletpublikasi.public', ['sort' => 'name_asc']))
            ->assertOk()
            ->assertSeeInOrder([$alpha->nama_conference, $zulu->nama_conference]);
    }

    public function test_backoffice_endpoints_create_read_update_and_delete_outlet(): void
    {
        $payload = [
            'nama_conference' => 'Data Science Conference',
            'tipe_kerjasama' => 'BJIC',
            'deadline_submission' => '2026-10-20',
            'scope' => 'Data science, analytics, and responsible AI.',
            'contact_pic' => 'Dr. Budi - budi@example.test',
            'url_website' => 'conference.example.test',
            'category' => 'publikasi',
        ];

        $createResponse = $this->postJson(route('outletpublikasi.create'), $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nama_conference', 'Data Science Conference')
            ->assertJsonPath('data.url_website', 'https://conference.example.test');

        $outletId = $createResponse->json('data.id');

        $this->postJson(route('outletpublikasi.read'), ['id' => $outletId, 'category' => 'publikasi'])
            ->assertOk()
            ->assertJsonPath('data.scope', $payload['scope']);

        $this->putJson(route('outletpublikasi.update'), [
            ...$payload,
            'id' => $outletId,
            'nama_conference' => 'Updated Data Science Conference',
            'tipe_kerjasama' => 'Co-Host',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nama_conference', 'Updated Data Science Conference');

        $this->assertDatabaseHas('outlet_publikasi', [
            'id' => $outletId,
            'nama_conference' => 'Updated Data Science Conference',
            'tipe_kerjasama' => 'Co-Host',
        ]);

        $this->deleteJson(route('outletpublikasi.delete'), ['id' => $outletId, 'category' => 'publikasi'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('outlet_publikasi', ['id' => $outletId]);
    }

    public function test_backoffice_rejects_invalid_collaboration_type(): void
    {
        $this->postJson(route('outletpublikasi.create'), [
            'nama_conference' => 'Invalid Conference',
            'tipe_kerjasama' => 'Partner',
            'deadline_submission' => '2026-10-20',
            'scope' => 'Information systems',
            'contact_pic' => 'PIC',
            'url_website' => 'https://example.test',
            'category' => 'publikasi',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipe_kerjasama');
    }

    public function test_each_landing_tab_has_specific_columns_search_and_title_link(): void
    {
        $this->createReference([
            'kategori' => 'book_chapter',
            'nama' => 'Digital Transformation Handbook',
            'publication_frequency' => 'Twice a year',
            'url_website' => 'https://book.example.test',
        ]);
        $this->createReference([
            'kategori' => 'scopus_journals',
            'nama' => 'Journal of Intelligent Systems',
            'issn' => '1234-5678',
            'quartile_sjr' => 'Q1 - 1.25',
            'publication_frequency' => 'Quarterly',
            'scope' => 'Artificial intelligence and robotics',
            'url_website' => 'https://scopus.example.test',
        ]);
        $this->createReference([
            'kategori' => 'sinta',
            'nama' => 'Jurnal Teknologi Nusantara',
            'issn' => '9876-5432',
            'sinta' => 'SINTA 2',
            'publication_frequency' => 'Biannual',
            'scope' => 'Teknologi informasi',
            'url_website' => 'https://sinta.example.test',
        ]);
        $this->createReference([
            'kategori' => 'penelitian_hibah',
            'nama' => 'Hibah Fundamental Nasional',
            'deadline_submission' => '2026-11-20',
            'url_website' => 'https://hibah.example.test',
        ]);

        $this->get(route('outletpublikasi.public', ['tab' => 'book_chapter', 'search' => 'Twice a year']))
            ->assertOk()
            ->assertSee('Nama Book Chapter')
            ->assertSee('Digital Transformation Handbook')
            ->assertSee('https://book.example.test');

        $this->get(route('outletpublikasi.public', ['tab' => 'scopus_journals', 'search' => 'Q1']))
            ->assertOk()
            ->assertSee('Quartile - SJR')
            ->assertSee('Journal of Intelligent Systems')
            ->assertSee('Lihat Scope');

        $this->get(route('outletpublikasi.public', ['tab' => 'sinta', 'search' => 'SINTA 2']))
            ->assertOk()
            ->assertSee('Jurnal Teknologi Nusantara')
            ->assertSee('SINTA 2')
            ->assertDontSee('Quartile - SJR');

        $this->get(route('outletpublikasi.public', ['tab' => 'penelitian_hibah', 'search' => 'Fundamental']))
            ->assertOk()
            ->assertSee('Nama Hibah')
            ->assertSee('Hibah Fundamental Nasional')
            ->assertSee('https://hibah.example.test');
    }

    public function test_backoffice_crud_supports_all_new_categories(): void
    {
        $cases = [
            'book_chapter' => [
                'nama' => 'Book Chapter Test',
                'publication_frequency' => 'Annual',
                'url_website' => 'book.test',
            ],
            'scopus_journals' => [
                'nama' => 'Scopus Journal Test',
                'issn' => '1111-2222',
                'quartile_sjr' => 'Q2 - 0.75',
                'publication_frequency' => 'Quarterly',
                'scope' => 'Computer science',
                'url_website' => 'scopus.test',
            ],
            'sinta' => [
                'nama' => 'SINTA Journal Test',
                'issn' => '3333-4444',
                'sinta' => 'SINTA 3',
                'publication_frequency' => 'Biannual',
                'scope' => 'Information systems',
                'url_website' => 'sinta.test',
            ],
            'penelitian_hibah' => [
                'nama' => 'Research Grant Test',
                'deadline_submission' => '2026-12-15',
                'url_website' => 'grant.test',
            ],
        ];

        foreach ($cases as $category => $payload) {
            $response = $this->postJson(route('outletpublikasi.create'), [
                ...$payload,
                'category' => $category,
            ])->assertCreated()->assertJsonPath('success', true);

            $recordId = $response->json('data.id');

            $this->postJson(route('outletpublikasi.read'), ['id' => $recordId, 'category' => $category])
                ->assertOk()
                ->assertJsonPath('data.url_website', 'https://'.$payload['url_website']);

            $this->putJson(route('outletpublikasi.update'), [
                ...$payload,
                'id' => $recordId,
                'category' => $category,
                'nama' => $payload['nama'].' Updated',
            ])->assertOk()->assertJsonPath('data.nama', $payload['nama'].' Updated');

            $this->deleteJson(route('outletpublikasi.delete'), ['id' => $recordId, 'category' => $category])
                ->assertOk()
                ->assertJsonPath('success', true);
        }

        $this->assertDatabaseCount('outlet_publikasi_referensi', 0);
    }

    private function createOutlet(array $overrides = []): OutletPublikasi
    {
        return OutletPublikasi::create(array_merge([
            'nama_conference' => 'Sample Conference',
            'tipe_kerjasama' => '-',
            'deadline_submission' => '2026-12-01',
            'scope' => 'Computer science and information systems',
            'contact_pic' => 'Sample PIC',
            'url_website' => 'https://conference.example.test',
        ], $overrides));
    }

    private function createReference(array $attributes): OutletPublikasiReferensi
    {
        return OutletPublikasiReferensi::create($attributes);
    }
}
