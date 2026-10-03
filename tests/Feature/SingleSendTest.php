<?php

namespace Kfn\Tests\Feature;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Kfn\Base\Enums\ResponseCode;
use Kfn\Base\Exceptions\KfnException;
use Kfn\Tests\TestCase;
use Kfn\UI\Response;

/**
 * Kontrak single-send: respons Kfn tidak boleh dikirim langsung di dalam
 * toResponse()/renderException — pengiriman adalah tugas kernel, sekali saja.
 * Double-send menimbulkan "headers already sent" di log production.
 */
class SingleSendTest extends TestCase
{
    public function test_ui_response_redirect_is_returned_without_sending(): void
    {
        $request = Request::create('/_kfn_source', 'GET');

        $response = (new Response)
            ->to('/tujuan')
            ->toResponse($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/tujuan', $response->headers->get('Location'));
        $this->assertFalse(headers_sent());
    }

    public function test_exception_redirect_branch_returns_redirect_instead_of_aborting(): void
    {
        config()->set('koffinate.ui.exception.handling_method', 'redirect');

        $request = Request::create('/_kfn_source', 'GET', [], [], [], ['HTTP_REFERER' => 'http://localhost/kembali']);
        $request->headers->set('Accept', 'text/html');
        $request->setLaravelSession(app('session')->driver('array'));
        app()->instance('request', $request);

        $response = (new KfnException(ResponseCode::ERR_BAD_REQUEST, 'rusak'))
            ->toResponse($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/kembali', $response->headers->get('Location'));
        $this->assertFalse(headers_sent());
    }

    public function test_render_exception_does_not_echo_output(): void
    {
        config()->set('koffinate.ui.exception.handling_method', 'abort');

        $request = Request::create('/_kfn_source', 'GET');
        $request->headers->set('Accept', 'application/json');

        ob_start();
        try {
            $response = KfnException::renderException($request, new KfnException(ResponseCode::ERR_BAD_REQUEST, 'rusak'));
        }
        finally {
            $output = (string) ob_get_clean();
        }

        $this->assertSame('', $output);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertFalse(headers_sent());
    }
}
