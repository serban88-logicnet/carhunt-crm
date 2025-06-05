<?php require APPROOT.'/views/inc/header.php'; ?>
<div class="row">
	<div class="col-md-12 mx-auto">
		<div data-id="<?= $data['item']->id; ?>" class="card card-body bg-white shadow-sm mt-0 js-single-details js-single-<?= $data['what']; ?>">
			<?php flash('notices'); ?>
			<div class="row mb-3">
				<div class="col-md-6">
				    <h3><?= $data['title']; ?></h3>
				    <?php if (!empty($data['hasHistory']) && !empty($data['lastEdit'])): ?>
				        <div class="mb-2 small text-muted">
				            <span><strong>Modificat la:</strong> <?= date("d.m.Y H:i", strtotime($data['lastEdit']->edited_at)) ?></span>
				            |
				            <span><strong>Modificat de:</strong> <?= htmlspecialchars($data['lastEdit']->edited_by_user) ?></span>
				        </div>
				    <?php endif; ?>
				</div>

				<div class="col-md-6 text-end">
					<?php if (isset($data['content']['buttons'])): ?>
					    <?php foreach ($data['content']['buttons'] as $button): ?>
					        <?php 
					            $target = isset($button['target']) ? $button['target'] : '_self';
					            $downloadAttr = '';

					            if (isset($button['download'])) {
					                if (is_string($button['download'])) {
					                    $downloadAttr = 'download="' . htmlspecialchars($button['download']) . '"';
					                } elseif ($button['download'] === true) {
					                    $downloadAttr = 'download';
					                }
					            }

					            // Prepare any custom attributes if present
					            $customAttrs = '';
					            if (isset($button['attrs']) && is_array($button['attrs'])) {
					                foreach ($button['attrs'] as $attrName => $attrValue) {
					                    // Properly escape attribute names and values
					                    $attrNameEsc = htmlspecialchars($attrName, ENT_QUOTES, 'UTF-8');
					                    $attrValueEsc = htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8');
					                    $customAttrs .= " $attrNameEsc=\"$attrValueEsc\"";
					                }
					            }
					        ?>
					        <a href="<?= URLROOT . '/' . $button['link'] ?>" 
					           target="<?= $target ?>" 
					           class="btn <?= $button['class'] ?>" 
					           <?= $downloadAttr ?>
					           <?= $customAttrs ?>
					        >
					           <?= $button['name'] ?>
					        </a>
					    <?php endforeach; ?>
					<?php endif; ?>


				</div>
			</div>
			<?php createDetailsTable($data['fields'], $data['what']); ?>
		</div>  
	</div>
</div>
<?php //dd($data); ?>
<?php 
	if($data['what'] == "inchirieri") {
		require APPROOT.'/views/pages/modals/incasarePlataModal.php';
	}
?>

<?php require APPROOT . '/views/pages/modals/schimbariReturModal.php'; ?>
<?php require APPROOT.'/views/general/additions/connections.php'; ?>
<?php require APPROOT.'/views/general/additions/comments.php'; ?>
<?php require APPROOT.'/views/inc/footer.php'; ?>