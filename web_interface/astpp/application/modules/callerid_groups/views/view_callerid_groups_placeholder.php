<?php
include (FCPATH . 'application/views/popup_header.php');
?>
<section class="slice m-0">
	<div class="w-section inverse p-0">
		<div class="col-md-12 p-0 card-header">
			<h3 class="fw4 p-4 m-0"><?= $page_title ?></h3>
		</div>
	</div>
</section>
<div>
	<section class="slice m-0">
		<div class="w-section inverse p-4">
			<div class="alert alert-info">
				<i class="fa fa-info-circle fa-lg fa-fw"></i>
				<strong>UI Prototype Notice:</strong><br><br>
				The <strong>Create Caller ID Group</strong> dialog will be fully implemented in <strong>Screen 2</strong>.<br>
				In accordance with Screen 1 task scope, backend mutations and database saves are intentionally disabled.
			</div>
			<div class="mt-4" style="text-align: right;">
				<button type="button" class="btn btn-secondary" onclick="jQuery(document).trigger('close.facebox');">
					<?= gettext('Close') ?>
				</button>
			</div>
		</div>
	</section>
</div>
