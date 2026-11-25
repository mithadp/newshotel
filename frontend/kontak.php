<?php
require_once __DIR__ . '/includes/init.php';

$page = [
    'title'       => t('contact.title'),
    'description' => t('meta.contact_description'),
];

require FRONT . '/includes/header.php';
$heading = t('contact.title');
$sub     = tw('contact.sub');
$crumbs  = [[t('nav.home'), url()], [t('nav.contact'), null]];
include FRONT . '/includes/partials/page-hero.php';
?>
<div class="page-body">
  <div class="container">
    <div class="contact-cards">
      <?php foreach (hotels() as $h): ?>
        <section class="panel contact-card" data-brand="<?= e($h['brand']) ?>" aria-labelledby="ct-<?= e($h['slug']) ?>">
          <h2 id="ct-<?= e($h['slug']) ?>" class="box-title"><a href="<?= e(url($h['slug'])) ?>"><?= e($h['name']) ?></a></h2>
          <address class="muted-address"><?= e(hotel_address($h)) ?></address>
          <?php $viaDir = contact_action($h)['kind'] === 'dir'; ?>
          <?= wa_button($h, 'stay', '', 'btn btn-primary btn-block', t('wa.chat')) ?>
          <?php if ($viaDir): ?><p class="tba contact-soon"><?= e(t('hotel.contact_soon')) ?></p><?php endif; ?>
          <ul class="contact-list">
            <?php if ($h['phone']): ?><li><?= icon('phone') ?><a href="<?= e(tel_link($h['phone'])) ?>"><?= e($h['phone']) ?></a></li><?php endif; ?>
            <?php if ($h['email']): ?><li><?= icon('mail') ?><a href="mailto:<?= e($h['email']) ?>"><?= e($h['email']) ?></a></li><?php endif; ?>
            <?php if ($h['instagram']): ?><li><?= icon('instagram') ?><a href="https://www.instagram.com/<?= e($h['instagram']) ?>/" target="_blank" rel="noopener">@<?= e($h['instagram']) ?></a></li><?php endif; ?>
            <?php if (!$viaDir): ?><li><?= icon('map-pin') ?><a href="<?= e(directions_url($h)) ?>" target="_blank" rel="noopener"><?= e(t('hotel.directions')) ?></a></li><?php endif; ?>
          </ul>
        </section>
      <?php endforeach; ?>
    </div>
    <?php if (array_filter(hotels(), 'wa_number')): ?><p class="page-note"><?= icon('whatsapp') ?><span><?= e(t('wa.how')) ?></span></p><?php endif; ?>
  </div>
</div>
<?php require FRONT . '/includes/footer.php'; ?>
