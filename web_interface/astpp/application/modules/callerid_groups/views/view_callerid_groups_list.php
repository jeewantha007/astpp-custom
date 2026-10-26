<? extend('master.php') ?>
<? startblock('extra_head') ?>

<script type="text/javascript" language="javascript">
    $(document).ready(function() {
        // Initialize the native ASTPP Flexigrid
        build_grid("callerid_groups_grid", "<?= base_url() ?>callerid_groups/callerid_groups_list_json", <? echo $grid_fields; ?>, <? echo $grid_buttons; ?>);

        // Header checkall checkbox
        $('.checkall').click(function () {
            $('.chkRefNos').prop('checked', $(this).prop('checked'));
        });

        // Search button trigger
        $("#callerid_groups_search_btn").click(function(){
            post_request_for_search("callerid_groups_grid", "<?= base_url() ?>callerid_groups/callerid_groups_list_search", "callerid_groups_search");
        });

        // Reset search filter trigger
        $("#id_reset").click(function(){
            clear_search_request("callerid_groups_grid", "<?= base_url() ?>callerid_groups/callerid_groups_list_clearsearchfilter");
            $('#group_name').val('');
            $('#status').val('');
            $('.selectpicker').selectpicker('refresh');
        });
    });

    // Prototype placeholder alert for future screens
    function callerid_placeholder(screen_name) {
        alert("UI Prototype Note:\n\n'" + screen_name + "' will be implemented in a subsequent phase.\n\nBackend logic and database modifications are intentionally disabled in this Screen 1 prototype.");
    }
</script>

<? endblock() ?>

<? startblock('page-title') ?>
    <?= $page_title ?>
<? endblock() ?>

<? startblock('content') ?>
<!-- Search Panel (collapsible via header search icon) -->
<section class="slice color-three">
	<div class="w-section inverse p-0">
		<div class="col-12">
			<div class="portlet-content mb-4" id="search_bar" style="display: none">
                <?php echo $form_search; ?>
            </div>
		</div>
	</div>
</section>

<!-- Data Grid Panel -->
<section class="slice color-three pb-4">
	<div class="w-section inverse p-0">
		<div class="card col-md-12 pb-4">
			<form method="POST" action="" id="ListForm">
				<table id="callerid_groups_grid" align="left" style="display: none;"></table>
			</form>
		</div>
	</div>
</section>

<? endblock() ?>
<? end_extend() ?>
