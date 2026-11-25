<?php
$cur = $page['hotel'] ?? null;
?>
</main>

<footer class="site-footer">
  <?php $footCta = !$cur || has_contact($cur); if ($footCta): ?>
  <div class="container footer-cta">
    <p class="footer-cta-text"><?= e(($cur ? wa_number($cur) : any_whatsapp()) ? t('footer.cta') : t('footer.cta_call')) ?></p>
    <?php if ($cur): ?><?= wa_button($cur, 'stay', '', 'btn btn-primary btn-lg', t('wa.chat')) ?>
    <?php else: $pickId = 'pick-footer'; $pickClass = 'wa-pick--footer'; $pickBtnClass = 'btn btn-primary btn-lg'; include FRONT . '/includes/partials/wa-picker.php'; endif; ?>
  </div>
  <?php endif; ?>
  <div class="container footer-top">
    <div class="footer-brand">
      <a class="footer-logo-link" href="<?= e(url()) ?>" aria-label="<?= e(setting('site_name') . ' — ' . t('nav.home')) ?>"><?= img('brand/news-hotel-white', '', '140px', ['class' => 'footer-logo']) ?></a>
      <p class="footer-tagline"><?= e(setting('tagline_' . LANG, '')) ?></p>
      <a class="endorse" href="<?= e(url('tentang')) ?>">
        <?= img('brand/graha-pena', 'Graha Pena Jawa Pos', '48px', ['class' => 'endorse-logo']) ?>
        <span><?= e(t('footer.part_of')) ?></span>
      </a>
    </div>

    <div class="footer-hotels">
      <?php foreach (hotels() as $fh): $wa = wa_link(wa_number($fh), wa_text($fh)); ?>
        <section class="footer-hotel" aria-labelledby="fh-<?= e($fh['slug']) ?>">
          <h2 id="fh-<?= e($fh['slug']) ?>"><a href="<?= e(url($fh['slug'])) ?>"><?= e($fh['name']) ?></a></h2>
          <address><?= e($fh['address']) ?><br><?= e($fh['city']) ?><?= $fh['province'] ? ', ' . e($fh['province']) : '' ?></address>
          <ul class="footer-contact">
            <?php if ($wa): ?><li><a href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?>WhatsApp</a></li><?php endif; ?>
            <?php if ($fh['phone']): ?><li><a href="<?= e(tel_link($fh['phone'])) ?>"><?= icon('phone') ?><?= e($fh['phone']) ?></a></li><?php endif; ?>
            <?php if ($fh['email']): ?><li><a href="mailto:<?= e($fh['email']) ?>"><?= icon('mail') ?><?= e($fh['email']) ?></a></li><?php endif; ?>
            <?php if ($fh['instagram']): ?><li><a href="https://www.instagram.com/<?= e($fh['instagram']) ?>/" target="_blank" rel="noopener"><?= icon('instagram') ?>@<?= e($fh['instagram']) ?></a></li><?php endif; ?>
            <?php if (!$wa && !$fh['phone'] && !$fh['email'] && !$fh['instagram']): ?><li><a href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= e(rawurlencode(maps_query($fh))) ?>" target="_blank" rel="noopener"><?= icon('pin') ?><?= e(t('hotel.directions')) ?></a></li><?php endif; ?>
          </ul>
        </section>
      <?php endforeach; ?>
    </div>

    <nav class="footer-links" aria-label="<?= e(t('footer.explore')) ?>">
      <h2><?= e(t('footer.explore')) ?></h2>
      <ul>
        <li><a href="<?= e(url('blog')) ?>"><?= e(t('nav.blog')) ?></a></li>
        <?php if (has_promo()): ?><li><a href="<?= e(url('blog', ['kategori' => 'promo'])) ?>"><?= e(t('nav.promo')) ?></a></li><?php endif; ?>
        <li><a href="<?= e(url('tentang')) ?>"><?= e(t('nav.about')) ?></a></li>
        <li><a href="<?= e(url('kontak')) ?>"><?= e(t('nav.contact')) ?></a></li>
      </ul>
    </nav>
  </div>
  <div class="container footer-bottom">
    <p>© <?= date('Y') ?> <?= e(setting('site_name')) ?> · Jawa Pos Group</p>
  </div>
</footer>

<?php if ($cur): ?>
<div class="wa-dock" data-wa-dock>
  <?php $dockName = ($cur['short_name'] === '' || strcasecmp($cur['short_name'], $cur['city']) === 0) ? $cur['name'] : $cur['short_name']; ?>
  <div class="wa-dock-text">
    <strong><?= e($dockName) ?></strong>
    <?php if (stripos($dockName, $cur['city']) === false): ?><small><?= e($cur['city']) ?></small><?php endif; ?>
  </div>
  <?= wa_button($cur, 'stay', '', 'btn btn-primary', t('wa.chat')) ?>
</div>
<?php endif; ?>

<script>window.NH = <?= json_encode(['i18n' => [
    'close' => t('a11y.close'), 'prev' => t('a11y.prev'), 'next' => t('a11y.next'), 'gallery' => t('hotel.gallery'),
]], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
