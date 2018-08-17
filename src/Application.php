<?php
declare(strict_types=1);

namespace Ely\SkinsRenderer;

use ErickSkrauch\SkinRenderer2D\Renderer as SkinsRenderer;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;

class Application {

    private $guzzle;

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $path = $request->getUri()->getPath();
        if ($path === '/sleep') {
            sleep(10);
        }

        if ($path !== '/') {
            return new Response(404);
        }

        $getParams = $request->getQueryParams();
        $url = $getParams['url'] ?? null;
        $scale = $getParams['scale'] ?? 3;
        $renderFace = $getParams['renderFace'] ?? false;

        if ($url === null) {
            return new Response(400);
        }

        try {
            $response = $this->getClient()->request('GET', $url);
        } catch (ClientException $e) {
            return new Response($e->getResponse()->getStatusCode(), [], $e->getResponse()->getBody()->getContents());
        } catch (\Exception $e) {
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
            $this->guzzle = new \GuzzleHttp\Client();
        }

        return $this->guzzle;
    }

}
