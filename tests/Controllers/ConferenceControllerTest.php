<?php

namespace App\Tests\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        /*
         * La variable $client simula un navegador.
         * En lugar de hacer llamadas HTTP al servidor, llama directamente a la app Symfony
         */
        $client = static::createClient();
        // vamos a la página de inicio
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback');
    }

    /**
     * Describamos lo que sucede en esta prueba en un lenguaje sencillo:
     *
     * Como en la primera prueba, vamos a la página de inicio;
     *
     * El método request() devuelve una instancia Crawler que ayuda a encontrar elementos en la página
     * (como enlaces, formularios, o cualquier cosa a la que se pueda llegar con selectores CSS o XPath);
     *
     * Gracias a un selector CSS, nos aseguramos de que tenemos dos conferencias listadas en homepage;
     *
     * Luego hacemos clic en el enlace "Ver" (como no puede hacer clic en más de un enlace a la vez,
     * Symfony elige automáticamente el primero que encuentra);
     *
     * Verificamos el título de la página, la respuesta y el <h2> de la página para asegurarnos de
     * que estamos en la página correcta (también podríamos haber comprobado que la ruta coincide);
     *
     * Finalmente, verificamos que hay 1 comentario en la página. div:contains() no es un selector
     * de CSS válido, pero Symfony incluye algunas mejoras prestadas de jQuery.
     * En lugar de hacer clic en el texto (es decir, View), también podríamos haber seleccionado
     * el enlace a través de un selector CSS:
     *   $client->click($crawler->filter('h4 + p a')->link());
     *
     */
    public function testConferencePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertCount(2, $crawler->filter('h4'));

        $client->clickLink('View');

        $this->assertPageTitleContains('Amsterdam');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Amsterdam 2019');
        $this->assertSelectorExists('div:contains("There are 1 comments")');
    }
}
