<section class="content-header">
    <h1 class="text-primary-18">Statutory PF Downloads</h1>
</section>
<hr>
<div class="col-md-3">
    <label class="col-md-5 control-label" for="filterby_month">Choose Month</label>
    <div class="col-md-7">
        <form class="form-horizontal" method="post" action="" id="form-showreport">
            <select id="filterby_month" name="reportfrom" onchange="hidebut();" class="form-control">

            <?php
            /*
             * By santhosh on 27 Dec 2015
             */
            $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
            for ($i = 0; $i < 10; $i++) {
                $month = date('Y-m', strtotime("-$i month", $start_month));
                if ($month == date('Y-m')) {
                    echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                } else {
                    echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                }
            }
            ?>
        </select>
        </form>
    </div>
</div>
<div class="col-md-6">
    <div class="col-md-12">
        <button class="btn btn-primary" onclick="download();">Generate</button>
        <div class="row col-md-6" id="downloadpf">
            
        </div>
</div>
</div>
<script>
    function hidebut(){
        $('#downloadpf').html('');
    }
    function download(){
        var month = $('#filterby_month').val();
        $('#downloadpf').load(livesite+ 'StatutoryUploads/pfdownload/'+month);
    }
</script>
