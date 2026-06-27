<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Sirve el manifest de la PWA con el Content-Type correcto.
 *
 * El manifest no se sirve como archivo estatico en public/ porque
 * Apache lo entregaria con text/plain (MIME type desconocido), y
 * Chrome rechaza manifests que no vienen con application/manifest+json.
 *
 * Esta ruta es publica (sin filtro de auth) porque el manifest es
 * parte de la configuracion de la PWA, no de la aplicacion.
 */
class PwaManifest extends Controller
{
    public function index()
    {
        $path = APPPATH . 'Config/pwa-manifest.json';
        if (! is_file($path) || ! is_readable($path)) {
            return $this->response->setStatusCode(500);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return $this->response->setStatusCode(500);
        }

        return $this->response
            ->setContentType('application/manifest+json')
            ->setCharset('utf-8')
            ->setBody($content);
    }
}
