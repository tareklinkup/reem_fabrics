<?php 

class Requisition extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->cbrunch = $this->session->userdata('BRANCHid');
        $access = $this->session->userdata('userId');
        if($access == '' ){
            redirect("Login");
        }
        $this->load->model('Model_table', "mt", TRUE);
    }

    public function productRequisition(){
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }

        $data['requisitionId'] = 0;
        $data['title'] = "Product Requisition";
        $data['content'] = $this->load->view('Administrator/requisition/product_requisition', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function requisitionEdit($requisitionId){
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }

        $data['requisitionId'] = $requisitionId;
        $data['title'] = "Product Requisition";
        $data['content'] = $this->load->view('Administrator/requisition/product_requisition', $data, TRUE);
        $this->load->view('Administrator/index', $data);
    }

    public function addProductRequisition(){
        $res = ['success'=>false, 'message'=>''];
        try{
            $data = json_decode($this->input->raw_input_stream);
            $requisition = array(
                'requisition_date' => $data->requisition->requisition_date,
                'requisition_by' => $data->requisition->requisition_by,
                'requisition_from' => $this->session->userdata('BRANCHid'),
                'requisition_to' => $data->requisition->requisition_to,
                'note' => $data->requisition->note,
                'total_amount' => $data->requisition->total_amount,
                'added_by' => $this->session->userdata("FullName"),
                'added_datetime' => date("Y-m-d H:i:s")
            );

            $this->db->insert('tbl_requisitionmaster', $requisition);
            $requisitionId = $this->db->insert_id();

            foreach($data->cart as $cartProduct){
                
                $requisitionDetails = array(
                    'requisition_id' => $requisitionId,
                    'product_id' => $cartProduct->product_id,
                    'quantity' => $cartProduct->quantity,
                    'purchase_rate' => $cartProduct->purchase_rate,
                    'total' => $cartProduct->total
                );

                $this->db->insert('tbl_requisitiondetails', $requisitionDetails);
             
            }
            
            $res = ['success'=>true, 'message'=>'Transfer success'];
        } catch (Exception $ex){
            $res = ['success'=>false, 'message'=>$ex->getMessage];
        }

        echo json_encode($res);
    }

    public function updateProductRequisition(){
        
        $res = ['success'=>false, 'message'=>''];
            try{
                $data           = json_decode($this->input->raw_input_stream);
                $requisitionId     =   $data->requisition->requisition_id;

                $oldTransfer    =   $this->db->query("select * from tbl_requisitionmaster where requisition_id = ?", $requisitionId)->row();

                $requisition = array(
                    'requisition_date' => $data->requisition->requisition_date,
                    'requisition_by' => $data->requisition->requisition_by,
                    'requisition_from' => $this->session->userdata('BRANCHid'),
                    'requisition_to' => $data->requisition->requisition_to,
                    'note' => $data->requisition->note
                );

                $this->db->where('requisition_id', $requisitionId)->update('tbl_requisitionmaster', $requisition);

                $oldRequisitionDetails = $this->db->query("select * from tbl_requisitiondetails where requisition_id = ?", $requisitionId)->result();
                
                $this->db->query("delete from tbl_requisitiondetails where requisition_id = ?", $requisitionId);

                // foreach($oldTransferDetails as $oldDetails) {
                //     $this->db->query("
                //         update tbl_currentinventory 
                //         set transfer_from_quantity = transfer_from_quantity - ? 
                //         where product_id = ?
                //         and branch_id = ?
                //     ", [$oldDetails->quantity, $oldDetails->product_id, $this->session->userdata('BRANCHid')]);

                //     $this->db->query("
                //         update tbl_currentinventory 
                //         set transfer_to_quantity = transfer_to_quantity - ? 
                //         where product_id = ?
                //         and branch_id = ?
                //     ", [$oldDetails->quantity, $oldDetails->product_id, $oldTransfer->transfer_to]);
                // }

                foreach($data->cart as $cartProduct){
                    $requisitionDetails = array(
                        'requisition_id' => $requisitionId,
                        'product_id' => $cartProduct->product_id,
                        'quantity' => $cartProduct->quantity
                    );

                    $this->db->insert('tbl_requisitiondetails', $requisitionDetails);

                    // $currentBranchInventoryCount = $this->db->query("select * from tbl_currentinventory where product_id = ? and branch_id = ?", [$cartProduct->product_id, $this->session->userdata('BRANCHid')])->num_rows();
                    // if($currentBranchInventoryCount == 0){
                    //     $currentBranchInventory = array(
                    //         'product_id' => $cartProduct->product_id,
                    //         'transfer_from_quantity' => $cartProduct->quantity,
                    //         'branch_id' => $this->session->userdata('BRANCHid')
                    //     );

                    //     $this->db->insert('tbl_currentinventory', $currentBranchInventory);
                    // } else {
                    //     $this->db->query("
                    //         update tbl_currentinventory 
                    //         set transfer_from_quantity = transfer_from_quantity + ? 
                    //         where product_id = ? 
                    //         and branch_id = ?
                    //     ", [$cartProduct->quantity, $cartProduct->product_id, $this->session->userdata('BRANCHid')]);
                    // }

                    // $transferToBranchInventoryCount = $this->db->query("select * from tbl_currentinventory where product_id = ? and branch_id = ?", [$cartProduct->product_id, $data->transfer->transfer_to])->num_rows();
                    // if($transferToBranchInventoryCount == 0){
                    //     $transferToBranchInventory = array(
                    //         'product_id' => $cartProduct->product_id,
                    //         'transfer_to_quantity' => $cartProduct->quantity,
                    //         'branch_id' => $data->transfer->transfer_to
                    //     );

                    //     $this->db->insert('tbl_currentinventory', $transferToBranchInventory);
                    // } else {
                    //     $this->db->query("
                    //         update tbl_currentinventory
                    //         set transfer_to_quantity = transfer_to_quantity + ?
                    //         where product_id = ?
                    //         and branch_id = ?
                    //     ", [$cartProduct->quantity, $cartProduct->product_id, $data->transfer->transfer_to]);
                    // }
                }
                $res = ['success'=>true, 'message'=>'Requisition updated'];
            } catch (Exception $ex){
                $res = ['success'=>false, 'message'=>$ex->getMessage];
            }

            echo json_encode($res);
    }

    public function requisitionList(){
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Requisition List";
        $data['content'] = $this->load->view('Administrator/requisition/requisition_list', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function requisitionReceivedList(){
        $access = $this->mt->userAccess();
        if(!$access){
            redirect(base_url());
        }
        $data['title'] = "Requisition Received List";
        $data['content'] = $this->load->view('Administrator/requisition/requisition_received_list', $data, true);
        $this->load->view('Administrator/index', $data);
    }


    

    public function getRequisitions(){
        $data = json_decode($this->input->raw_input_stream);

        $clauses = "";
        if(isset($data->branch) && $data->branch != ''){
            $clauses .= " and rm.requisition_to = '$data->branch'";
        }

        if((isset($data->dateFrom) && $data->dateFrom != '') && (isset($data->dateTo) && $data->dateTo != '')){
            $clauses .= " and rm.requisition_date between '$data->dateFrom' and '$data->dateTo'";
        }

        if(isset($data->requisitionId) && $data->requisitionId != ''){
            $clauses .= " and rm.requisition_id = '$data->requisitionId'";
        }

        $requisitions = $this->db->query("
            select
                rm.*,
                b.Brunch_name as requisition_to_name,
                e.Employee_Name as requisition_by_name
            from tbl_requisitionmaster rm
            join tbl_brunch b on b.brunch_id = rm.requisition_to
            join tbl_employee e on e.Employee_SlNo = rm.requisition_by
            where rm.requisition_from = ? $clauses
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($requisitions);
    }

    public function getRequisitionDetails() {
        
        $data = json_decode($this->input->raw_input_stream);
        $requisitionDetails = $this->db->query("
            select 
                rd.*,
                p.Product_Code,
                p.Product_Name,
                pc.ProductCategory_Name
            from tbl_requisitiondetails rd
            join tbl_product p on p.Product_SlNo = rd.product_id
            left join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
            where rd.requisition_id = ?
        ", $data->requisitionId)->result();

        echo json_encode($requisitionDetails);
    }

    public function getRequisitionReceives(){
        $data = json_decode($this->input->raw_input_stream);

        $branchClause = "";
        if($data->branch != null && $data->branch != ''){
            $branchClause = " and rm.requisition_from = '$data->branch'";
        }

        $dateClause = "";
        if(($data->dateFrom != null && $data->dateFrom != '') && ($data->dateTo != null && $data->dateTo != '')){
            $dateClause = " and rm.requisition_date between '$data->dateFrom' and '$data->dateTo'";
        }


        $transfers = $this->db->query("
            select
                rm.*,
                b.Brunch_name as requisition_from_name,
                e.Employee_Name as requisition_by_name
            from tbl_requisitionmaster rm
            join tbl_brunch b on b.brunch_id = rm.requisition_from
            join tbl_employee e on e.Employee_SlNo = rm.requisition_by
            where rm.requisition_to = ? $branchClause $dateClause
        ", $this->session->userdata('BRANCHid'))->result();

        echo json_encode($transfers);
    }

    public function requisitionInvoice($requisitionId){
        $data['title'] = 'Requisition Invoice';

        $data['requisition'] = $this->db->query("
            select
                rm.*,
                b.Brunch_name as requisition_to_name,
                e.Employee_Name as requisition_by_name
            from tbl_requisitionmaster rm
            join tbl_brunch b on b.brunch_id = rm.requisition_to
            join tbl_employee e on e.Employee_SlNo = rm.requisition_by
            where rm.requisition_id = ?
        ", $requisitionId)->row();

        $data['requisitionDetails'] = $this->db->query("
            select
                rd.*,
                p.Product_Code,
                p.Product_Name,
                pc.ProductCategory_Name
            from tbl_requisitiondetails rd
            join tbl_product p on p.Product_SlNo = rd.product_id
            join tbl_productcategory pc on pc.ProductCategory_SlNo = p.ProductCategory_ID
            where rd.requisition_id = ?
        ", $requisitionId)->result();

        $data['content'] = $this->load->view('Administrator/requisition/requisition_invoice', $data, true);
        $this->load->view('Administrator/index', $data);
    }

    public function deleteRequisition() {
        $res = ['success'=>false, 'message'=>''];
        try{
            $data = json_decode($this->input->raw_input_stream);
            $requisitionId = $data->requisitionId;

            $this->db->query("delete from tbl_requisitionmaster where requisition_id = ?", $requisitionId);
            $this->db->query("delete from tbl_requisitiondetails where requisition_id = ?", $requisitionId);
         
            $res = ['success'=>true, 'message'=>'Requisition deleted'];
        } catch (Exception $ex){
            $res = ['success'=>false, 'message'=>$ex->getMessage];
        }

        echo json_encode($res);
    }
}



?>