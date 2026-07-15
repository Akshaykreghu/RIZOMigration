<style>
	body {
		font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
	}

	
	

	#shiftpolicygrouptable_wrapper .DTTT.btn-group,
	#shiftpolicytable_wrapper .DTTT.btn-group {
		padding-left: 5px;
	}

	.heading {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
	}

	.home {
		background-color: #1e516e;
		border-radius: 50px;
		padding: 4px 15px;
		color: white;
		font-weight: 500;
		text-decoration: none;
		transition: all 0.3s ease;
		cursor: pointer;
	}

	.btns {
		background-color: #fff !important;
		border: 1px solid #e0e0e0 !important;
		color: #555 !important;
		border-radius: 8px !important;
		font-weight: 600 !important;
		font-size: 13px;
		padding: 8px 16px;
		margin-right: 10px;
		display: flex;
		align-items: center;
		gap: 6px;
		transition: all 0.2s ease;
		cursor: pointer;
	}

	.datagrid-cell {
		font-size: 14px !important;
		font-weight: normal !important;
	}

	.datagrid-header .datagrid-cell span {
		font-size: 15px !important;
		font-weight: 600 !important;
	}

	.datagrid-btable td:not(:first-child),
	.datagrid-htable td:not(:first-child) {
    
    text-align: center;
}


	.modal-dialog {
	width: 60% !important;
	height: 100vh !important;        /* Viewport height */
	margin: 20px auto;              /* Center it nicely */
}

.modal-content {
	height: 100% !important;
	display: flex;
	flex-direction: column;
}

.modal-header {
	flex-shrink: 0;
	padding: 12px 30px;
	font-size:18px !important;
	font-weight:600;
}

.modal-body {
	flex-grow: 1;
	overflow-y: auto;  
	padding:15px;             /* Scroll only inner content if needed */

}

.modal-footer {
	flex-shrink: 0;
}
.heading {
		display: flex;
		flex-direction: row;
		align-items: end;
		justify-content: space-between;
	}

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }

</style>

<section class="content-header heading">
	<h1 class="text-primary-18">Shift Timings & Rules</h1>
	
	  <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

</section>

<hr style="margin-top: 8px; margin-bottom: -2px; margin-right: 15px; margin-left: 15px;">

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<!-- Toolbar -->
			<div style="display:flex;justify-content:left;align-items:center;margin-bottom:10px;">
				<button class="btns" id="btnNew">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
						class="bi bi-plus-lg" viewBox="0 0 16 16">
						<path fill-rule="evenodd"
							d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2">
						</path>
					</svg>
					Add Shift
				</button>
				<button class="btns" id="btnView"><i class="fa fa-pencil"></i>View</button>
				<button class="btns" id="btnRemove"><i class="fa fa-times" style="color:#dc1010;"></i>Remove</button>
			</div>

			<!-- Full width grid -->
			<ul id="shiftpolicygrouptable" style="width: 100%; min-height: 300px;"></ul>
		</div>
	</div>
</section>

<!-- Modal -->
<div class="modal fade" id="shiftModal" tabindex="-1" role="dialog" aria-labelledby="shiftModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content" >
			<div class="modal-header" style="background:#1e516e;color:white;">
				<div style="display:flex;justify-content:space-between;align-items:center;">
				<h4 class="modal-title" id="shiftModalLabel">Shift Details</h4>
				<button type="button" class="close" id="closeShiftModal" aria-label="Close" style="color:white;">
    <span aria-hidden="true">&times;</span>
</button>
			</div>
			</div>
			<div class="modal-body" id="shiftContainer">
				<!-- shift form loads dynamically -->
			</div>
		</div>
	</div>
</div>

<script>
	function loader(loader) {
		$('#' + loader).html('<li class="fa fa-spinner fa-spin" style="margin: 50%; font-size: 3em;"></li>');
	}

	function stoploader(loader) {
		$('#' + loader).html('');
	}

	function pauseloader(cont) {
		$('#' + cont).find('li').removeClass('fa-spin');
	}

	jQuery(document).ready(function () {

		// Initialize datagrid
		$('#shiftpolicygrouptable').datagrid({
			url: livesite + "DayTimeProcedure/listpolicies",
			pagination: true,
			singleSelect: true,
			rownumbers: true,
			fitColumns: true,
			pageList: [2, 5, 10, 50, 100],
			columns: [[
				{ field: 'day_time_desc', title: 'Shift Name', width: '30%', sortable: true },
				{ field: 'on_dutty1', title: 'In Time', width: '15%', sortable: true },
				{ field: 'off_dutty1', title: 'Out Time', width: '15%', sortable: true },
				{ field: 'minutes_per_half', title: 'Half Day (Min)', width: '20%', sortable: true },
				{ field: 'minuts_calc_perday', title: 'Full Day (Min)', width: '20%', sortable: true },
			]],
			onLoadSuccess: function () {
				// no auto-load form now
			}
		});

		// ➕ Add Shift
		$("#btnNew").on("click", function () {
			loader('shiftContainer');
			$("#shiftContainer").load(livesite + "DayTimeProcedure/form", function () {
				$('#shiftModal').modal('show');
			});
		});

		// ✏️ View selected shift
		$("#btnView").on("click", function () {
			var row = $('#shiftpolicygrouptable').datagrid('getSelected');
			if (!row) {
				alert("Please select a shift to view");
				return;
			}
			loader('shiftContainer');
			$("#shiftContainer").load(livesite + 'DayTimeProcedure/lists?id=' + row.day_time_seq, function () {
				$('#shiftModal').modal('show');
			});
		});

		// ❌ Remove shift
		$("#btnRemove").on("click", function () {
			var row = $('#shiftpolicygrouptable').datagrid('getSelected');
			if (!row) {
				alert("Please select a shift to remove");
				return;
			}
			if (confirm("Are you sure you want to delete this shift?")) {
				$.ajax({
					url: livesite + "DayTimeProcedure/delete",
					data: { ids: row.day_time_seq },
					success: function (response) {
						response = $.parseJSON(response);
						$.notify(response.msg, {
							type: response.success ? 'success' : 'danger',
							allow_dismiss: true
						});
						if (response.success) {
							reloadTable('shiftpolicygrouptable');
							$('#shiftModal').modal('hide');
						}
					},
					error: function (response) {
						$.notify($.parseJSON(response).msg, { type: 'danger', allow_dismiss: false });
					}
				});
			}
		});

		// Back to Home
		$(".home").on("click", function () {
			$("#container").isLoading({ text: "Loading", position: "overlay" });
			$("#container").load(livesite + "CompanySetup/index", function () {
				isDashboardShown = false;
			});
		});
		//edited by athira on 08-11-2025
		function cleanupTempExceptions(callback) {
        $.ajax({
            url: '/DayTimeProcedure/deleteTempExceptions',
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                console.log(res.msg || 'Temporary exceptions cleaned.');
                if (typeof callback === "function") callback();
            },
            error: function() {
                console.log('Error while cleaning up exceptions.');
                if (typeof callback === "function") callback();
            }
        });
    }

    // When close button is clicked
    $('#closeShiftModal').on('click', function() {
        cleanupTempExceptions(function() {
            // Close the modal only after cleanup finishes
            $('#shiftModal').modal('hide');
        });
    });
	//end

	});
	/* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "CompanySetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 19-02-26 */
	
</script>

