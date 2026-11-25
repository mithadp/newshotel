<?php
// Paragraf pengantar hotel: kalimat pertama terlihat, sisanya di balik "Baca selengkapnya". Butuh: $hotel.
[$leadFirst, $leadRest] = split_lead(tr($hotel, 'description'));
?>
<p class="lead"><?= e($leadFirst) ?></p>
<?php if ($leadRest !== ''): ?>
  <details class="more">
    <summary><?= e(t('common.read_more')) ?><?= icon('chevron-down') ?></summary>
    <p class="lead"><?= e($leadRest) ?></p>
  </details>
<?php endif; ?>
