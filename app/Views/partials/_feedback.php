<?php

$fbSuccess = session()->getFlashdata('success');
$fbError = session()->getFlashdata('error');

if ($fbSuccess) {
    echo '<div class="alert alert-success">' . esc($fbSuccess) . '</div>';
    return;
}

if ($fbError) {
    echo '<div class="alert alert-danger">' . esc($fbError) . '</div>';
    return;
}
