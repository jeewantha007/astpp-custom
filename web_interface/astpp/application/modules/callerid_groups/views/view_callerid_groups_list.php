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
        // Ensure Facebox binds to dynamically loaded grid rows
        $(document).ajaxComplete(function() {
            $('a[rel*=facebox]').facebox();
        });
    });

    /**
     * Interactive status toggle handler
     */
    function callerid_toggle_status(group_id) {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>callerid_groups/callerid_groups_status_toggle/' + group_id,
            dataType: 'json',
            success: function(response) {
                $("#toast-container").css("display", "block");
                $(".toast-message").html(response.message || "<?= gettext('Caller ID group status updated.') ?>");
                $('.toast-top-right').delay(3000).fadeOut();

                var switchEl = $('#switch_' + group_id);
                var parentLabel = switchEl.closest('label');
                if (response.status == 0) {
                    switchEl.prop('checked', true);
                    parentLabel.attr('title', '<?= gettext("Active - Click to Deactivate") ?>');
                } else {
                    switchEl.prop('checked', false);
                    parentLabel.attr('title', '<?= gettext("Inactive - Click to Activate") ?>');
                }
            },
            error: function() {
                alert("<?= gettext('Error updating status. Please try again.') ?>");
            }
        });
    }

    /**
     * Single group delete handler
     */
    function callerid_delete_single(group_id, group_name) {
        if (confirm("<?= gettext('Are you sure you want to delete Caller ID group') ?> '" + group_name + "'?")) {
            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>callerid_groups/callerid_groups_delete_single/' + group_id,
                dataType: 'json',
                success: function(response) {
                    $('.flex_grid').flexReload();
                    $("#toast-container_error").css("display", "block");
                    $(".toast-message").html(response.SUCCESS || "<?= gettext('Caller ID group deleted successfully.') ?>");
                    $('.toast-top-right').delay(4000).fadeOut();
                },
                error: function() {
                    alert("<?= gettext('Error deleting group. Please try again.') ?>");
                }
            });
        }
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
