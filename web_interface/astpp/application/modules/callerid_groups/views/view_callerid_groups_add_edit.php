<?php include(FCPATH . 'application/views/popup_header.php'); ?>
<script type="text/javascript">
    $("#submit").click(function(){
        submit_form("callerid_groups_form");
    });
</script>

<style type="text/css">
    /* Remove horizontal scrollbar */
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
</style>

<section class="slice m-0">
    <div class="w-section inverse p-0">
        <div>
            <div>
                <div class="col-md-12 p-0 card-header">
                    <h3 class="fw4 p-4 m-0"><?= $page_title; ?></h3>
                </div>
            </div>
        </div>
    </div>    
</section>

<div>
    <div>
        <section class="slice m-0">
            <div class="w-section inverse p-4">
                <div id="error_msg" style="display:none;">
                    <?php if (isset($validation_errors)) {
                        echo $validation_errors;
                    } ?> 
                </div>
                <?= $form; ?>
            </div>      
        </section>
    </div>
</div>

<script type="text/javascript" language="javascript">
$(document).ready(function() {
    // Hide parent form-group for hidden inputs
    $("input[type='hidden']").parents('li.form-group').addClass("d-none");
});
</script>
