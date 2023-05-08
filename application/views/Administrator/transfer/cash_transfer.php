<style>
.v-select {
    margin-bottom: 5px;
}

.v-select .dropdown-toggle {
    padding: 0px;
}

.v-select input[type=search],
.v-select input[type=search]:focus {
    margin: 0px;
}

.v-select .vs__selected-options {
    overflow: hidden;
    flex-wrap: nowrap;
}

.v-select .selected-tag {
    margin: 2px 0px;
    white-space: nowrap;
    position: absolute;
    left: 0px;
}

.v-select .vs__actions {
    margin-top: -5px;
}

.v-select .dropdown-menu {
    width: auto;
    overflow-y: auto;
}
</style>

<div id="productTransfer">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="widget-box">
                <div class="widget-header">
                    <h4 class="widget-title">Transfer Information</h4>
                    <div class="widget-toolbar">
                        <a href="#" data-action="collapse">
                            <i class="ace-icon fa fa-chevron-up"></i>
                        </a>

                        <a href="#" data-action="close">
                            <i class="ace-icon fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="widget-body">
                    <div class="widget-main" style="min-height:117px;">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label class="control-label col-md-4">Transfer date</label>
                                    <div class="col-md-8">
                                        <input type="date" class="form-control" v-model="transfer.transfer_date"
                                            @change="getTransfers">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4">Transfer by</label>
                                    <div class="col-md-8">
                                        <select class="form-control"
                                            v-bind:style="{display: employees.length > 0 ? 'none' : ''}"></select>
                                        <v-select v-bind:options="employees" v-model="selectedEmployee"
                                            label="Employee_Name"
                                            v-bind:style="{display: employees.length > 0 ? '' : 'none'}"></v-select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4">Transfer to</label>
                                    <div class="col-md-8">
                                        <select class="form-control"
                                            v-bind:style="{display: branches.length > 0 ? 'none' : ''}"></select>
                                        <v-select v-bind:options="branches" v-model="selectedBranch" label="Brunch_name"
                                            v-bind:style="{display: branches.length > 0 ? '' : 'none'}"></v-select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-4">Amount</label>
                                    <div class="col-md-8">
                                        <input type="number" step="0.01" class="form-control"
                                            v-model="transfer.transfer_amount">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <textarea class="form-control" style="min-height:84px" placeholder="Note"
                                        v-model="transfer.note"></textarea>
                                </div>
                                <button class="btn btn-success pull-right" v-on:click="saveCashTransfer">Save</button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Transfer Date</th>
                            <th>Transfer by</th>
                            <th>Transfer to</th>
                            <th>Note</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, sl) in transfers">
                            <td>{{ sl + 1 }}</td>
                            <td>{{ item.transfer_date }}</td>
                            <td>{{ item.transfer_by_name }}</td>
                            <td>{{ item.transfer_to_name }}</td>
                            <td>{{ item.note }}</td>
                            <td>{{ item.transfer_amount }}</td>
                            <td>
                                <?php if($this->session->userdata('accountType') != 'u'){?>
                                <button type="button" class="button edit" @click="editTransfer(item)">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button type="button" class="button" @click="deleteTransfer(item.transfer_id)">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <?php }?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
Vue.component('v-select', VueSelect.VueSelect);
new Vue({
    el: '#productTransfer',
    data() {
        return {
            transfer: {
                transfer_id: 0,
                transfer_date: moment().format('YYYY-MM-DD'),
                transfer_by: null,
                transfer_from: '',
                transfer_to: '',
                transfer_amount: '',
                note: ''
            },
            transfers: [],
            employees: [],
            selectedEmployee: null,
            branches: [],
            selectedBranch: null,
        }
    },
    created() {
        this.getEmployees();
        this.getBranches();
        this.getTransfers();
    },
    methods: {
        getTransfers() {
            axios.post('/get_cash_transfers', {
                date: this.transfer.transfer_date
            }).then(res => {
                this.transfers = res.data;
            })
        },
        getEmployees() {
            axios.post('/get_employees', {
                is_courier: 'Yes'
            }).then(res => {
                this.employees = res.data;
            })
        },

        getBranches() {
            axios.get('/get_branches').then(res => {
                let currentBranchId = parseInt("<?php echo $this->session->userdata('BRANCHid');?>");
                let currentBranchInd = res.data.findIndex(branch => branch.brunch_id ==
                    currentBranchId);
                res.data.splice(currentBranchInd, 1);
                this.branches = res.data;
            })
        },

        saveCashTransfer() {
            if (this.transfer.transfer_date == null) {
                alert('Select transfer date');
                return;
            }


            if (this.selectedBranch == null) {
                alert('Select branch');
                return;
            }

            if (this.transfer.transfer_amount == '' || this.transfer.transfer_amount == 0) {
                alert('transfer amount required');
                return;
            }

            if (this.selectedEmployee != null) {
                this.transfer.transfer_by = this.selectedEmployee.Employee_SlNo;
            } else {
                this.transfer.transfer_by = null;
            }

            this.transfer.transfer_to = this.selectedBranch.brunch_id;

            let url = '/add_cash_transfer';

            if (this.transfer.transfer_id != 0) {
                url = '/update_cash_transfer';
            }

            axios.post(url, {
                transfer: this.transfer
            }).then(res => {
                let r = res.data;
                alert(r.message);
                if (r.success) {
                    location.reload();
                }
            })
        },
        editTransfer(item) {
            let keys = Object.keys(this.transfer);
            keys.forEach(key => {
                this.transfer[key] = item[key];
            })

            this.selectedEmployee = {
                Employee_SlNo: item.transfer_by,
                Employee_Name: item.transfer_by_name
            }

            this.selectedBranch = {
                brunch_id: item.transfer_to,
                Brunch_name: item.transfer_to_name
            }
        },
        deleteTransfer(id) {
            let deleteConf = confirm('Are you sure?');
            if (deleteConf == false) {
                return;
            }
            axios.post('/delete_cash_transfer', id).then(res => {
                let r = res.data;
                alert(r.message);
                if (r.success) {
                    this.getTransfers();
                }
            })
        }
    }
})
</script>