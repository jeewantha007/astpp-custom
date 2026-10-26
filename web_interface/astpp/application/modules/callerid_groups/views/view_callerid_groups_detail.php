<? extend('master.php') ?>
<? startblock('extra_head') ?>

<script type="text/javascript" language="javascript">
    $(document).ready(function() {
        // Initialize Facebox for popup modals
        $('a[rel*=facebox]').facebox();

        // Sub-tab switcher
        $('.nav-tabs a').click(function(e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });

    /**
     * Live Status Toggle in Detail View
     */
    function detail_toggle_status(group_id) {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>callerid_groups/callerid_groups_status_toggle/' + group_id,
            dataType: 'json',
            success: function(response) {
                $("#toast-container").css("display", "block");
                $(".toast-message").html(response.message || "<?= gettext('Caller ID group status updated.') ?>");
                $('.toast-top-right').delay(3000).fadeOut();

                if (response.status == 0) {
                    $('#detail_status_switch').prop('checked', true);
                    $('#status_icon_box').css('background', '#e6fffa');
                    $('#status_icon').removeClass('fa-minus-circle text-danger').addClass('fa-check-circle text-success');
                    $('#status_badge_container').html('<span class="badge badge-success px-2 py-1"><?= gettext("Active") ?></span>');
                } else {
                    $('#detail_status_switch').prop('checked', false);
                    $('#status_icon_box').css('background', '#fff5f5');
                    $('#status_icon').removeClass('fa-check-circle text-success').addClass('fa-minus-circle text-danger');
                    $('#status_badge_container').html('<span class="badge badge-secondary px-2 py-1"><?= gettext("Inactive") ?></span>');
                }
            },
            error: function() {
                alert("<?= gettext('Error updating status. Please try again.') ?>");
            }
        });
    }

    /**
     * Delete Group from Detail View
     */
    function detail_delete_group(group_id, group_name) {
        if (confirm("<?= gettext('Are you sure you want to delete Caller ID group') ?> '" + group_name + "'?")) {
            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>callerid_groups/callerid_groups_delete_single/' + group_id,
                dataType: 'json',
                success: function(response) {
                    window.location.href = '<?= base_url() ?>callerid_groups/callerid_groups_list/';
                },
                error: function() {
                    alert("<?= gettext('Error deleting group. Please try again.') ?>");
                }
            });
        }
    }

    /**
     * Toggle individual number status from Detail table
     */
    function detail_toggle_number(group_id, number_id) {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>callerid_groups/callerid_groups_toggle_number_status/',
            data: { group_id: group_id, number_id: number_id },
            success: function(response) {
                var badge = $('#num_status_badge_' + number_id);
                if (badge.hasClass('badge-success')) {
                    badge.removeClass('badge-success').addClass('badge-secondary').text('<?= gettext("Inactive") ?>');
                } else {
                    badge.removeClass('badge-secondary').addClass('badge-success').text('<?= gettext("Active") ?>');
                }
                $("#toast-container").css("display", "block");
                $(".toast-message").html("<?= gettext('Caller ID number status updated.') ?>");
                $('.toast-top-right').delay(2500).fadeOut();
            }
        });
    }

    /**
     * Delete individual number from Detail table
     */
    function detail_delete_number(group_id, number_id, num_str) {
        if (confirm("<?= gettext('Are you sure you want to delete number') ?> '" + num_str + "' <?= gettext('from this group?') ?>")) {
            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>callerid_groups/callerid_groups_delete_number/',
                data: { group_id: group_id, number_id: number_id },
                success: function(response) {
                    $('#detail_num_row_' + number_id).fadeOut(300, function() {
                        $(this).remove();
                        var current = parseInt($('#numbers_kpi_count').text()) || 0;
                        if (current > 0) {
                            $('#numbers_kpi_count').text(current - 1);
                            $('#numbers_tab_badge').text(current - 1);
                        }
                    });
                    $("#toast-container_error").css("display", "block");
                    $(".toast-message").html("Number '" + num_str + "' deleted.");
                    $('.toast-top-right').delay(2500).fadeOut();
                }
            });
        }
    }

    /**
     * Unassign customer from Detail table
     */
    function detail_unassign_customer(group_id, customer_id, customer_name) {
        if (confirm("<?= gettext('Are you sure you want to unassign customer') ?> '" + customer_name + "' <?= gettext('from this Caller ID group?') ?>")) {
            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>callerid_groups/callerid_groups_unassign_customer/',
                data: { group_id: group_id, customer_id: customer_id },
                dataType: 'json',
                success: function(response) {
                    $('#detail_cust_row_' + customer_id).fadeOut(300, function() {
                        $(this).remove();
                        var current = parseInt($('#customers_kpi_count').text()) || 0;
                        if (current > 0) {
                            $('#customers_kpi_count').text(current - 1);
                            $('#customers_tab_badge').text(current - 1);
                        }
                    });
                    $("#toast-container").css("display", "block");
                    $(".toast-message").html(response.SUCCESS || "<?= gettext('Customer unassigned successfully.') ?>");
                    $('.toast-top-right').delay(3000).fadeOut();
                }
            });
        }
    }
