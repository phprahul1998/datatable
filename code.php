<?php
   // ================code optimization========================
    public function get_instructors_data($length, $start, $searchText, $sortColumn, $sortOrder) {
        if (empty($sortColumn) || !in_array($sortColumn, ['id',  'name','email', 'action'])) {
            $sortColumn = 'id'; 
        }
        if (empty($sortOrder) || !in_array(strtoupper($sortOrder), ['ASC', 'DESC'])) {
            $sortOrder = 'DESC'; 
        }
        $this->db->select('first_name, id, last_name, email');
        $this->db->from('users');

         $get_company_id = get_company_id();
        $this->db->where('company_id', $get_company_id);
        $where = [
            'is_instructor'          => 1,
            'is_instructor_approved' => 1,
            'default_admin'          => 1,
        ];
        if (!empty($searchText)) {
            $likeCriteria = "(first_name LIKE '%" . $searchText .  "%' OR email LIKE '%" . $searchText . "%' OR last_name LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->or_where($where);
        switch($sortColumn) {
            case 'role':
                $this->db->order_by('name', $sortOrder);
                break;
            case 'image':
                break;
            case 'name':
                $this->db->order_by('first_name', $sortOrder);
                break;
            case 'action':
                break;
            default:
                $this->db->order_by($sortColumn, $sortOrder);
                break;
        }
        if ($length != '' && $start != '') {
            $this->db->limit($length, $start);
        }
        $query = $this->db->get();
        return $query->result();
    }
    
    public function get_instructors_data_count_all($searchText) {
        $this->db->from('users');
        if(!empty($searchText)) {
            $likeCriteria = "(first_name LIKE '%" . $searchText . "%' OR mobile LIKE '%" . $searchText . "%' OR email LIKE '%" . $searchText . "%' OR last_name LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $get_company_id = get_company_id();
        $this->db->where('company_id', $get_company_id);
        $where = [
            'is_instructor'          => 1,
            'is_instructor_approved' => 1,
            'default_admin'          => 1,
        ];
        $this->db->or_where($where);
        return $this->db->count_all_results(); 
    }
        // ================code optimization========================



      // ================code optimization========================

        public function getAjax_courseLevel() {
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $searchText = $this->input->post('search')['value'];
            $draw = $this->input->post('draw');
            $order = $this->input->post('order');
            $columns = $this->input->post('columns');
            $sortColumn = 'id';
            $sortOrder = 'DESC';
            if (!empty($order)) {
                $sortColumnIndex = $order[0]['column'];
                $sortColumn = $columns[$sortColumnIndex]['data'];
                $sortOrder = $order[0]['dir'];
            }
        
                $data = array();
                $list = $this->user_model->get_user_data($length, $start, $searchText, $sortColumn, $sortOrder);
                foreach($list as $value){
                    $name = ucfirst($value->first_name ?? '') . ' ' . ucfirst($value->last_name ?? '');
                    $statusBadge = '';
                    if ($value->status != 1) {
                    $statusBadge = '<small><p>' . get_phrase('status') . ': <span class="badge badge-danger-lighten">' . get_phrase('unverified') . '</span></p></small>';
                    }
                 $data[] = [
                    'email' => $value->email,
                    'image' =>$this->user_model->get_user_image_url($value->id),
                    'name' => $name . $statusBadge,
                    'mobile'=> $value->mobile,
                    'role'=> $value->name,
                    'created_at'=> date('d-m-Y',strtotime($value->created_at)),
                    'action'=>'<div class="dropright dropright">' .
                        '<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
                        '<i class="mdi mdi-dots-vertical"></i>' .
                        '</button>' .
                        '<ul class="dropdown-menu">' .
                        (($edited = can_action('7', 'edited')) ? '<li><a class="dropdown-item" target="_blanks" href="' . site_url('admin/user_form/edit_user_form/'.$value->id.'/') . '">' . get_phrase('edit') . '</a></li>' : '') .
                        (($deleted = can_action('7', 'deleted')) ? (($value->password) ? '<li><a class="dropdown-item" href="#" onclick="confirm_modal(\''. site_url('admin/users/delete/'.$value->id.'/') .'\');">'. get_phrase('Deactivate') .'</a></li>' : '') : '') .
                        (check_assign_profile_role($value->role_id) ? '<li><a class="dropdown-item" href="'. site_url('admin/user_form/user_assign_profiles/'.$value->id.'/') .'">'. get_phrase('assign_profiles') .'</a></li>' : '') .
                        '</ul>' .
                    '</div>'
                
                ];
                }
                $output = array(
                "draw"=> isset ( $draw ) ? intval( $draw ) : 0,
                "recordsTotal" => intval(count($list)),
                "recordsFiltered" => intval($this->user_model->count_all($searchText)),
                "data" => $data,
                );
                echo json_encode($output);
            }
                // ================code optimization========================
        


        $(document).ready(function() {
    $('#course_type-datatable').DataTable({
        order: [[0, 'desc']],
        processing: true,
        serverSide: true,
        paging: true,
        searchable: true,
        serverMethod: 'POST',
        ajax: {
            url: '<?= base_url()?>ajax-course-type/',
            type: 'POST', 
            complete: function(response) {
            }
        },
        columns: [
            {"data": null},
            {data:'title'},
            {data: 'classtype_name' },
            {data:'display_on_home'},
            {data:'status'},
            {data:'action'}
        ],
        "rowCallback": function(row, data, index) {
                var dt = $('#course_type-datatable').DataTable();
                $('td:eq(0)', row).html(dt.page.info().start + index + 1);
            }
    });
});




?>