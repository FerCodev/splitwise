<?php
$action = $action ?? '';
$formId = $formId ?? '';
$label = $label ?? 'Eliminar';
$buttonClass = $buttonClass ?? 'btn btn-danger';
$icon = $icon ?? '';
$confirmTitle = $confirmTitle ?? 'Confirmar';
$confirmMsg = $confirmMsg ?? '¿Estás seguro?';
$confirmBtn = $confirmBtn ?? 'Eliminar';
$confirmClass = $confirmClass ?? 'btn-danger';
$extraAttrs = $extraAttrs ?? '';
$hiddenFields = is_array($hiddenFields ?? null) ? $hiddenFields : [];
?>
<form action="<?= esc($action, 'attr') ?>" method="post" id="<?= esc($formId, 'attr') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="DELETE">
    <?php foreach ($hiddenFields as $fieldName => $fieldValue): ?>
        <input type="hidden" name="<?= esc((string) $fieldName, 'attr') ?>" value="<?= esc((string) $fieldValue, 'attr') ?>">
    <?php endforeach; ?>
    <button type="button" class="<?= esc($buttonClass) ?>"
        data-bs-toggle="modal" data-bs-target="#confirmModal"
        data-confirm-title="<?= esc($confirmTitle, 'attr') ?>"
        data-confirm-msg="<?= esc($confirmMsg, 'attr') ?>"
        data-confirm-btn="<?= esc($confirmBtn, 'attr') ?>"
        data-confirm-class="<?= esc($confirmClass, 'attr') ?>"
        data-confirm-form="<?= esc($formId, 'attr') ?>"
        <?= $extraAttrs ?>>
        <?php if ($icon): ?><?= $icon ?> <?php endif; ?><?= esc($label) ?>
    </button>
</form>
