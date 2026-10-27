<?php

namespace Tests\Feature\Api;

use App\Models\Jurisprudence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class JurisprudenceSearchScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyword_matches_every_field_when_no_scope_is_given(): void
    {
        $this->record(['gr_number' => 'A', 'citation' => 'Manila ordinance']);
        $this->record(['gr_number' => 'B', 'reference' => 'Manila ordinance']);
        $this->record(['gr_number' => 'C', 'subject' => 'Unrelated']);

        $response = $this->getJson('/api/public/jurisprudence?search=manila');

        $response->assertOk();
        $this->assertSame(['A', 'B'], $this->numbers($response));
    }

    public function test_scope_restricts_the_keyword_to_the_requested_column(): void
    {
        $this->record(['gr_number' => 'A', 'reference' => 'Manila ordinance']);
        $this->record(['gr_number' => 'B', 'citation' => 'Manila ordinance']);
        $this->record(['gr_number' => 'C', 'ponente' => 'Manila']);

        $response = $this->getJson('/api/public/jurisprudence?search=manila&fields=reference');

        $response->assertOk();
        $this->assertSame(['A'], $this->numbers($response));
    }

    public function test_scope_accepts_several_columns(): void
    {
        $this->record(['gr_number' => 'A', 'reference' => 'Manila ordinance']);
        $this->record(['gr_number' => 'B', 'citation' => 'Manila ordinance']);
        $this->record(['gr_number' => 'C', 'subject' => 'Manila ordinance']);

        $response = $this->getJson('/api/public/jurisprudence?search=manila&fields=citation,reference');

        $response->assertOk();
        $this->assertSame(['A', 'B'], $this->numbers($response));
    }

    public function test_scope_can_target_the_date_column(): void
    {
        $this->record(['gr_number' => 'A', 'date' => '2020-05-04']);
        $this->record(['gr_number' => 'B', 'date' => '1999-01-01', 'citation' => 'Effective in 2020']);

        $response = $this->getJson('/api/public/jurisprudence?search=2020&fields=date');

        $response->assertOk();
        $this->assertSame(['A'], $this->numbers($response));
    }

    public function test_unknown_scope_values_fall_back_to_every_field(): void
    {
        $this->record(['gr_number' => 'A', 'reference' => 'Manila ordinance']);
        $this->record(['gr_number' => 'B', 'citation' => 'Manila ordinance']);

        $response = $this->getJson('/api/public/jurisprudence?search=manila&fields=bogus');

        $response->assertOk();
        $this->assertSame(['A', 'B'], $this->numbers($response));
    }

    public function test_scope_and_year_filters_apply_together(): void
    {
        $this->record(['gr_number' => 'A', 'date' => '2020-05-04', 'reference' => 'Manila ordinance']);
        $this->record(['gr_number' => 'B', 'date' => '2021-05-04', 'reference' => 'Manila ordinance']);
        $this->record(['gr_number' => 'C', 'date' => '2020-06-06', 'citation' => 'Manila ordinance']);

        $response = $this->getJson('/api/public/jurisprudence?search=manila&fields=reference&year=2020');

        $response->assertOk();
        $this->assertSame(['A'], $this->numbers($response));
    }

    private function record(array $attributes): Jurisprudence
    {
        return Jurisprudence::create(array_merge([
            'user_id' => 1,
            'gr_number' => 'G.R. No. 1',
            'date' => '2020-01-01',
            'citation' => null,
            'ponente' => null,
            'reference' => null,
            'url' => 'gr_1_2020.html',
            'pdf_availability' => false,
            'pdf_path' => null,
            'subject' => null,
        ], $attributes));
    }

    /**
     * Sorted so the assertion does not depend on the order of equally-dated rows.
     *
     * @return array<int, string>
     */
    private function numbers(TestResponse $response): array
    {
        return collect($response->json('data'))->pluck('gr_number')->sort()->values()->all();
    }
}
