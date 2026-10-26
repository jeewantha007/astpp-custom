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
            }
            if (!isset($permissioninfo['callerid_groups']['callerid_groups_list']['delete'])) {
                $permissioninfo['callerid_groups']['callerid_groups_list']['delete'] = 0;
            }
            $this->session->set_userdata('permissioninfo', $permissioninfo);
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
    /**
     * Retrieve groups from callerid_groups database table
     */
    private function _get_mock_groups()
    {
        $db_groups = $this->db->select('*')->from('callerid_groups')->order_by('id', 'ASC')->get()->result_array();
        if (!empty($db_groups)) {
            foreach ($db_groups as &$g) {
                // Count numbers live from ani_map table where callerid_group_id = g.id
                $g['numbers_count'] = $this->db->where('callerid_group_id', $g['id'])->count_all_results('ani_map');
                // Count assigned customers live from accounts table where callerid_group_id = g.id
                $g['customers_count'] = $this->db->where('callerid_group_id', $g['id'])->count_all_results('accounts');
                $g['reseller_name'] = ($g['reseller_id'] == 0) ? 'Admin (SuperAdmin)' : 'Reseller';
            }
            return $db_groups;
        }

        // Fallback to session mock if database table is empty
        $groups = $this->session->userdata('mock_callerid_groups');
        if (empty($groups) || !is_array($groups)) {
            $groups = array(
                array(
                    'id' => 1,
                    'name' => 'US Outbound Primary Pool',
                    'description' => 'Main rotational Caller ID pool for US outbound accounts',
                    'numbers_count' => 3,
                    'customers_count' => 3,
                    'status' => 0,
                    'reseller_name' => 'Admin (SuperAdmin)',
                    'creation_date' => '2026-01-15 10:00:00',
                    'last_modified_date' => '2026-03-10 14:30:00'
                )
            );
            $this->session->set_userdata('mock_callerid_groups', $groups);
        }
        return $groups;
    }

    /**
     * Retrieve caller ID numbers assigned to a specific group from ani_map
     */
    private function _get_mock_numbers($group_id)
    {
        $query = $this->db->select('a.*, acc.first_name, acc.last_name, acc.number as account_number, acc.company_name')
            ->from('ani_map a')
            ->join('accounts acc', 'acc.id = a.accountid', 'left')
            ->where('a.callerid_group_id', $group_id)
            ->order_by('a.id', 'ASC')
            ->get();
        $list = array();
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $account_label = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                if (!empty($row['company_name'])) {
                    $account_label .= ' (' . $row['company_name'] . ')';
                }
                $list[] = array(
                    'id' => $row['id'],
                    'callerid_number' => $row['number'],
                    'callerid_name' => !empty($account_label) ? $account_label : 'Account #' . $row['accountid'],
                    'account_number' => $row['account_number'] ?? $row['accountid'],
                    'status' => $row['status'],
                    'creation_date' => $row['creation_date']
                );
            }
        }
        return $list;
    }

    /**
     * Retrieve all available Caller IDs from ani_map with assigned group info
     */
    private function _get_all_animap_numbers()
    {
        $query = $this->db->select('a.*, acc.first_name, acc.last_name, acc.number as account_number, acc.company_name, g.name as assigned_group_name')
            ->from('ani_map a')
            ->join('accounts acc', 'acc.id = a.accountid', 'left')
            ->join('callerid_groups g', 'g.id = a.callerid_group_id', 'left')
            ->order_by('a.id', 'ASC')
            ->get();
        $list = array();
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $account_label = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                if (!empty($row['company_name'])) {
                    $account_label .= ' (' . $row['company_name'] . ')';
                }
                $list[] = array(
                    'id' => $row['id'],
                    'number' => $row['number'],
                    'account_name' => !empty($account_label) ? $account_label : 'Account #' . $row['accountid'],
                    'account_number' => $row['account_number'] ?? $row['accountid'],
                    'assigned_group_id' => $row['callerid_group_id'],
                    'assigned_group_name' => !empty($row['assigned_group_name']) ? $row['assigned_group_name'] : gettext('None'),
                    'status' => $row['status'],
                    'creation_date' => $row['creation_date']
                );
            }
        }
        return $list;
    }

    /**
     * Internal helper: Retrieve all available customer accounts from accounts table
     */
    private function _get_all_customers()
    {
        $query = $this->db->select('acc.id, acc.number, acc.first_name, acc.last_name, acc.company_name, acc.email, acc.balance, acc.status, acc.callerid_group_id, g.name as assigned_group_name')
            ->from('accounts acc')
            ->join('callerid_groups g', 'g.id = acc.callerid_group_id', 'left')
            ->where('acc.deleted', 0)
            ->where_in('acc.type', array(0, 1))
            ->order_by('acc.id', 'ASC')
            ->get();
        $list = array();
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                if (!empty($row['company_name'])) {
                    $name .= ' (' . $row['company_name'] . ')';
                }
                $list[] = array(
                    'id' => $row['id'],
                    'number' => $row['number'],
                    'name' => !empty($name) ? $name : 'Account #' . $row['number'],
                    'email' => $row['email'],
                    'status' => ($row['status'] == 0 ? 'Active' : 'Inactive'),
                    'balance' => '$' . number_format((float)$row['balance'], 2),
                    'assigned_group_id' => $row['callerid_group_id'],
                    'assigned_group_name' => !empty($row['assigned_group_name']) ? $row['assigned_group_name'] : gettext('None')
                );
            }
            return $list;
        }

        $customers = $this->session->userdata('mock_all_customers');
        if (empty($customers) || !is_array($customers)) {
            $customers = array(
                array('id' => 1, 'number' => '1001', 'name' => 'Acme Corporation', 'email' => 'billing@acme.com', 'status' => 'Active', 'balance' => '$1,425.50', 'assigned_group_id' => 1, 'assigned_group_name' => 'US Outbound Primary Pool'),
                array('id' => 2, 'number' => '1042', 'name' => 'Global Logistics LLC', 'email' => 'ops@globallogistics.com', 'status' => 'Active', 'balance' => '$850.00', 'assigned_group_id' => 1, 'assigned_group_name' => 'US Outbound Primary Pool'),
                array('id' => 3, 'number' => '1089', 'name' => 'NextGen Cloud Telecom', 'email' => 'accounts@nextgen.io', 'status' => 'Active', 'balance' => '$3,210.75', 'assigned_group_id' => 1, 'assigned_group_name' => 'US Outbound Primary Pool')
            );
            $this->session->set_userdata('mock_all_customers', $customers);
        }
        return $customers;
    }

    /**
     * Internal helper: Retrieve assigned customers for a specific group from accounts table
     */
    private function _get_mock_customers($group_id)
    {
        $query = $this->db->select('acc.id, acc.number, acc.first_name, acc.last_name, acc.company_name, acc.email, acc.balance, acc.status, acc.callerid_group_id, g.name as assigned_group_name')
            ->from('accounts acc')
            ->join('callerid_groups g', 'g.id = acc.callerid_group_id', 'left')
            ->where('acc.deleted', 0)
            ->where('acc.callerid_group_id', $group_id)
            ->order_by('acc.id', 'ASC')
            ->get();
        $list = array();
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                if (!empty($row['company_name'])) {
                    $name .= ' (' . $row['company_name'] . ')';
                }
                $list[] = array(
                    'id' => $row['id'],
                    'number' => $row['number'],
                    'name' => !empty($name) ? $name : 'Account #' . $row['number'],
                    'email' => $row['email'],
                    'status' => ($row['status'] == 0 ? 'Active' : 'Inactive'),
                    'balance' => '$' . number_format((float)$row['balance'], 2),
                    'assigned_group_id' => $row['callerid_group_id'],
                    'assigned_group_name' => !empty($row['assigned_group_name']) ? $row['assigned_group_name'] : gettext('None')
                );
            }
            return $list;
        }

        $all = $this->_get_all_customers();
        $assigned = array();
        foreach ($all as $cust) {
            if ($cust['assigned_group_id'] == $group_id) {
                $assigned[] = $cust;
            }
        }
        return $assigned;
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
            $numbers_html = '<a href="' . base_url() . 'callerid_groups/callerid_groups_manage_numbers/' . $id . '" rel="facebox" class="badge badge-info py-1 px-2" style="font-size:11px; text-decoration:none;" title="' . gettext('Manage Numbers') . '"><i class="fa fa-phone fa-fw"></i> ' . $group['numbers_count'] . ' Numbers</a>';

            // Assigned Customers count badge
            $customers_html = '<a href="' . base_url() . 'callerid_groups/callerid_groups_assign_customers/' . $id . '" rel="facebox" class="badge badge-primary py-1 px-2" style="font-size:11px; text-decoration:none;" title="' . gettext('Assign Customers') . '"><i class="fa fa-users fa-fw"></i> ' . $group['customers_count'] . ' Customers</a>';

            // Status toggle indicator (Interactive ASTPP switch)
            $status_html = '<label class="switch" title="' . ($group['status'] == 0 ? gettext('Active - Click to Deactivate') : gettext('Inactive - Click to Activate')) . '">' .
                '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="switch_' . $id . '" onclick="callerid_toggle_status(' . $id . ')" ' . ($group['status'] == 0 ? 'checked' : '') . '>' .
                '<span class="slider round"></span></label>';

            // Row Action buttons
            $actions_html = 
                '<a href="' . base_url() . 'callerid_groups/callerid_groups_detail/' . $id . '" class="btn btn-outline-primary btn-sm mx-1" title="' . gettext('View Details') . '"><i class="fa fa-eye fa-fw"></i></a>' .
                '<a href="' . base_url() . 'callerid_groups/callerid_groups_edit/' . $id . '" rel="facebox" class="btn btn-royelblue btn-sm mx-1" title="' . gettext('Edit Group') . '"><i class="fa fa-pencil-square-o fa-fw"></i></a>' .
                '<a href="' . base_url() . 'callerid_groups/callerid_groups_manage_numbers/' . $id . '" rel="facebox" class="btn btn-outline-info btn-sm mx-1" title="' . gettext('Manage Numbers') . '"><i class="fa fa-phone fa-fw"></i></a>' .
                '<a href="' . base_url() . 'callerid_groups/callerid_groups_assign_customers/' . $id . '" rel="facebox" class="btn btn-outline-success btn-sm mx-1" title="' . gettext('Assign Customers') . '"><i class="fa fa-users fa-fw"></i></a>' .
                '<a href="javascript:void(0);" onclick="callerid_delete_single(' . $id . ', \'' . htmlspecialchars(addslashes($group['name'])) . '\')" class="btn btn-danger btn-sm mx-1" title="' . gettext('Delete Group') . '"><i class="fa fa-trash fa-fw"></i></a>';

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
     * Live status toggle handler for Caller ID Groups in database
     */
    function callerid_groups_status_toggle($id = '')
    {
        $id = intval($id);
        $row = $this->db->get_where('callerid_groups', array('id' => $id))->row();
        $new_status = 0;
        $name = '';
        if ($row) {
            $new_status = ($row->status == 0) ? 1 : 0;
            $name = $row->name;
            $this->db->where('id', $id)->update('callerid_groups', array(
                'status' => $new_status,
                'last_modified_date' => date('Y-m-d H:i:s')
            ));
        }

        $status_label = ($new_status == 0) ? gettext('Active') : gettext('Inactive');
        echo json_encode(array(
            'status' => $new_status,
            'message' => sprintf(gettext("Caller ID Group '%s' is now %s."), htmlspecialchars($name), $status_label)
        ));
        exit();
    }

    /**
     * Single group delete handler
     */
    function callerid_groups_delete_single($id = '')
    {
        $id = intval($id);
        $row = $this->db->get_where('callerid_groups', array('id' => $id))->row();
        $deleted_name = $row ? $row->name : 'Group #' . $id;

        // Delete group from database
        $this->db->where('id', $id)->delete('callerid_groups');

        // Unlink any ani_map numbers that were in this group
        $this->db->where('callerid_group_id', $id)->update('ani_map', array('callerid_group_id' => 0));

        // Unlink any accounts that were in this group
        $this->db->where('callerid_group_id', $id)->update('accounts', array('callerid_group_id' => 0));

        echo json_encode(array(
            'SUCCESS' => sprintf(gettext("Caller ID Group '%s' deleted successfully!"), htmlspecialchars($deleted_name))
        ));
        exit();
    }

    /**
     * Bulk group delete handler (compatible with ASTPP native delete_multiple)
     */
    function callerid_groups_delete_multiple()
    {
        $selected_ids = $this->input->post('selected_ids');
        if (!empty($selected_ids)) {
            $clean_ids = str_replace("'", "", $selected_ids);
            $id_array = array_map('intval', explode(',', $clean_ids));

            if (!empty($id_array)) {
                $this->db->where_in('id', $id_array)->delete('callerid_groups');
                $this->db->where_in('callerid_group_id', $id_array)->update('ani_map', array('callerid_group_id' => 0));
                $this->db->where_in('callerid_group_id', $id_array)->update('accounts', array('callerid_group_id' => 0));
            }
            echo 1;
        } else {
            echo 0;
        }
        exit();
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

        if ($id != '') {
            // Edit existing group in database
            $this->db->where('id', intval($id))->update('callerid_groups', array(
                'name' => trim($post['name']),
                'description' => trim($post['description'] ?? ''),
                'status' => intval($post['status']),
                'last_modified_date' => date('Y-m-d H:i:s')
            ));

            echo json_encode(array(
                'SUCCESS' => sprintf(gettext("Caller ID Group '%s' updated successfully!"), htmlspecialchars($post['name']))
            ));
            exit();
        } else {
            // Create new group in database
            $this->db->insert('callerid_groups', array(
                'name' => trim($post['name']),
                'description' => trim($post['description'] ?? ''),
                'status' => intval($post['status']),
                'reseller_id' => 0,
                'creation_date' => date('Y-m-d H:i:s'),
                'last_modified_date' => date('Y-m-d H:i:s')
            ));

            echo json_encode(array(
                'SUCCESS' => sprintf(gettext("Caller ID Group '%s' created successfully!"), htmlspecialchars($post['name']))
            ));
            exit();
        }
    }

    /**
     * Manage Caller ID Numbers Modal View (Screen 3: Assign from ani_map)
     */
    function callerid_groups_manage_numbers($group_id = '')
    {
        $group_id = intval($group_id);
        $group = $this->db->get_where('callerid_groups', array('id' => $group_id))->row_array();
        if (!$group) {
            $groups = $this->_get_mock_groups();
            $group = !empty($groups) ? $groups[0] : array('id' => 1, 'name' => 'Default Pool');
            $group_id = $group['id'];
        }

        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Manage Caller ID Numbers');
        $data['group'] = $group;
        $data['available_numbers'] = $this->_get_all_animap_numbers();
        $data['numbers'] = $this->_get_mock_numbers($group_id);

        $this->load->view('view_callerid_groups_numbers', $data);
    }

    /**
     * Save Assigned Caller IDs to Group (persists to ani_map.callerid_group_id)
     */
    function callerid_groups_save_assigned_numbers()
    {
        $group_id = intval($this->input->post('group_id'));
        $selected_ids = $this->input->post('animap_ids');
        if (!is_array($selected_ids)) {
            $selected_ids = array();
        }

        $group = $this->db->get_where('callerid_groups', array('id' => $group_id))->row_array();
        $group_name = $group['name'] ?? 'Group #' . $group_id;

        // 1. Unlink any numbers currently assigned to this group that were deselected
        $this->db->where('callerid_group_id', $group_id);
        if (!empty($selected_ids)) {
            $this->db->where_not_in('id', array_map('intval', $selected_ids));
        }
        $this->db->update('ani_map', array('callerid_group_id' => 0, 'last_modified_date' => gmdate('Y-m-d H:i:s')));

        // 2. Link newly selected numbers to this group
        if (!empty($selected_ids)) {
            $this->db->where_in('id', array_map('intval', $selected_ids));
            $this->db->update('ani_map', array('callerid_group_id' => $group_id, 'last_modified_date' => gmdate('Y-m-d H:i:s')));
        }

        $assigned_count = count($selected_ids);
        echo json_encode(array(
            'SUCCESS' => sprintf(gettext("%d Caller ID(s) successfully assigned to group '%s'!"), $assigned_count, htmlspecialchars($group_name))
        ));
        exit();
    }

    /**
     * Return numbers JSON for a group from ani_map
     */
    function callerid_groups_get_numbers_json($group_id = '')
    {
        $numbers = $this->_get_mock_numbers(intval($group_id));
        header('Content-Type: application/json');
        echo json_encode($numbers);
        exit();
    }

    /**
     * Toggle individual number status in ani_map
     */
    function callerid_groups_toggle_number_status()
    {
        $number_id = intval($this->input->post('number_id'));
        $row = $this->db->get_where('ani_map', array('id' => $number_id))->row();
        if ($row) {
            $new_status = ($row->status == 0) ? 1 : 0;
            $this->db->where('id', $number_id)->update('ani_map', array('status' => $new_status, 'last_modified_date' => gmdate('Y-m-d H:i:s')));
        }
        echo '1';
        exit();
    }

    /**
     * Unlink Caller ID from group (sets ani_map.callerid_group_id = 0)
     */
    function callerid_groups_delete_number()
    {
        $group_id = intval($this->input->post('group_id'));
        $number_id = intval($this->input->post('number_id'));

        $this->db->where('id', $number_id);
        $this->db->where('callerid_group_id', $group_id);
        $this->db->update('ani_map', array('callerid_group_id' => 0, 'last_modified_date' => gmdate('Y-m-d H:i:s')));

        echo '1';
        exit();
    }

    /**
     * Assign Customers Modal View (Screen 4)
     */
    function callerid_groups_assign_customers($group_id = '')
    {
        $groups = $this->_get_mock_groups();
        $group = null;
        foreach ($groups as $g) {
            if ($g['id'] == $group_id) {
                $group = $g;
                break;
            }
        }
        if (!$group) {
            $group = !empty($groups) ? $groups[0] : array('id' => 1, 'name' => 'Default Pool');
            $group_id = $group['id'];
        }

        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Assign Customers');
        $data['group'] = $group;
        $data['available_customers'] = $this->_get_all_customers();

        $this->load->view('view_callerid_groups_assign_customers', $data);
    }

    /**
     * Save Customer Assignments Handler (persists directly to accounts.callerid_group_id)
     */
    function callerid_groups_save_assigned_customers()
    {
        $group_id = intval($this->input->post('group_id'));
        $selected_ids = $this->input->post('customer_ids');
        if (!is_array($selected_ids)) {
            $selected_ids = array();
        }

        $group = $this->db->get_where('callerid_groups', array('id' => $group_id))->row_array();
        $group_name = $group['name'] ?? 'Group #' . $group_id;

        // 1. Unlink any accounts currently assigned to this group that were deselected
        $this->db->where('callerid_group_id', $group_id);
        if (!empty($selected_ids)) {
            $this->db->where_not_in('id', array_map('intval', $selected_ids));
        }
        $this->db->update('accounts', array('callerid_group_id' => 0));

        // 2. Link newly selected accounts to this group
        if (!empty($selected_ids)) {
            $this->db->where_in('id', array_map('intval', $selected_ids));
            $this->db->update('accounts', array('callerid_group_id' => $group_id));
        }

        $assigned_count = count($selected_ids);
        echo json_encode(array(
            'SUCCESS' => sprintf(gettext("%d customer(s) successfully assigned to Caller ID group '%s'!"), $assigned_count, htmlspecialchars($group_name))
        ));
        exit();
    }

    /**
     * Unassign a single customer from group (sets accounts.callerid_group_id = 0)
     */
    function callerid_groups_unassign_customer()
    {
        $group_id = intval($this->input->post('group_id'));
        $customer_id = intval($this->input->post('customer_id'));

        $customer = $this->db->get_where('accounts', array('id' => $customer_id))->row();
        $unassigned_name = $customer ? trim($customer->first_name . ' ' . $customer->last_name) : 'Customer #' . $customer_id;

        $this->db->where('id', $customer_id)
            ->where('callerid_group_id', $group_id)
            ->update('accounts', array('callerid_group_id' => 0));

        echo json_encode(array(
            'SUCCESS' => sprintf(gettext("Customer '%s' unassigned from group successfully."), htmlspecialchars($unassigned_name))
        ));
        exit();
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
