<script>
    $(document).ready(function () {
		/*console.log(dashBoardTimer);
		if(typeof dashBoardTimer != undefined){
			clearInterval(dashBoardTimer);
        }*/
		var url = livesite + 'CompanyNew/setup';

        var container = $("#modalDetailForm #modaldetails-content");
		$("#modalDetailForm").modal('show');
		$("#modalDetailForm").on('shown.bs.modal', function() {
			container.load(url, function () {
				console.log(url);
			});
		});
    });
</script>

<div id="modalDetailForm" class="modal fade" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modaldetails-content">

        </div>
    </div>
</div>