<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SwaggerController extends Controller
{
    /**
     * Serve the interactive Swagger UI page.
     */
    public function index()
    {
        return response()->view('swagger', [
            'specUrl' => route('swagger.spec'),
        ]);
    }

    /**
     * Serve the OpenAPI YAML specification.
     */
    public function spec(): Response
    {
        $path = base_path('docs/openapi.yaml');

        abort_unless(file_exists($path), 404, 'OpenAPI specification not found.');

        return response()->file($path, [
            'Content-Type' => 'application/yaml',
        ]);
    }
}

