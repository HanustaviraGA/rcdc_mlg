<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\OutletPublikasi\Models\OutletPublikasi;
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
            $table->timestamps();
        });

        $this->outletTableReady = true;
    }

    protected function tearDown(): void
    {
        if ($this->outletTableReady) {
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

        $this->get(route('outletpublikasi.public', ['sort' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder(['Older Conference', 'Newer Conference']);
    }

    public function test_backoffice_endpoints_create_read_update_and_delete_outlet(): void
    {
        $payload = [
            'nama_conference' => 'Data Science Conference',
            'tipe_kerjasama' => 'BJIC',
            'deadline_submission' => '2026-10-20',
            'scope' => 'Data science, analytics, and responsible AI.',
            'contact_pic' => 'Dr. Budi - budi@example.test',
        ];

        $createResponse = $this->postJson(route('outletpublikasi.create'), $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nama_conference', 'Data Science Conference');

        $outletId = $createResponse->json('data.id');

        $this->postJson(route('outletpublikasi.read'), ['id' => $outletId])
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

        $this->deleteJson(route('outletpublikasi.delete'), ['id' => $outletId])
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
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipe_kerjasama');
    }

    private function createOutlet(array $overrides = []): OutletPublikasi
    {
        return OutletPublikasi::create(array_merge([
            'nama_conference' => 'Sample Conference',
            'tipe_kerjasama' => '-',
            'deadline_submission' => '2026-12-01',
            'scope' => 'Computer science and information systems',
            'contact_pic' => 'Sample PIC',
        ], $overrides));
    }
}
