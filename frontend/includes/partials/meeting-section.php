<?php
// Butuh: $venues, $hotel. Opsional: $sectionClass.
$cap = static fn ($v) => $v !== null ? t('meetings.pax', ['n' => (int) $v]) : '—';
$lead = $venues[0];
// Kolom yang kosong di semua ruang tidak ditampilkan; sel kosong di kolom yang terisi tetap "—".
$capCols = [
    'size_m2'      => [t('meetings.size'), static fn ($v) => $v['size_m2'] ? size_text($v['size_m2']) : '—'],
    'cap_theatre'  => [t('meetings.theatre'), static fn ($v) => $cap($v['cap_theatre'])],
    'cap_classroom' => [t('meetings.classroom'), static fn ($v) => $cap($v['cap_classroom'])],
    'cap_ushape'   => [t('meetings.ushape'), static fn ($v) => $cap($v['cap_ushape'])],
    'cap_banquet'  => [t('meetings.banquet'), static fn ($v) => $cap($v['cap_banquet'])],
];
$capCols = array_filter($capCols, static function ($def, $key) use ($venues) {
    foreach ($venues as $v) {
        if (!empty($v[$key])) {
            return true;
        }
    }
    return false;
}, ARRAY_FILTER_USE_BOTH);
?>
<?php $secClass = $sectionClass ?? 'section--sand'; unset($sectionClass); ?>
<section class="section<?= $secClass !== '' ? ' ' . e($secClass) : '' ?>" id="meeting" aria-labelledby="h-meet">
  <div class="container">
    <div class="split split--top meet-split">
      <div class="media media--wide parallax" data-parallax="0.06"><?= img($lead['image'], tr($lead, 'name'), '(min-width: 1000px) 50vw, 100vw') ?></div>
      <div class="meet-copy">
        <h2 id="h-meet"><?= e(t('hotel.meetings')) ?></h2>
        <p class="lead"><?= e(tr($lead, 'description')) ?></p>
        <p><?= e(t('meetings.sub')) ?></p>
        <?php if (has_contact($hotel)): ?><div class="btn-row"><?= wa_button($hotel, 'meeting', '', 'btn btn-primary', t('wa.ask_meeting')) ?></div><?php endif; ?>
      </div>
    </div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="<?= e(t('meetings.capacity')) ?>">
      <table class="cap-table" style="--cols: <?= count($capCols) + 1 ?>">
        <caption><?= e(t('meetings.capacity')) ?></caption>
        <thead><tr><th scope="col"><?= e(t('meetings.venue')) ?></th><?php foreach ($capCols as [$label]): ?><th scope="col"><?= e($label) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach ($venues as $v): ?>
            <tr><th scope="row"><?= e(tr($v, 'name')) ?></th><?php foreach ($capCols as [, $cell]): ?><td><?= e($cell($v)) ?></td><?php endforeach; ?></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (!empty($meetCombinedNote)): ?><p class="footnote"><?= e($meetCombinedNote) ?></p><?php endif; ?>
  </div>
</section>
