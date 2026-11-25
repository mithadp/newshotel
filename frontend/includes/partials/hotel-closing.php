<?php
// Bagian penutup halaman hotel. Butuh: $hotel. Opsional: $hotelPosts.
$hotelPosts = $hotelPosts ?? [];
?>
<?php if (tr($hotel, 'policies') !== ''): ?>
<section class="section section--compact" aria-labelledby="h-pol">
  <div class="container container--narrow">
    <div class="accordion">
      <details>
        <summary id="h-pol"><?= e(t('hotel.policies')) ?><?= icon('chevron-down') ?></summary>
        <div class="prose"><?= markdown(tr($hotel, 'policies')) ?></div>
      </details>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($hotelPosts): ?>
<section class="section section--sand" aria-labelledby="h-hnews">
  <div class="container">
    <header class="section-head section-head--row">
      <h2 id="h-hnews"><?= e(t('hotel.news')) ?></h2>
      <a class="link-arrow" href="<?= e(url('blog', ['hotel' => $hotel['slug']])) ?>"><?= e(t('common.see_all')) ?><?= icon('arrow-right') ?></a>
    </header>
    <div class="post-grid post-grid--<?= min(3, count($hotelPosts)) ?>">
      <?php foreach ($hotelPosts as $p): ?><?php include FRONT . '/includes/partials/post-card.php'; ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section--dark" aria-labelledby="h-others">
  <div class="container">
    <header class="section-head"><h2 id="h-others"><?= e(t('hotel.others')) ?></h2></header>
    <div class="hotel-cards hotel-cards--two">
      <?php foreach (other_hotels($hotel) as $h): ?><?php include FRONT . '/includes/partials/hotel-card.php'; ?><?php endforeach; ?>
    </div>
  </div>
</section>
