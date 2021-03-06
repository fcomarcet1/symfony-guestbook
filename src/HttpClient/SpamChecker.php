<?php
declare(strict_types=1);


namespace App\HttpClient;

use App\Entity\Comment;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker
{
    private HttpClientInterface $client;
    private string $endpoint;

    public function __construct(HttpClientInterface $client, string $akismetAPIKey)
    {
        $this->client = $client;
        $this->endpoint = sprintf('https://%s.rest.akismet.com/1.1/comment-check', $akismetAPIKey);
    }

    /**
     * The getSpamScore() method returns 3 values depending on the API call response:
     *
     *  2: if the comment is a "blatant spam";
     *  1: if the comment might be spam;
     *  0: if the comment is not spam (ham).
     *
     *  Use the special akismet-guaranteed-spam@example.com email address to force the
     *  result of the call to be spam.
     */

    /**
     * @return int Spam score: 0: not spam, 1: maybe spam, 2: blatant spam
     *
     * @throws RuntimeException if the call did not work
     * @throws TransportExceptionInterface
     */
    public function getSpamScore(Comment $comment, array $context): int
    {
        $response = $this->client->request('POST', $this->endpoint, [
            'body' => array_merge($context, [
                'blog' => 'https://guestbook.example.com',
                'comment_type' => 'comment',
                'comment_author' => $comment->getAuthor(),
                'comment_author_email' => $comment->getEmail(),
                'comment_content' => $comment->getText(),
                'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
                'blog_lang' => 'es',
                'blog_charset' => 'UTF-8',
                'is_test' => true,
            ]),
        ]);

        $headers = $response->getHeaders();
        if ('discard' === ($headers['x-akismet-pro-tip'][0] ?? '')) {
            return 2;
        }

        $content = $response->getContent();
        if (isset($headers['x-akismet-debug-help'][0])) {
            throw new RuntimeException(sprintf('Unable to check for spam: %s (%s).', $content, $headers['x-akismet-debug-help'][0]));
        }

        return 'true' === $content ? 1 : 0;
    }
}