</script>

<style>
    .kpi-card {
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .nav-tabs .nav-link {
        font-weight: 600;
        color: #4a5568;
        border-radius: 6px 6px 0 0;
        padding: 10px 18px;
    }
    .nav-tabs .nav-link.active {
        color: #3b3280 !important;
        border-color: #e2e8f0 #e2e8f0 #fff;
        border-top: 3px solid #3b3280;
    }
</style>

<? endblock() ?>

<? startblock('page-title') ?>
    <?= $page_title ?>
<? endblock() ?>

<? startblock('content') ?>

<!-- Top Action Bar & Breadcrumb -->
<section class="slice color-three mb-3">
    <div class="w-section inverse p-0">
        <div class="col-12 d-flex justify-content-between align-items-center py-2 flex-wrap">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0 bg-transparent">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>accounts/customer_list/"><?= gettext('Accounts') ?></a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>callerid_groups/callerid_groups_list/"><?= gettext('Caller ID Groups') ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><strong><?= htmlspecialchars($group['name']) ?></strong></li>
                    </ol>
                </nav>
            </div>
            <div class="my-1">
                <a href="<?= base_url() ?>callerid_groups/callerid_groups_list/" class="btn btn-secondary btn-sm mr-2">
                    <i class="fa fa-arrow-left fa-fw"></i> <?= gettext('Back to Groups') ?>
                </a>
                <a href="<?= base_url() ?>callerid_groups/callerid_groups_edit/<?= $group['id'] ?>" rel="facebox" class="btn btn-royelblue btn-sm mr-2">
                    <i class="fa fa-pencil-square-o fa-fw"></i> <?= gettext('Edit Group') ?>
                </a>
                <a href="<?= base_url() ?>callerid_groups/callerid_groups_manage_numbers/<?= $group['id'] ?>" rel="facebox" class="btn btn-outline-info btn-sm mr-2">
                    <i class="fa fa-phone fa-fw"></i> <?= gettext('Manage Numbers') ?>
                </a>
                <a href="<?= base_url() ?>callerid_groups/callerid_groups_assign_customers/<?= $group['id'] ?>" rel="facebox" class="btn btn-outline-success btn-sm mr-2">
                    <i class="fa fa-users fa-fw"></i> <?= gettext('Assign Customers') ?>
                </a>
                <a href="javascript:void(0);" onclick="detail_delete_group(<?= $group['id'] ?>, '<?= htmlspecialchars(addslashes($group['name'])) ?>')" class="btn btn-outline-danger btn-sm">
                    <i class="fa fa-trash fa-fw"></i> <?= gettext('Delete Group') ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- KPI Metric Cards -->
<section class="slice color-three mb-3">
    <div class="w-section inverse p-0">
        <div class="row mx-0">
            <!-- Metric 1: Status (with interactive switch) -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="kpi-card p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div id="status_icon_box" class="mr-3" style="width:48px; height:48px; border-radius:50%; background:<?= ($group['status'] == 0) ? '#e6fffa' : '#fff5f5' ?>; display:flex; align-items:center; justify-content:center;">
                            <i id="status_icon" class="fa <?= ($group['status'] == 0) ? 'fa-check-circle text-success' : 'fa-minus-circle text-danger' ?> fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted" style="font-size:12px; font-weight:600; text-transform:uppercase;"><?= gettext('Pool Status') ?></div>
                            <div id="status_badge_container" style="font-size:18px; font-weight:700; color:#2d3748;">
                                <?= ($group['status'] == 0) ? '<span class="badge badge-success px-2 py-1">' . gettext('Active') . '</span>' : '<span class="badge badge-secondary px-2 py-1">' . gettext('Inactive') . '</span>' ?>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="switch mb-0" title="<?= ($group['status'] == 0) ? gettext('Active - Click to Deactivate') : gettext('Inactive - Click to Activate') ?>">
                            <input type="checkbox" id="detail_status_switch" onchange="detail_toggle_status(<?= $group['id'] ?>)" <?= ($group['status'] == 0) ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Metric 2: Caller ID Numbers -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="kpi-card p-3 d-flex align-items-center">
                    <div class="mr-3" style="width:48px; height:48px; border-radius:50%; background:#ebf8ff; display:flex; align-items:center; justify-content:center;">
                        <i class="fa fa-phone fa-2x" style="color:#3182ce;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:12px; font-weight:600; text-transform:uppercase;"><?= gettext('Caller ID Numbers') ?></div>
                        <div style="font-size:22px; font-weight:700; color:#2b6cb0;">
                            <span id="numbers_kpi_count"><?= count($numbers) ?></span> <span style="font-size:13px; font-weight:400;" class="text-muted"><?= gettext('in rotation') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 3: Assigned Customers -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="kpi-card p-3 d-flex align-items-center">
                    <div class="mr-3" style="width:48px; height:48px; border-radius:50%; background:#f0fff4; display:flex; align-items:center; justify-content:center;">
                        <i class="fa fa-users fa-2x" style="color:#38a169;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:12px; font-weight:600; text-transform:uppercase;"><?= gettext('Assigned Customers') ?></div>
                        <div style="font-size:22px; font-weight:700; color:#276749;">
                            <span id="customers_kpi_count"><?= count($customers) ?></span> <span style="font-size:13px; font-weight:400;" class="text-muted"><?= gettext('accounts') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 4: Reseller / Created Date -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="kpi-card p-3 d-flex align-items-center">
                    <div class="mr-3" style="width:48px; height:48px; border-radius:50%; background:#faf5ff; display:flex; align-items:center; justify-content:center;">
                        <i class="fa fa-calendar fa-2x" style="color:#805ad5;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:12px; font-weight:600; text-transform:uppercase;"><?= gettext('Created Date') ?></div>
                        <div style="font-size:14px; font-weight:700; color:#4a5568;">
                            <?= isset($group['creation_date']) ? substr($group['creation_date'], 0, 10) : '2026-01-15' ?>
                        </div>
                        <div style="font-size:11px;" class="text-muted">Owner: <?= htmlspecialchars($group['reseller_name'] ?? 'Admin') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Details and Sub-Tables -->
<section class="slice color-three pb-4">
    <div class="w-section inverse p-0">
        <div class="card col-12 p-4">
            
            <!-- Description Callout -->
            <div class="p-3 mb-4 rounded" style="background:#f7fafc; border-left:4px solid #3b3280;">
                <div class="d-flex align-items-start">
                    <i class="fa fa-info-circle fa-lg mt-1 mr-2" style="color:#3b3280;"></i>
                    <div>
                        <div style="font-weight:700; color:#2d3748;"><?= gettext('Group Description & Purpose') ?></div>
                        <div class="text-muted mt-1" style="font-size:14px;">
                            <?= !empty($group['description']) ? nl2br(htmlspecialchars($group['description'])) : '<em>' . gettext('No description provided for this caller ID group.') . '</em>' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabbed Navigation Header -->
            <ul class="nav nav-tabs mb-4" id="groupDetailTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="numbers-tab" data-toggle="tab" href="#numbers-panel" role="tab" aria-controls="numbers" aria-selected="true">
                        <i class="fa fa-phone fa-fw mr-1"></i> <?= gettext('Assigned Numbers Pool') ?> (<span id="numbers_tab_badge"><?= count($numbers) ?></span>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="customers-tab" data-toggle="tab" href="#customers-panel" role="tab" aria-controls="customers" aria-selected="false">
                        <i class="fa fa-users fa-fw mr-1"></i> <?= gettext('Assigned Customers') ?> (<span id="customers_tab_badge"><?= count($customers) ?></span>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="overview-tab" data-toggle="tab" href="#overview-panel" role="tab" aria-controls="overview" aria-selected="false">
                        <i class="fa fa-sliders fa-fw mr-1"></i> <?= gettext('Routing & Settings') ?>
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="groupDetailTabContent">
                
                <!-- TAB 1: Assigned Numbers -->
                <div class="tab-pane fade show active" id="numbers-panel" role="tabpanel" aria-labelledby="numbers-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0" style="color:#2d3748; font-weight:600;"><?= gettext('Rotational Outbound Caller ID Numbers') ?></h5>
                        <a href="<?= base_url() ?>callerid_groups/callerid_groups_manage_numbers/<?= $group['id'] ?>" rel="facebox" class="btn btn-outline-info btn-sm">
                            <i class="fa fa-plus-circle fa-fw"></i> <?= gettext('Add / Manage Numbers') ?>
                        </a>
                    </div>

                    <?php if (!empty($numbers)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped border">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th><?= gettext('Caller ID Number') ?></th>
                                    <th><?= gettext('Caller ID Name / Label') ?></th>
                                    <th><?= gettext('Status') ?></th>
                                    <th><?= gettext('Date Added') ?></th>
                                    <th class="text-right"><?= gettext('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $idx = 1; foreach ($numbers as $num): ?>
                                <tr id="detail_num_row_<?= $num['id'] ?>">
                                    <td class="text-muted"><?= $idx++ ?></td>
                                    <td>
                                        <strong style="color:#3b3280; font-size:14px;"><i class="fa fa-phone fa-fw mr-1 text-muted"></i><?= htmlspecialchars($num['callerid_number']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($num['callerid_name']) ?></td>
                                    <td>
                                        <span id="num_status_badge_<?= $num['id'] ?>" class="badge badge-<?= ($num['status'] == 0 ? 'success' : 'secondary') ?> px-2 py-1">
                                            <?= ($num['status'] == 0 ? gettext('Active') : gettext('Inactive')) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted" style="font-size:13px;"><?= htmlspecialchars($num['creation_date']) ?></td>
                                    <td class="text-right">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="detail_toggle_number(<?= $group['id'] ?>, <?= $num['id'] ?>)" title="<?= gettext('Toggle Status') ?>">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm ml-1" onclick="detail_delete_number(<?= $group['id'] ?>, <?= $num['id'] ?>, '<?= htmlspecialchars(addslashes($num['callerid_number'])) ?>')" title="<?= gettext('Delete Number') ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-light text-center py-4 border">
                        <i class="fa fa-phone fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-2"><?= gettext('No Caller ID numbers have been added to this pool yet.') ?></p>
                        <a href="<?= base_url() ?>callerid_groups/callerid_groups_manage_numbers/<?= $group['id'] ?>" rel="facebox" class="btn btn-sm btn-info">
                            <i class="fa fa-plus-circle"></i> <?= gettext('Add First Caller ID Number') ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 2: Assigned Customers -->
                <div class="tab-pane fade" id="customers-panel" role="tabpanel" aria-labelledby="customers-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0" style="color:#2d3748; font-weight:600;"><?= gettext('Customer Accounts Enforcing this Caller ID Pool') ?></h5>
                        <a href="<?= base_url() ?>callerid_groups/callerid_groups_assign_customers/<?= $group['id'] ?>" rel="facebox" class="btn btn-outline-success btn-sm">
                            <i class="fa fa-user-plus fa-fw"></i> <?= gettext('Assign Customers') ?>
                        </a>
                    </div>

                    <?php if (!empty($customers)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped border">
                            <thead class="thead-light">
                                <tr>
                                    <th><?= gettext('Account #') ?></th>
                                    <th><?= gettext('Customer / Company Name') ?></th>
                                    <th><?= gettext('Contact Email') ?></th>
                                    <th><?= gettext('Account Status') ?></th>
                                    <th><?= gettext('Balance') ?></th>
                                    <th class="text-right"><?= gettext('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $cust): ?>
                                <tr id="detail_cust_row_<?= $cust['id'] ?>">
                                    <td>
                                        <strong style="color:#3b3280;"><i class="fa fa-user fa-fw mr-1 text-muted"></i><?= htmlspecialchars($cust['number']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($cust['name']) ?></td>
                                    <td class="text-muted" style="font-size:13px;"><?= htmlspecialchars($cust['email']) ?></td>
                                    <td>
                                        <span class="badge badge-success px-2 py-1"><?= htmlspecialchars($cust['status']) ?></span>
                                    </td>
                                    <td><strong><?= htmlspecialchars($cust['balance']) ?></strong></td>
                                    <td class="text-right">
                                        <button class="btn btn-outline-danger btn-sm" onclick="detail_unassign_customer(<?= $group['id'] ?>, <?= $cust['id'] ?>, '<?= htmlspecialchars(addslashes($cust['name'])) ?>')" title="<?= gettext('Unassign Customer') ?>">
                                            <i class="fa fa-unlink"></i> <?= gettext('Unassign') ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-light text-center py-4 border">
                        <i class="fa fa-users fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-2"><?= gettext('No customer accounts have been assigned to this group yet.') ?></p>
                        <a href="<?= base_url() ?>callerid_groups/callerid_groups_assign_customers/<?= $group['id'] ?>" rel="facebox" class="btn btn-sm btn-success">
                            <i class="fa fa-user-plus"></i> <?= gettext('Assign Customers to this Group') ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 3: Routing & Settings -->
                <div class="tab-pane fade" id="overview-panel" role="tabpanel" aria-labelledby="overview-tab">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card p-3 border">
                                <h6 class="font-weight-bold mb-3" style="color:#3b3280;"><i class="fa fa-cogs mr-1"></i> <?= gettext('FreeSWITCH Outbound Routing Policy') ?></h6>
                                <p class="text-muted" style="font-size:13px;">
                                    When an account assigned to this group places an outbound call, FreeSWITCH Lua routing evaluates whether the caller's requested CLI is in this group's pool:
                                </p>
                                <ul style="font-size:13px;" class="pl-3 text-muted">
                                    <li class="mb-1"><strong>Matched CLI:</strong> Preserved and passed to termination carrier.</li>
                                    <li class="mb-1"><strong>Unmatched or Empty CLI:</strong> Dynamically rewritten to an active number from this group.</li>
                                    <li class="mb-1"><strong>Rotation Policy:</strong> Round-robin / random active selection from pool.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card p-3 border">
                                <h6 class="font-weight-bold mb-3" style="color:#3b3280;"><i class="fa fa-info mr-1"></i> <?= gettext('Group Metadata') ?></h6>
                                <table class="table table-sm border-0 mb-0" style="font-size:13px;">
                                    <tr>
                                        <td class="text-muted border-0"><?= gettext('Internal Group ID') ?>:</td>
                                        <td class="font-weight-bold border-0"><?= $group['id'] ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><?= gettext('Reseller / Account Owner') ?>:</td>
                                        <td class="font-weight-bold"><?= htmlspecialchars($group['reseller_name'] ?? 'Admin (SuperAdmin)') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><?= gettext('Creation Date') ?>:</td>
                                        <td class="font-weight-bold"><?= htmlspecialchars($group['creation_date'] ?? '2026-01-15 10:00:00') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><?= gettext('Last Modified Date') ?>:</td>
                                        <td class="font-weight-bold"><?= htmlspecialchars($group['last_modified_date'] ?? '2026-03-10 14:30:00') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>

<? endblock() ?>
<? end_extend() ?>
