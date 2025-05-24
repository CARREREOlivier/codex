<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomepageTest extends WebTestCase
{
    public function testHomepageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        @mkdir(__DIR__ . '/../tmp', recursive: true);
        file_put_contents('tests/tmp/homepage.html', $client->getResponse()->getContent());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1.homepage-title');
        $this->assertSelectorTextContains('h1', 'Le Codex d\'Uuki');
    }
}
