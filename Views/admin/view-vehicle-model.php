<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <nav aria-label="breadcrumb" class="d-flex flex-row justify-content-between ">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="javascript:void(0);">Home</a>
        </li>
        <li class="breadcrumb-item">
          <a href="javascript:void(0);"><?=$menu?></a>
        </li>
        <li class="breadcrumb-item active"><?=$title?></li>
      </ol>
    </nav>
    <div class="card pt-2">
        <div class="card-body">
          <div class="dflexbtwn hdngvwall">
               <h4 class="cardhdng">Vehicle Model List</h4>
               <a href="<?= base_url(ADMINPATH . 'add-vehicle-model') ?>" class="sitebtn">Add</a>
          </div>
          <div class="table-responsive text-nowrap">
            <input type="hidden" value="0" id="totalRecords" />
            <table id="responseData" class="table  mb-0 ">
            </table>
          </div>
        </div>
    </div>
  </div>
  <div class="content-backdrop fade"></div>
</div>
<script>
    function getTotalRecordsData(qparam) {
        $.ajax({
            url: '<?= base_url(ADMINPATH . 'vehicle-model-data'); ?>?' + qparam,
            type: "POST",
            data: { 'is_count': 'yes', 'start': 0, 'length': 10 },
            cache: false,
            success: function (response) {
                $('#totalRecords').val(response);
                //if (response) {
                    loadAllRecordsData(qparam);
                //}
            }
        });
    }

    $(document).ready(function () {
        let qparam = (new URL(location)).searchParams;
        getTotalRecordsData(qparam);
    });

    function loadAllRecordsData(qparam) {
       // alert(qparam);
        $('#responseData').html('');
        var newQueryParam = '?'+qparam + '&recordstotal=' + $('#totalRecords').val();
        $('#responseData').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": '<?= base_url(ADMINPATH . 'vehicle-model-data'); ?>' + newQueryParam,
                "type": 'POST',
                dataSrc: (res) => {
                    return res.data
                }
            },
            "columns": [{ data: "sr_no", "name": "Sr.No", "title": "Sr.No" },
            { data: "", "title": "Model Details", "render": model_details },
            { data: "", "title": "Features", "render": features },
            { data: "", "title": "Dates","render":dates },
          <?php if(!empty($access) || ($user_type != "Role User") ){?>
            { data: "", "name": "Status", "title": "Status", "render": status_render },
           
            { data: "id", "name": "Action", "title": "Action", "render": action_render }
            <?php } ?>
          ],
             
            "rowReorder": { selector: 'td:nth-child(2)' },
            "responsive": false,
            "autoWidth": false,
            "destroy": true,
            "searchDelay": 500,
            "searching": true,
            "pagingType": 'simple_numbers',
            "rowId": (a) => { return 'id_' + a.id; },
            "iDisplayLength": 10,
            "order": [2, "asc"],
        });
    }

    var dates = (data, type, row, meta) => {
  var data = '';
  let add_date = row.add_date != null ? row.add_date : "";
  let update_date = row.update_date != null ? row.update_date : "";
  if (type === 'display') {
    data += '<span class="fotr_10"><b>Added On : </b>' + add_date + '</span><br>';
    data += '<span class="fotr_10"><b>Updated On: </b>' + update_date + '</span>';

  }
  return data;
}

var model_details = (data, type, row, meta) => {
  var data = '';
  let model_name = row.model_name != null ? row.model_name : "";
  let category_name = row.category_name != null ? row.category_name : "";
  let fuel_type = row.fuel_type != null ? row.fuel_type : "";
  let seat_segment = row.seat_segment != null ? row.seat_segment : "";
  if (type === 'display') {
    data += '<span class="fotr_10"><b>Model Name : </b>' + model_name + '</span><br>';
    data += '<span class="fotr_10"><b>Category Name: </b>' + category_name + '</span><br>';
    data += '<span class="fotr_10"><b>Fuel Type: </b>' + fuel_type + '</span><br>';
    data += '<span class="fotr_10"><b>Seat Segment: </b>' + seat_segment + '</span>';

  }
  return data;
}

var features = (data, type, row, meta) => {
  var data = '';
  let ac_or_non_ac = row.ac_or_non_ac != null ? row.ac_or_non_ac : "";
  let water_bottle = row.water_bottle != null ? row.water_bottle : "";
  let carrier = row.carrier != null ? row.carrier : "";
  let luggage = row.luggage != null ? row.luggage : "";
  if (type === 'display') {
    data += '<span class="fotr_10"><b>AC/Non AC : </b>' + ac_or_non_ac + '</span><br>';
    data += '<span class="fotr_10"><b>Water Bottle Name : </b>' + water_bottle + '</span><br>';
    data += '<span class="fotr_10"><b>Carrier : </b>' + carrier + '</span><br>';
    data += '<span class="fotr_10"><b>Luggage: </b>' + luggage + '</span>';

  }
  return data;
}

function action_render(data, type, row, meta) {
  let output = '';
  if (type === 'display') {
    var onclick = "remove('" + row.id + "','dt_vehicle_model')";
    output = '<a href="<?= base_url(ADMINPATH . "add-vehicle-model?id=") ?>' + row.id + '" class="btn actnbtn btn-orange" title="Edit Vehicle Model"><i class="tf-icons bx bx-edit"></i></a> ';
    output += '<a class="btn actnbtn btn-red" onclick="' + onclick + '" title="Delete Vehicle Model"><i class="tf-icons bx bx-trash"></i></a> ';
  }
  return output;
}

function status_render(data, type, row, meta) {
  if (type === 'display') {
    const isChecked = row.status === 'Active';
    const label = isChecked ? 'Active' : 'Inactive';
    const id = `tableswitch5${row.id}`;
    const onchange = `change_status(${row.id}, 'dt_vehicle_model')`;

    return `<div class="custom-control custom-switch form-switch">
                <input type="checkbox" onchange="${onchange}" ${isChecked ? 'checked' : ''} class="custom-control-input form-check-input" id="${id}" role="switch">
                <label class="custom-control-label" for="${id}" id="status_label${row.id}">${label}</label>
            </div> `;
  }
  return '';
}

</script>