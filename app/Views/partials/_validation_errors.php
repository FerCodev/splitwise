<?php

$validationErrors = session()->getFlashdata('errors');

if ($validationErrors) {
    echo view('partials/_alert', [
        'type' => 'error',
        'title' => 'Revis&aacute; los datos ingresados',
        'messages' => array_values((array) $validationErrors),
    ]);
}
