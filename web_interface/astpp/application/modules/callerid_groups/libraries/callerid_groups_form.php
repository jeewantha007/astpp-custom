<?php
// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
// Caller ID Groups Module - Form & Grid Definition Library
// ##############################################################################
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Callerid_groups_form extends common
{
    function __construct($library_name = '')
    {
        $this->CI = & get_instance();
    }

    /**
     * Column definitions for the Caller ID Groups Flexigrid
     */
    function build_callerid_groups_grid()
    {
        $grid_field_arr = json_encode(array(
            array(
                "<input type='checkbox' name='chkAll' class='ace checkall'/><label class='lbl'></label>",
                "30",
                "",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Group Name"),
                "200",
                "name",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Description"),
                "280",
                "description",
                "",
                "",
                "",
                "",
                "true",
                "left"
            ),
            array(
                gettext("Caller ID Numbers"),
                "150",
                "numbers_count",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Assigned Customers"),
                "150",
                "customers_count",
                "",
                "",
                "",
                "",
                "false",
                "center"
            ),
            array(
                gettext("Status"),
                "100",
                "status",
                "",
                "",
                "",
                "",
                "true",
                "center"
            ),
            array(
                gettext("Actions"),
                "180",
                "",
                "",
                "",
                "",
                "",
                "false",
                "center"
            )
        ));

        return $grid_field_arr;
    }

    /**
     * Top-level Action Buttons (Create Group)
     */
    function build_grid_buttons()
    {
        $buttons_json = json_encode(array(
            array(
                gettext("Create Group"),
                "btn btn-line-warning btn",
                "fa fa-plus-circle fa-lg",
                "button_action",
                "/callerid_groups/callerid_groups_add/",
                "popup",
                "",
                "create"
            ),
            array(
                gettext("Delete"),
                "btn btn-line-danger",
                "fa fa-times-circle fa-lg",
                "button_action",
                "/callerid_groups/callerid_groups_delete_multiple/",
                "",
                "",
                "delete"
            )
        ));

        return $buttons_json;
    }

    /**
     * Search Form Configuration
     */
    function get_callerid_groups_search_form()
    {
        $form['forms'] = array(
            "",
            array(
                'id' => 'callerid_groups_search'
            )
        );

        $form[gettext('Search')] = array(
            array(
                gettext('Group Name'),
                'INPUT',
                array(
                    'name' => 'group_name',
                    'id' => 'group_name',
                    'size' => '20',
                    'class' => 'text field',
                    'placeholder' => gettext('Search by group name')
                ),
                '',
                'tOOL TIP',
                'Filter groups by name',
                '1',
                'group_name',
                '',
                '',
                'search_string_type',
                ''
            ),
            array(
                gettext('Status'),
                'status',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                'Filter by active or inactive status',
                '',
                '',
                '',
                'set_search_status'
            ),
            array(
                '',
                'HIDDEN',
                'ajax_search',
                '1',
                '',
                '',
                ''
            ),
            array(
                '',
                'HIDDEN',
                'advance_search',
                '1',
                '',
                '',
                ''
            )
        );

        $form['button_search'] = array(
            'name' => 'action',
            'id' => 'callerid_groups_search_btn',
            'content' => gettext('Search'),
            'value' => 'save',
            'type' => 'button',
            'class' => 'btn btn-success float-right'
        );

        $form['button_reset'] = array(
            'name' => 'action',
            'id' => 'id_reset',
            'content' => gettext('Clear'),
            'value' => 'cancel',
            'type' => 'reset',
            'class' => 'btn btn-secondary float-right mx-2'
        );

        return $form;
    }

    /**
     * Create / Edit Form Configuration
     */
    function get_callerid_groups_form_fields($id = '')
    {
        $form['forms'] = array(
            base_url() . 'callerid_groups/callerid_groups_save/',
            array(
                'id' => 'callerid_groups_form',
                'method' => 'POST',
                'name' => 'callerid_groups_form'
            )
        );

        $form[gettext('Group Details')] = array(
            array(
                '',
                'HIDDEN',
                array(
                    'name' => 'id'
                ),
                '', '', '', ''
            ),
            array(
                gettext('Group Name'),
                'INPUT',
                array(
                    'name' => 'name',
                    'id' => 'name',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|required|min_length[3]|max_length[100]',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Description'),
                'INPUT',
                array(
                    'name' => 'description',
                    'id' => 'description',
                    'size' => '20',
                    'class' => "text field medium"
                ),
                'trim|max_length[255]',
                'tOOL TIP',
                ''
            ),
            array(
                gettext('Status'),
                'status',
                'SELECT',
                '',
                '',
                'tOOL TIP',
                '',
                '', '', '',
                'set_status'
            )
        );

        $form['button_save'] = array(
            'name' => 'action',
            'content' => gettext('Save'),
            'value' => 'save',
            'id' => 'submit',
            'type' => 'button',
            'class' => 'btn btn-success'
        );

        $form['button_cancel'] = array(
            'name' => 'action',
            'content' => gettext('Close'),
            'value' => 'cancel',
            'type' => 'button',
            'class' => 'btn btn-secondary mx-2',
            'onclick' => '$(document).trigger("close.facebox");'
        );

        return $form;
    }
}
