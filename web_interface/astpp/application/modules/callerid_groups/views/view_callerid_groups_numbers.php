<?php include(FCPATH . 'application/views/popup_header.php'); ?>

<style type="text/css">
    /* Comprehensive horizontal scrollbar prevention */
    #facebox,
    #facebox .popup,
    #facebox .content,
    .pop_md,
    #facebox .card-one,
    #facebox .card,
    #facebox ul,
    #floating-label {
        overflow-x: hidden !important;
        max-width: 100% !important;
    }
    .animap-assign-row:hover {
        background-color: #f1f5f9;
    }
</style>

<section class="slice m-0">
    <div class="w-section inverse p-0">
        <div class="col-md-12 p-0 card-header">
            <h3 class="fw4 p-4 m-0" style="color:#3b3280; font-weight:700;">
                <i class="fa fa-phone fa-fw mr-2"></i>
                <?= gettext('Manage Numbers') ?>: <?= htmlspecialchars($group['name']) ?>
            </h3>
        </div>
    </div>
</section>

<div class="p-4">
    <!-- Top Action and Context Description -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <p class="text-muted small m-0" style="max-width: 75%;">
            <?= gettext('Caller IDs are centrally managed via the system Caller IDs inventory (ani_map). Select existing numbers below to assign them to this group, or create a brand new Caller ID.') ?>
        </p>
        <a href="<?= base_url() ?>animap/animap_add/" rel="facebox" class="btn btn-outline-primary btn-sm my-1">
            <i class="fa fa-plus-circle mr-1"></i> <?= gettext('+ New Caller ID') ?>
        </a>
    </div>

    <!-- Live Search filter bar -->
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
        </div>
        <input type="text" id="animap_search_filter" class="form-control" placeholder="<?= gettext('Search by phone number, customer name, or account #...') ?>">
    </div>

    <!-- Caller ID Numbers Form Table -->
    <form id="assign_numbers_form" onsubmit="return assign_numbers_submit(event);">
        <input type="hidden" name="group_id" value="<?= $group['id'] ?>">

        <div class="table-responsive border mb-3" style="max-height: 320px; overflow-y: auto;">
            <table class="table table-sm table-striped m-0" id="assign_numbers_table">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px; text-align:center;">
                            <input type="checkbox" id="checkall_nums" class="ace">
                            <label class="lbl" for="checkall_nums"></label>
                        </th>
                        <th><?= gettext('Caller ID Number') ?></th>
                        <th><?= gettext('Owning Account / Customer') ?></th>
                        <th style="width: 140px;"><?= gettext('Current Group') ?></th>
                        <th style="width: 90px; text-align:center;"><?= gettext('Status') ?></th>
                    </tr>
                </thead>
                <tbody id="assign_numbers_tbody">
                    <?php if (!empty($available_numbers)): ?>
                        <?php foreach ($available_numbers as $num): ?>
                        <tr class="animap-assign-row" data-search="<?= strtolower(htmlspecialchars($num['number'] . ' ' . $num['account_name'] . ' ' . $num['account_number'])) ?>">
                            <td style="text-align:center;">
                                <input type="checkbox" name="animap_ids[]" value="<?= $num['id'] ?>" id="num_chk_<?= $num['id'] ?>" class="ace num-chk" <?= ($num['assigned_group_id'] == $group['id']) ? 'checked' : '' ?>>
                                <label class="lbl" for="num_chk_<?= $num['id'] ?>"></label>
                            </td>
                            <td>
                                <strong style="color:#3b3280;"><i class="fa fa-phone fa-fw mr-1 text-muted"></i><?= htmlspecialchars($num['number']) ?></strong>
                            </td>
                            <td>
                                <span><?= htmlspecialchars($num['account_name']) ?></span>
                                <span class="text-muted small ml-1">(#<?= htmlspecialchars($num['account_number']) ?>)</span>
                            </td>
                            <td>
                                <?php if ($num['assigned_group_id'] == $group['id']): ?>
                                    <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i><?= gettext('This Group') ?></span>
                                <?php elseif ($num['assigned_group_id'] > 0): ?>
                                    <span class="badge badge-light border text-muted px-2 py-1"><?= htmlspecialchars($num['assigned_group_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary px-2 py-1"><?= gettext('None') ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge badge-<?= ($num['status'] == 0 ? 'success' : 'secondary') ?> px-2 py-1">
                                    <?= ($num['status'] == 0 ? gettext('Active') : gettext('Inactive')) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa fa-phone fa-2x mb-2 d-block"></i>
                                <?= gettext('No Caller IDs registered in the system yet. Click "+ New Caller ID" above to add numbers.') ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Buttons Row -->
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <span class="text-muted small my-1">
                <span id="selected_num_count">0</span> <?= gettext('Caller ID(s) selected for this group') ?>
            </span>
            <div class="my-1">
                <button type="submit" id="btn_save_assign_numbers" class="btn btn-success">
                    <i class="fa fa-save mr-1"></i> <?= gettext('Save Assignments') ?>
                </button>
                <button type="button" class="btn btn-secondary mx-2" onclick="$(document).trigger('close.facebox');">
                    <?= gettext('Close') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
$(document).ready(function() {
    update_selected_num_count();

    // Select all numbers checkbox
    $('#checkall_nums').click(function() {
        var checked = $(this).prop('checked');
        $('.animap-assign-row:visible .num-chk').prop('checked', checked);
        update_selected_num_count();
    });

    // Individual checkbox change
    $('.num-chk').change(function() {
        update_selected_num_count();
    });

    // Live search filter
    $('#animap_search_filter').on('keyup', function() {
        var term = $(this).val().toLowerCase().trim();
        $('.animap-assign-row').each(function() {
            var searchData = $(this).attr('data-search') || '';
            if (term == '' || searchData.indexOf(term) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});

function update_selected_num_count() {
    var count = $('.num-chk:checked').length;
    $('#selected_num_count').text(count);
}

function assign_numbers_submit(e) {
    e.preventDefault();
    var formData = $('#assign_numbers_form').serialize();

    $.ajax({
        type: 'POST',
        url: '<?= base_url() ?>callerid_groups/callerid_groups_save_assigned_numbers/',
        data: formData,
        success: function(response) {
            var res = JSON.parse(response);
            if (res.SUCCESS) {
                $("#toast-container").css("display", "block");
                $(".toast-message").html(res.SUCCESS);
                $('.toast-top-right').delay(4000).fadeOut();
                $(document).trigger('close.facebox');

                // Reload Flexigrid to update numbers count badge
                if ($('.flex_grid').length > 0) {
                    $('.flex_grid').flexReload();
                }

                // If in detail view, reload page to update table
                if (window.location.href.indexOf('callerid_groups_detail') !== -1) {
                    location.reload();
                }
            }
        }
    });
    return false;
}
</script>
