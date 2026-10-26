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
    .customer-assign-row:hover {
        background-color: #f1f5f9;
    }
</style>

<section class="slice m-0">
    <div class="w-section inverse p-0">
        <div class="col-md-12 p-0 card-header">
            <h3 class="fw4 p-4 m-0" style="color:#3b3280; font-weight:700;">
                <i class="fa fa-users fa-fw mr-2"></i>
                <?= gettext('Assign Customers') ?>: <?= htmlspecialchars($group['name']) ?>
            </h3>
        </div>
    </div>
</section>

<div class="p-4">
    <p class="text-muted small mb-3">
        <?= gettext('Select the customer accounts that should be assigned to this Caller ID pool. Outbound calls placed by assigned customers will enforce this group’s Caller ID numbers.') ?>
    </p>

    <!-- Search filter bar -->
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
        </div>
        <input type="text" id="customer_search_filter" class="form-control" placeholder="<?= gettext('Search by customer name, account number, or email...') ?>">
    </div>

    <!-- Customer selection list -->
    <form id="assign_customers_form" onsubmit="return assign_customers_submit(event);">
        <input type="hidden" name="group_id" value="<?= $group['id'] ?>">

        <div class="table-responsive border mb-3" style="max-height: 320px; overflow-y: auto;">
            <table class="table table-sm table-striped m-0" id="assign_customers_table">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px; text-align:center;">
                            <input type="checkbox" id="checkall_cust" class="ace">
                            <label class="lbl" for="checkall_cust"></label>
                        </th>
                        <th><?= gettext('Account #') ?></th>
                        <th><?= gettext('Customer Name / Company') ?></th>
                        <th><?= gettext('Email') ?></th>
                        <th style="width: 130px;"><?= gettext('Current Group') ?></th>
                    </tr>
                </thead>
                <tbody id="assign_customers_tbody">
                    <?php if (!empty($available_customers)): ?>
                        <?php foreach ($available_customers as $cust): ?>
                        <tr class="customer-assign-row" data-search="<?= strtolower(htmlspecialchars($cust['number'] . ' ' . $cust['name'] . ' ' . $cust['email'])) ?>">
                            <td style="text-align:center;">
                                <input type="checkbox" name="customer_ids[]" value="<?= $cust['id'] ?>" id="cust_chk_<?= $cust['id'] ?>" class="ace cust-chk" <?= ($cust['assigned_group_id'] == $group['id']) ? 'checked' : '' ?>>
                                <label class="lbl" for="cust_chk_<?= $cust['id'] ?>"></label>
                            </td>
                            <td>
                                <strong style="color:#3b3280;"><i class="fa fa-user fa-fw mr-1 text-muted"></i><?= htmlspecialchars($cust['number']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($cust['name']) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($cust['email']) ?></td>
                            <td>
                                <?php if ($cust['assigned_group_id'] == $group['id']): ?>
                                    <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i><?= gettext('This Group') ?></span>
                                <?php elseif ($cust['assigned_group_id'] > 0): ?>
                                    <span class="badge badge-light border text-muted px-2 py-1"><?= htmlspecialchars($cust['assigned_group_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary px-2 py-1"><?= gettext('None') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <?= gettext('No customer accounts found.') ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Buttons Row -->
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <span id="selected_count">0</span> <?= gettext('customer(s) selected for this group') ?>
            </span>
            <div>
                <button type="submit" id="btn_save_assign" class="btn btn-success">
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
    update_selected_count();

    // Select all checkbox
    $('#checkall_cust').click(function() {
        var checked = $(this).prop('checked');
        $('.customer-assign-row:visible .cust-chk').prop('checked', checked);
        update_selected_count();
    });

    // Individual checkbox change
    $('.cust-chk').change(function() {
        update_selected_count();
    });

    // Live search filter
    $('#customer_search_filter').on('keyup', function() {
        var term = $(this).val().toLowerCase().trim();
        $('.customer-assign-row').each(function() {
            var searchData = $(this).attr('data-search') || '';
            if (term == '' || searchData.indexOf(term) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});

function update_selected_count() {
    var count = $('.cust-chk:checked').length;
    $('#selected_count').text(count);
}

function assign_customers_submit(e) {
    e.preventDefault();
    var formData = $('#assign_customers_form').serialize();

    $.ajax({
        type: 'POST',
        url: '<?= base_url() ?>callerid_groups/callerid_groups_save_assigned_customers/',
        data: formData,
        success: function(response) {
            var res = JSON.parse(response);
            if (res.SUCCESS) {
                $("#toast-container").css("display", "block");
                $(".toast-message").html(res.SUCCESS);
                $('.toast-top-right').delay(4000).fadeOut();
                $(document).trigger('close.facebox');

                // Reload Flexigrid to update customer count badge
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
