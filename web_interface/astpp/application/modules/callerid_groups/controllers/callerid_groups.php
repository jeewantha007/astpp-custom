<?php
// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
// Caller ID Groups Module - UI Screen 1 Prototype
// ##############################################################################
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Callerid_groups extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->helper('template_inheritance');
        $this->load->library('session');
        // Check login session (redirect to login if unauthenticated)
        if ($this->session->userdata('user_login') == FALSE) {
            redirect(base_url() . 'login/login');
        }

        // Ensure prototype module access is permitted in current session
        // Prevents ASTPP Form library's check_permissions() from redirecting to dashboard
        $permited_modules = @unserialize($this->session->userdata('permited_modules'));
        if (!is_array($permited_modules)) {
            $permited_modules = array();
        }
        $changed = false;
        foreach (array('callerid', 'callerid_groups') as $mod) {
            if (!in_array($mod, $permited_modules)) {
                $permited_modules[] = $mod;
                $changed = true;
            }
        }
        if ($changed) {
            $this->session->set_userdata('permited_modules', serialize($permited_modules));
        }

        // Ensure button permission is granted in current session for prototype
        $permissioninfo = $this->session->userdata('permissioninfo');
        if (is_array($permissioninfo)) {
            if (!isset($permissioninfo['callerid_groups']['callerid_groups_list']['create'])) {
                $permissioninfo['callerid_groups']['callerid_groups_list']['create'] = 0;
                $this->session->set_userdata('permissioninfo', $permissioninfo);
            }
        }

        $this->load->library('callerid_groups_form');
        $this->load->library('astpp/form', 'callerid_groups_form');
        $this->load->library('astpp/permission');
    }

    /**
     * Screen 1: Caller ID Groups List Page
     */
    function callerid_groups_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Caller ID Groups');
        $data['search_flag'] = true;

        // Load grid fields and top action buttons from form library
        $data['grid_fields'] = $this->callerid_groups_form->build_callerid_groups_grid();
        $data['grid_buttons'] = $this->callerid_groups_form->build_grid_buttons();

        // Build search form
        $data['form_search'] = $this->form->build_serach_form(
            $this->callerid_groups_form->get_callerid_groups_search_form()
        );

        $this->load->view('view_callerid_groups_list', $data);
    }

    /**
     * Internal helper: Retrieve mock groups from session or default list
     */
    private function _get_mock_groups()
    {
        $groups = $this->session->userdata('mock_callerid_groups');
        if (empty($groups) || !is_array($groups)) {
            $groups = array(
                array(
                    'id' => 1,
                    'name' => 'US Outbound Primary Pool',
                    'description' => 'Main rotational Caller ID pool for US outbound accounts',
                    'numbers_count' => 12,
                    'customers_count' => 35,
                    'status' => 0, // 0 = Active
                    'reseller_name' => 'Admin (SuperAdmin)',
                    'creation_date' => '2026-01-15 10:00:00',
                    'last_modified_date' => '2026-03-10 14:30:00'
                ),
                array(
                    'id' => 2,
                    'name' => 'UK Support Team Pool',
                    'description' => 'London & Manchester geographic numbers for customer service',
                    'numbers_count' => 6,
                    'customers_count' => 14,
                    'status' => 0,
                    'reseller_name' => 'Admin (SuperAdmin)',
                    'creation_date' => '2026-02-01 11:20:00',
                    'last_modified_date' => '2026-02-20 16:45:00'
                ),
                array(
                    'id' => 3,
                    'name' => 'APAC Operations Pool',
                    'description' => 'Singapore and Sydney localized outbound caller IDs',
                    'numbers_count' => 4,
                    'customers_count' => 8,
                    'status' => 0,
                    'reseller_name' => 'Admin (SuperAdmin)',
                    'creation_date' => '2026-02-18 08:30:00',
                    'last_modified_date' => '2026-03-01 10:15:00'
                ),
                array(
                    'id' => 4,
                    'name' => 'EU Legacy Testing Pool',
                    'description' => 'Deprecated test group - scheduled for retirement',
                    'numbers_count' => 0,
                    'customers_count' => 0,
                    'status' => 1, // 1 = Inactive
                    'reseller_name' => 'Admin (SuperAdmin)',
                    'creation_date' => '2025-11-10 12:00:00',
                    'last_modified_date' => '2026-01-05 09:00:00'
                )
            );
            $this->session->set_userdata('mock_callerid_groups', $groups);
        }
        return $groups;
    }

    /**
     * Internal helper: Retrieve mock caller ID numbers for a group
     */
    private function _get_mock_numbers($group_id)
    {
        $all_numbers = array(
            1 => array(
                array('id' => 101, 'callerid_number' => '+12025550143', 'callerid_name' => 'Main Office US', 'status' => 0, 'creation_date' => '2026-01-16 09:20:00'),
                array('id' => 102, 'callerid_number' => '+12025550188', 'callerid_name' => 'Sales US East', 'status' => 0, 'creation_date' => '2026-01-16 09:25:00'),
                array('id' => 103, 'callerid_number' => '+14155552671', 'callerid_name' => 'San Francisco Tech Support', 'status' => 0, 'creation_date' => '2026-01-20 14:10:00'),
                array('id' => 104, 'callerid_number' => '+13125557890', 'callerid_name' => 'Chicago Regional Dispatch', 'status' => 0, 'creation_date' => '2026-02-02 11:45:00'),
                array('id' => 105, 'callerid_number' => '+17025559812', 'callerid_name' => 'West Coast Operations', 'status' => 0, 'creation_date' => '2026-02-15 16:30:00')
            ),
            2 => array(
                array('id' => 201, 'callerid_number' => '+442079460912', 'callerid_name' => 'London Central Desk', 'status' => 0, 'creation_date' => '2026-02-01 10:15:00'),
                array('id' => 202, 'callerid_number' => '+441614960123', 'callerid_name' => 'Manchester Regional Hub', 'status' => 0, 'creation_date' => '2026-02-03 14:00:00')
            ),
            3 => array(
                array('id' => 301, 'callerid_number' => '+6567123456', 'callerid_name' => 'Singapore Support Hub', 'status' => 0, 'creation_date' => '2026-02-19 08:30:00'),
                array('id' => 302, 'callerid_number' => '+61291234567', 'callerid_name' => 'Sydney Tech Center', 'status' => 0, 'creation_date' => '2026-02-21 09:00:00')
            ),
            4 => array()
        );

        return $all_numbers[$group_id] ?? array(
            array('id' => 901, 'callerid_number' => '+18005550199', 'callerid_name' => 'Toll-Free Alternate', 'status' => 0, 'creation_date' => date('Y-m-d H:i:s'))
        );
    }

    /**
     * Internal helper: Retrieve mock assigned customers for a group
     */
    private function _get_mock_customers($group_id)
    {
        $all_customers = array(
            1 => array(
                array('id' => 1, 'number' => '1001', 'name' => 'Acme Corporation', 'email' => 'billing@acme.com', 'status' => 'Active', 'balance' => '$1,425.50'),
                array('id' => 2, 'number' => '1042', 'name' => 'Global Logistics LLC', 'email' => 'ops@globallogistics.com', 'status' => 'Active', 'balance' => '$850.00'),
                array('id' => 3, 'number' => '1089', 'name' => 'NextGen Cloud Telecom', 'email' => 'accounts@nextgen.io', 'status' => 'Active', 'balance' => '$3,210.75')
            ),
            2 => array(
                array('id' => 4, 'number' => '1105', 'name' => 'Britannic Systems Ltd', 'email' => 'finance@britannic.co.uk', 'status' => 'Active', 'balance' => '£540.20')
            ),
            3 => array(
                array('id' => 5, 'number' => '1120', 'name' => 'Pacific Traders SG', 'email' => 'contact@pacifictraders.sg', 'status' => 'Active', 'balance' => '$620.00')
            ),
            4 => array()
        );

        return $all_customers[$group_id] ?? array();
    }

    /**
     * Flexigrid JSON Data Provider
     */
    function callerid_groups_list_json()
    {
        // 1. Defined realistic mock data rows (with session persistence)
        $mock_groups = $this->_get_mock_groups();

        // 2. Apply search filter if active in session (mock filtering)
        $search_filter = $this->session->userdata('callerid_groups_search');
        if (!empty($search_filter) && is_array($search_filter)) {
            $filtered = array();
            foreach ($mock_groups as $group) {
                $match = true;
                if (!empty($search_filter['group_name']) && stripos($group['name'], $search_filter['group_name']) === false) {
                    $match = false;
                }
                if (isset($search_filter['status']) && $search_filter['status'] !== '' && $search_filter['status'] != -1) {
                    if ($group['status'] != $search_filter['status']) {
                        $match = false;
                    }
                }
                if ($match) {
                    $filtered[] = $group;
                }
            }
            $mock_groups = $filtered;
        }

        // 3. Format rows for Flexigrid
        $rows = array();
        foreach ($mock_groups as $group) {
            $id = $group['id'];

            // Checkbox column
            $checkbox = '<input type="checkbox" name="chkAll" id="' . $id . '" class="ace chkRefNos" onclick="clickchkbox(' . $id . ')" value="' . $id . '"><label class="lbl"></label>';

            // Group Name - Clickable link navigating directly to Group Detail View
            $name_html = '<a href="' . base_url() . 'callerid_groups/callerid_groups_detail/' . $id . '" style="font-weight:600; color:#3b3280; text-decoration:none;" class="hover-underline" title="' . gettext('View Group Details') . '">' . htmlspecialchars($group['name']) . '</a>';

            // Description
            $desc_html = htmlspecialchars($group['description']);

            // Caller ID Numbers count badge
            $numbers_html = '<span class="badge badge-info py-1 px-2" style="font-size:11px;"><i class="fa fa-phone fa-fw"></i> ' . $group['numbers_count'] . ' Numbers</span>';

            // Assigned Customers count badge
            $customers_html = '<span class="badge badge-primary py-1 px-2" style="font-size:11px;"><i class="fa fa-users fa-fw"></i> ' . $group['customers_count'] . ' Customers</span>';

            // Status toggle indicator (ASTPP native switch styling)
            if ($group['status'] == 0) {
                $status_html = '<label class="switch" title="Active">' .
                    '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" checked disabled>' .
                    '<span class="slider round"></span></label>';
            } else {
                $status_html = '<label class="switch" title="Inactive">' .
                    '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" disabled>' .
                    '<span class="slider round"></span></label>';
            }

            // Row Action buttons
            $actions_html = 
                '<a href="' . base_url() . 'callerid_groups/callerid_groups_detail/' . $id . '" class="btn btn-outline-primary btn-sm mx-1" title="' . gettext('View Details') . '"><i class="fa fa-eye fa-fw"></i></a>' .
                '<a href="' . base_url() . 'callerid_groups/callerid_groups_edit/' . $id . '" rel="facebox" class="btn btn-royelblue btn-sm mx-1" title="' . gettext('Edit Group') . '"><i class="fa fa-pencil-square-o fa-fw"></i></a>' .
                '<a href="javascript:void(0);" onclick="callerid_placeholder(\'Manage Numbers (Screen 3)\');" class="btn btn-outline-info btn-sm mx-1" title="' . gettext('Manage Numbers') . '"><i class="fa fa-phone fa-fw"></i></a>' .
                '<a href="javascript:void(0);" onclick="callerid_placeholder(\'Assign Customers (Screen 4)\');" class="btn btn-outline-success btn-sm mx-1" title="' . gettext('Assign Customers') . '"><i class="fa fa-users fa-fw"></i></a>' .
                '<a href="javascript:void(0);" onclick="callerid_placeholder(\'Delete Group\');" class="btn btn-danger btn-sm mx-1" title="' . gettext('Delete Group') . '"><i class="fa fa-trash fa-fw"></i></a>';

            $rows[] = array(
                'id' => $id,
                'cell' => array(
                    $checkbox,
                    $name_html,
                    $desc_html,
                    $numbers_html,
                    $customers_html,
                    $status_html,
                    $actions_html
                )
            );
        }

        $json_data = array(
            'page' => 1,
            'total' => count($rows),
            'rows' => $rows
        );

        header('Content-Type: application/json');
        echo json_encode($json_data);
        exit();
    }

    /**
     * Search submission handler (saves search parameters into session)
     */
    function callerid_groups_list_search()
    {
        $post = $this->input->post();
        if ($this->input->post('advance_search', TRUE) == 1) {
            unset($post['action']);
            unset($post['advance_search']);
            unset($post['ajax_search']);
            $this->session->set_userdata('callerid_groups_search', $post);
        }
        echo '1';
    }

    /**
     * Clear search filter handler
     */
    function callerid_groups_list_clearsearchfilter()
    {
        $this->session->unset_userdata('callerid_groups_search');
        echo '1';
    }

    /**
     * Create Caller ID Group Modal (Facebox popup)
     */
    function callerid_groups_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Create Caller ID Group');
        $data['flag'] = 'create';
        $data['form'] = $this->form->build_form(
            $this->callerid_groups_form->get_callerid_groups_form_fields(''),
            array('status' => 0)
        );
        $this->load->view('view_callerid_groups_add_edit', $data);
    }

    /**
     * Edit Caller ID Group Modal (Facebox popup)
     */
    function callerid_groups_edit($edit_id = '')
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Edit Caller ID Group');
        $data['flag'] = 'edit';

        $groups = $this->_get_mock_groups();
        $edit_data = array('id' => $edit_id, 'name' => '', 'description' => '', 'status' => 0);
        foreach ($groups as $g) {
            if ($g['id'] == $edit_id) {
                $edit_data = $g;
                break;
            }
        }

        $data['form'] = $this->form->build_form(
            $this->callerid_groups_form->get_callerid_groups_form_fields($edit_id),
            $edit_data
        );
        $this->load->view('view_callerid_groups_add_edit', $data);
    }

    /**
     * Save Caller ID Group (handles Create & Edit AJAX submissions)
     */
    function callerid_groups_save()
    {
        $post = $this->input->post();
        $id = isset($post['id']) ? trim($post['id']) : '';

        // Server-side validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', gettext('Group Name'), 'trim|required|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('status', gettext('Status'), 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array(
                'name_error' => form_error('name', '', ''),
                'status_error' => form_error('status', '', '')
            ));
            exit();
        }

        $groups = $this->_get_mock_groups();

        if ($id != '') {
            // Edit existing group
            $updated_name = $post['name'];
            foreach ($groups as &$group) {
                if ($group['id'] == $id) {
                    $group['name'] = trim($post['name']);
                    $group['description'] = trim($post['description'] ?? '');
                    $group['status'] = intval($post['status']);
                    $group['last_modified_date'] = date('Y-m-d H:i:s');
                    $updated_name = $group['name'];
                    break;
                }
            }
            $this->session->set_userdata('mock_callerid_groups', $groups);

            echo json_encode(array(
                'SUCCESS' => sprintf(gettext("Caller ID Group '%s' updated successfully!"), htmlspecialchars($updated_name))
            ));
            exit();
        } else {
            // Create new group
            $new_id = 1;
            foreach ($groups as $g) {
                if ($g['id'] >= $new_id) {
                    $new_id = $g['id'] + 1;
                }
            }

            $new_group = array(
                'id' => $new_id,
                'name' => trim($post['name']),
                'description' => trim($post['description'] ?? ''),
                'numbers_count' => 0,
                'customers_count' => 0,
                'status' => intval($post['status']),
                'reseller_name' => 'Admin (SuperAdmin)',
                'creation_date' => date('Y-m-d H:i:s'),
                'last_modified_date' => date('Y-m-d H:i:s')
            );
            $groups[] = $new_group;
            $this->session->set_userdata('mock_callerid_groups', $groups);

            echo json_encode(array(
                'SUCCESS' => sprintf(gettext("Caller ID Group '%s' created successfully!"), htmlspecialchars($new_group['name']))
            ));
            exit();
        }
    }

    /**
     * Group Detail Page View (shows group info, metric cards, numbers pool, and assigned customers)
     */
    function callerid_groups_detail($id = '')
    {
        $groups = $this->_get_mock_groups();
        $group = null;
        foreach ($groups as $g) {
            if ($g['id'] == $id) {
                $group = $g;
                break;
            }
        }

        if (!$group) {
            // Default to first group if ID not found
            $group = !empty($groups) ? $groups[0] : array(
                'id' => 1,
                'name' => 'Default Caller ID Pool',
                'description' => '',
                'numbers_count' => 0,
                'customers_count' => 0,
                'status' => 0,
                'reseller_name' => 'Admin',
                'creation_date' => date('Y-m-d H:i:s'),
                'last_modified_date' => date('Y-m-d H:i:s')
            );
            $id = $group['id'];
        }

        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Caller ID Group Details');
        $data['group'] = $group;
        $data['numbers'] = $this->_get_mock_numbers($id);
        $data['customers'] = $this->_get_mock_customers($id);

        $this->load->view('view_callerid_groups_detail', $data);
    }
}
