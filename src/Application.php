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

        $textures = (string)$response->getBody();
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
                // TODO: adjust time limits
            ]);
        }

        return $this->guzzle;
    }

}
