<?php

namespace App\Tests\Unit;

use App\Entity\Comment;
use App\HttpClient\SpamChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;


class SpamCheckerUnitTest extends TestCase
{
    // Happy path
    /** @dataProvider getComments */
    public function testSpamScore(
        int $expectedScore,
        ResponseInterface $response,
        Comment $comment,
        array $context
    ): void
    {
        $client = new MockHttpClient([$response]);
        $checker = new SpamChecker($client, 'abcde');

        $score = $checker->getSpamScore($comment, $context);
        $this->assertSame($expectedScore, $score);
    }


    // bad path
    public function testSpamScoreWithInvalidRequest(): void
    {
        $comment = new Comment('lerele comment');
        $comment->setCreatedAtValue();
        $context = [];

        /**
         * La clase MockHttpClient permite hacer un mock de cualquier servidor HTTP.
         * Para ello toma un array de instancias MockResponse que contienen el cuerpo
         * esperado y las cabeceras de cada respuesta.
         */
        $client = new MockHttpClient([
            new MockResponse(
                'invalid',
                ['response_headers' => ['x-akismet-debug-help: Invalid key']]
            )
        ]);
        // Set invalid Akismet API key
        $checker = new SpamChecker($client, 'abcde');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to check for spam: invalid (Invalid key).');

        $checker->getSpamScore($comment, $context);
    }



    public function getComments(): iterable
    {
        $comment = new Comment();
        $comment->setCreatedAtValue();
        $context = [];

        // possible response scores: 0, 1, 2
        // comment is a "blatant spam"
        $response = new MockResponse('', ['response_headers' => ['x-akismet-pro-tip: discard']]);
        yield 'blatant_spam' => [2, $response, $comment, $context];

        // comment might be spam
        $response = new MockResponse('true');
        yield 'spam' => [1, $response, $comment, $context];

        // Not spam
        $response = new MockResponse('false');
        yield 'ham' => [0, $response, $comment, $context];
    }

}
