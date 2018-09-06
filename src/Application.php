<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer;

use ErickSkrauch\SkinRenderer2D\Renderer as SkinsRenderer;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;

class Application {

    private const MAX_RESPONSE_SIZE = 16000;

    /**
     * @var ClientInterface|null
     */
    private $guzzle;

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $path = $request->getUri()->getPath();
        if ($path !== '/') {
            return new Response(404);
        }

        $getParams = $request->getQueryParams();
        $url = $getParams['url'] ?? null;
        $scale = (int)($getParams['scale'] ?? 3);
        $renderFace = $getParams['renderFace'] ?? false;

        if ($url === null) {
            return new Response(400);
        }

        try {
            $response = $this->getClient()->request('GET', $url);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            return new Response($response->getStatusCode(), [], $response->getBody()->getContents());
        } catch (GuzzleException $e) {
            return new Response(500, [], $e->getMessage());
        }

        $body = $response->getBody();
        $textures = $body->read(self::MAX_RESPONSE_SIZE);
        if (!$body->eof()) {
            return new Response(400);
        }

        $sizes = @getimagesizefromstring($textures);
        if ($sizes === false || $sizes[2] !== IMAGETYPE_PNG) {
            return new Response(400);
        }

        $renderer = SkinsRenderer::assignSkinFromString($textures);
        if ($renderFace) {
            $result = $renderer->renderFace($scale);
        } else {
            $result = $renderer->renderCombined($scale);
        }

        ob_start();
        imagepng($result);
        $contents =  ob_get_contents();
        ob_end_clean();

        return new Response(200, ['Content-Type' => 'image/png'], $contents);
    }

    public function getClient(): ClientInterface {
        if ($this->guzzle === null) {
            $this->guzzle = new GuzzleClient([
                'connect_timeout' => 5,
                'decode_content' => false,
                'read_timeout' => 5,
                'stream' => true,
                'timeout' => 10,
            ]);
        }

        return $this->guzzle;
    }

}
