<?php

$fbSuccess = session()->getFlashdata('success');
$fbError = session()->getFlashdata('error');
$fbWarning = session()->getFlashdata('warning');
$fbInfo = session()->getFlashdata('info');

if ($fbSuccess) {
    echo view('partials/_alert', ['type' => 'success', 'message' => $fbSuccess]);
}

if ($fbError) {
    echo view('partials/_alert', ['type' => 'error', 'message' => $fbError]);
}

if ($fbWarning) {
    echo view('partials/_alert', ['type' => 'warning', 'message' => $fbWarning]);
}

if ($fbInfo) {
    echo view('partials/_alert', ['type' => 'info', 'message' => $fbInfo]);
}
