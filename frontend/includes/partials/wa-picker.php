<?php
// Tombol "Chat WhatsApp" yang membuka daftar 3 hotel (halaman grup). Opsional: $pickId, $pickClass, $pickBtnClass.
$pickId       = $pickId ?? 'pick';
$pickClass    = $pickClass ?? '';
$pickBtnClass = $pickBtnClass ?? 'btn btn-primary';
// Label tidak menyebut WhatsApp selama belum ada nomor yang terisi.
$hasWa = any_whatsapp();
?>
<details class="wa-pick <?= e($pickClass) ?>" data-wa-pick>
  <summary class="<?= e($pickBtnClass) ?>"><?= icon($hasWa ? 'whatsapp' : 'phone') ?><span><?= e($hasWa ? t('wa.chat') : t('wa.contact')) ?></span></summary>
  <div class="wa-pick-pop" id="<?= e($pickId) ?>">
    <p class="wa-pick-title"><?= e(t('wa.pick_title')) ?></p>
    <ul class="wa-list">
      <?php foreach (hotels_by_contact() as $h): $a = contact_action($h); ?>
        <li data-brand="<?= e($h['brand']) ?>"><?php include FRONT . '/includes/partials/wa-row.php'; ?></li>
      <?php endforeach; ?>
    </ul>
    <?php if (count(array_filter(hotels(), 'has_contact')) < count(hotels())): ?><p class="wa-pick-note"><?= e(t('wa.soon_note')) ?></p><?php endif; ?>
  </div>
</details>
