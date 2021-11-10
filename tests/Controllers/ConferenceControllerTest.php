<?php

namespace App\Tests\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex()
    {
        /*
         * La variable $client simula un navegador.
         * En lugar de hacer llamadas HTTP al servidor, llama directamente a la app Symfony
         */
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback');
    }
}
