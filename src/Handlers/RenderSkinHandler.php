<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer\Handlers;

use Ely\SkinsRenderer\Exceptions\InvalidRequestException;
use Ely\SkinsRenderer\Validators\UrlValidator;
use ErickSkrauch\SkinRenderer2D\Renderer as SkinsRenderer;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as HTTPClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Throwable;

final class RenderSkinHandler implements HandlerInterface {

    private const MAX_RESPONSE_SIZE = 16000;

    // TODO: make it configurable
    private const ALLOWED_PATHS = [
        'ely.by',
        'dev.ely.by',
        'ely.by.local',
        'upgrade.ely.by.local',
        'skinsystem.ely.by/skins',
    ];

    private HTTPClientInterface $client;

    public function __construct(HTTPClientInterface $client) {
        $this->client = $client;
    }

    public static function create(): self {
        return new static(new GuzzleClient([
            'connect_timeout' => 5,
            'decode_content' => false,
            'read_timeout' => 5,
            'stream' => true,
            'timeout' => 10,
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws InvalidRequestException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $params = $request->getQueryParams();
        /** @var string|null $url */
        $url = $params['url'] ?? null;
        $scale = (float)($params['scale'] ?? 3);
        $isSlim = (bool)($params['slim'] ?? false);
        $renderFace = (bool)($params['renderFace'] ?? false);

        if ($url === null) {
            throw new InvalidRequestException('Required query params not provided: url');
        }

        $urlValidator = new UrlValidator(self::ALLOWED_PATHS);
        if (!$urlValidator->validate($url)) {
            return new Response(403);
        }

        try {
            $response = $this->client->request('GET', $url);
        } catch (RequestException $e) {
            if ($e->getResponse() !== null && $e->getResponse()->getStatusCode() === 404) {
                return new Response(404, [], 'Provided url doesn\'t contains skin.');
            }

            throw $e;
        }

        $body = $response->getBody();
        $textures = '';
        $partSize = 1024;
        while ($part = $body->read($partSize)) {
            $textures .= $part;
            if (mb_strlen($textures, '8bit') > self::MAX_RESPONSE_SIZE) {
                throw new InvalidRequestException('Provided url responds with too big response');
            }

            if (mb_strlen($part, '8bit') < $partSize) {
                break;
            }
        }

        $sizes = @getimagesizefromstring($textures);
        if ($sizes === false || $sizes[2] !== IMAGETYPE_PNG) {
            throw new InvalidRequestException('Provided url responds with not png file');
        }

        try {
            $image = @imagecreatefromstring($textures);
            $renderer = new SkinsRenderer($image);
            if ($renderFace) {
                $result = $renderer->renderFace($scale);
            } else {
                // Prevent autodetect slim arms
                $renderer->setIsSlim($isSlim);
                // Due to overridden implementation check 1.8 format manually
                if ($isSlim && !$renderer->is1_8()) {
                    throw new InvalidRequestException('Cannot render skin with slim arms for non 1.8 skin format');
                }

                $result = $renderer->renderCombined($scale);
            }
        } catch (InvalidRequestException $e) {
            // Just let this expression throw
            throw $e;
        } catch (Throwable $e) {
            throw new InvalidRequestException('Unable to render provided skin url', 0, $e);
        }

        ob_start();
        imagepng($result);
        $contents = ob_get_clean();

        return new Response(200, ['Content-Type' => 'image/png'], $contents);
    }

}
