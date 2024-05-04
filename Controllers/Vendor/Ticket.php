<?php
namespace App\Controllers\Vendor;
use App\Controllers\BaseController;
use App\Models\Common_model;
class Ticket extends BaseController {
    protected $c_model;
    protected $table;
    public function __construct() {
        $this->c_model = new Common_model();
        $this->table = 'dt_ticket_list';
    }
    public function index() {
        $data = [];
        $data['title'] = 'Tickets';
        $data['menu'] = 'Tickets';
        vendorView('ticket', $data);
    }
    function add_ticket() {
        $id = !empty($this->request->getVar('ticket_id')) ? $this->request->getVar('ticket_id') : '';
        $data = [];
        $data["menu"] = "Tickets";
        $data["title"] = !empty($id) ? "View Ticket" : "Add Ticket";
        $data['subject_list'] = $this->c_model->getAllData('ticket_master', 'id,subject_name', ['status' => 'Active']);
        $savedData = $this->c_model->getSingle($this->table, '*', ['ticket_id' => $id]);
        $data['reply_list'] = $this->c_model->getAllData('ticket_list', 'id,user_name,description,image,add_date', ['ticket_id' => $id],null,null,'DESC');
        $data['id'] = !empty($savedData['id']) ? $savedData['id'] : $id;
        $data['ticket_id'] = !empty($savedData['ticket_id']) ? $savedData['ticket_id'] : '';
        $data['user_name'] = !empty($savedData['user_name']) ? $savedData['user_name'] : '';
        $data['user_mobile_no'] = !empty($savedData['user_mobile_no']) ? '+91-' . $savedData['user_mobile_no'] : '';
        $data['urgency_type'] = !empty($savedData['urgency_type']) ? $savedData['urgency_type'] : 'Normal';
        $data['subject'] = !empty($savedData['subject']) ? $savedData['subject'] : '';
        $data['description'] = !empty($savedData['description']) ? $savedData['description'] : '';
        $data['add_date'] = !empty($savedData['add_date']) ? date('d/m/Y, h:i a', strtotime($savedData['add_date'])) : '';
        $data['update_date'] = !empty($savedData['update_date']) ? date('d/m/Y, h:i a', strtotime($savedData['update_date'])) : '';
        $data['image'] = !empty($savedData['image']) ? base_url('uploads/') . $savedData['image'] : '';
        $data['status'] = !empty($savedData['status']) ? $savedData['status'] : '';
        $data['is_final_closed'] = !empty($savedData['is_final_closed']) ? $savedData['is_final_closed'] : '';
        if (empty($id)) {
            vendorView('add-ticket', $data);
        } else {
            vendorView('view-ticket', $data);
        }
    }
    public function save_ticket() {
        $post = $this->request->getVar();
        $vendor = getVendorProfile();
        $saveData = [];
        if ($fileImage = $this->request->getFile('image')) {
            $fileDataImage = uploadJpgWebp($fileImage, true);
            if (!empty($fileDataImage)) {
                if (!empty($fileDataImage['jpg'])) {
                    $saveData['image'] = $fileDataImage['jpg'];
                }
            }
        }
        $saveData['user_id'] = !empty($vendor['id']) ? $vendor['id'] : '';
        $saveData['user_type'] = 'Vendor';
        $saveData['parent_id'] = !empty($post['parent_id']) ? $post['parent_id'] : 0;
        $saveData['ticket_id'] = !empty($post['ticket_id']) ? $post['ticket_id'] : rand(111111111, 999999999);
        $saveData['user_name'] = !empty($vendor['full_name']) ? $vendor['full_name'] : '';
        $saveData['user_mobile_no'] = !empty($vendor['mobile_no']) ? $vendor['mobile_no'] : '';
        $saveData['urgency_type'] = !empty($post['urgency_type']) ? $post['urgency_type'] : '';
        $saveData['subject'] = !empty($post['subject']) ? $post['subject'] : '';
        $saveData['description'] = !empty($post['description']) ? trim($post['description']) : '';
        $saveData['add_date'] = date('Y-m-d H:i:s');
        $last_id = $this->c_model->insertRecords('ticket_list', $saveData);
        session()->setFlashData('success','Message Added Successfully');
        return redirect()->to(base_url(VENDORPATH . 'view-ticket?ticket_id=' . $saveData['ticket_id']));
    }
    public function getRecords() {
        $post = $this->request->getVar();
        $get = $this->request->getVar();
        $limit = (int)(!empty($get["length"]) ? $get["length"] : 1);
        $start = (int)!empty($get["start"]) ? $get["start"] : 0;
        $is_count = !empty($post["is_count"]) ? $post["is_count"] : "";
        $totalRecords = !empty($get["recordstotal"]) ? $get["recordstotal"] : 0;
        $orderby = "DESC";
        $where = [];
        $vendor= getVendorProfile();
        $where['parent_id'] = 0;
        $where['user_id'] = $vendor['id'];
        $where['user_type'] = 'Vendor';
        $searchString = null;
        if (!empty($get["search"]["value"])) {
            $searchString = trim($get["search"]["value"]);
            $where["ticket_id LIKE '%" . $searchString . "%' OR user_name LIKE '%" . $searchString . "%' OR user_mobile_no LIKE '%" . $searchString . "%' OR urgency_type LIKE '%" . $searchString . "%' OR subject LIKE '%" . $searchString . "%' OR status LIKE '%" . $searchString . "%' "] = null;
            $limit = 100;
            $start = 0;
        }
        
        if ($is_count == "yes") {
            $countData = $this->c_model->countRecords($this->table, $where, 'id');
            echo (int)(!empty($countData) ? sizeof($countData) : 0);
            exit();
        }
        if (!empty($get["showRecords"])) {
            $limit = $get["showRecords"];
            $orderby = "DESC";
        }
        $select = '*,DATE_FORMAT(add_date , "%d-%m-%Y %r") AS add_date,DATE_FORMAT(update_date , "%d-%m-%Y %r") AS update_date';
        $listData = $this->c_model->getAllData($this->table, $select, $where, $limit, $start, $orderby);
        $result = [];
        if (!empty($listData)) {
            $i = $start + 1;
            foreach ($listData as $key => $value) {
                $push = [];
                $push = $value;
                $push["sr_no"] = $i;
                array_push($result, $push);
                $i++;
            }
        }
        $json_data = [];
        if (!empty($get["search"]["value"])) {
            $countItems = !empty($result) ? count($result) : 0;
            $json_data["draw"] = intval($get["draw"]);
            $json_data["recordsTotal"] = intval($countItems);
            $json_data["recordsFiltered"] = intval($countItems);
            $json_data["data"] = !empty($result) ? $result : [];
        } else {
            $json_data["draw"] = intval($get["draw"]);
            $json_data["recordsTotal"] = intval($totalRecords);
            $json_data["recordsFiltered"] = intval($totalRecords);
            $json_data["data"] = !empty($result) ? $result : [];
        }
        echo json_encode($json_data);
    }
}