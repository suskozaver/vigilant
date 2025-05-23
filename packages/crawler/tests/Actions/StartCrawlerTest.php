<?php

namespace Vigilant\Crawler\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Vigilant\Crawler\Actions\StartCrawler;
use Vigilant\Crawler\Enums\State;
use Vigilant\Crawler\Jobs\ImportSitemapsJob;
use Vigilant\Crawler\Models\CrawledUrl;
use Vigilant\Crawler\Models\Crawler;
use Vigilant\Crawler\Tests\TestCase;

class StartCrawlerTest extends TestCase
{
    #[Test]
    public function it_starts_crawler(): void
    {
        /** @var Crawler $crawler */
        $crawler = Crawler::query()->create([
            'start_url' => 'vigilant',
            'schedule' => '0 0 * * *',
        ]);

        /** @var StartCrawler $action */
        $action = app(StartCrawler::class);
        $action->start($crawler);

        /** @var ?CrawledUrl $startUrl */
        $startUrl = $crawler->urls()->firstWhere('url', '=', 'vigilant');

        $this->assertNotNull($startUrl);
        $crawler->refresh();
        $this->assertEquals(State::Crawling, $crawler->state);
        $this->assertNull($crawler->crawler_stats);
    }

    #[Test]
    public function it_starts_sitemap_job(): void
    {
        Bus::fake();

        /** @var Crawler $crawler */
        $crawler = Crawler::query()->create([
            'start_url' => 'vigilant',
            'sitemaps' => ['sitemap-1'],
            'schedule' => '0 0 * * *',
        ]);

        /** @var StartCrawler $action */
        $action = app(StartCrawler::class);
        $action->start($crawler);

        Bus::assertDispatched(ImportSitemapsJob::class);
    }
}
