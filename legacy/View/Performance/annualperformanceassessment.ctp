<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Annual Performance Assessment</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: Times, "Times New Roman", serif;
            font-size: 10pt;
            background-color: #fff;
        }

        td,
        th {
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        .page-container {
            margin: 0mm 15mm 15mm 15mm;
        }

        .page-header {
            margin-bottom: 20px;
            min-height: 70px;
        }

        .header-logo-container {
            text-align: right;
        }

        .header-logo {
            width: 150px;
            height: 100px;
            display: block;
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
            text-align: center;
        }

        .report-main-title {
            font-size: 10pt;
            font-weight: bold;
            margin: 0 0 4px 0;
            text-align: center;
        }

        .report-period {
            font-size: 9pt;
            text-align: center;
        }

        .section-main-title {
            font-weight: bold;
            font-size: 12pt;
            margin-top: 30px;
            margin-bottom: 5px;
            text-align: left;
        }

        .section-sub-title {
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            margin: 5px 0 2px;
        }

        .section-instruction {
            font-size: 9pt;
            text-align: center;
            margin-bottom: 15px;
        }

        .personal-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            line-height: 1.8;
        }

        .personal-data-table td {
            border: none;
            vertical-align: top;
            padding: 6px 0;
        }

        .label-col {
            width: 55%;
        }

        .colon-col {
            width: 3%;
            text-align: left;
        }

        .value-col {
            width: 42%;
        }

        .table-sub-note {
            padding-left: 25px;
            font-size: 9pt;
            line-height: 1.2;
        }

        .appraisal-question {
            font-size: 10pt;
            margin-top: 15px;
            margin-bottom: 8px;
            text-align: justify;
        }

        .appraisal-answer-space {
            width: 100%;
            margin-bottom: 15px;
            padding: 5px 12px;
            box-sizing: border-box;
            font-size: 10pt;
            text-align: justify;
            min-height: 50px;
        }

        .part3-main-instruction {
            font-size: 10pt;
            margin-bottom: 10px;
            text-align: center;
        }

        .part3-textarea-label {
            font-size: 11pt;
            margin-bottom: 5px;
            margin-top: 10px;
        }

        .part3-textarea {
            width: 100%;
            min-height: 150px;
            padding: 5px;
            font-size: 11pt;
            margin-bottom: 15px;

        }

        .attributes-table {
            width: 100%;
            font-size: 10pt;
            margin-top: 5px;
            margin-bottom: 0px !important;
            border-collapse: collapse;
        }

        .attributes-table tr {
            page-break-inside: avoid;
        }


        .attributes-table th,
        .attributes-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: middle;
        }

        .attributes-table th {
            background-color: #f0f0f0;
            padding: 15px 0;
            border: 1px solid #000;
        }

        .attribute-name {
            text-align: left;
        }

        .grading-header {
            font-size: 11pt;
            font-weight: bold;
            /* margin-top: 8px; */
        }

        .grading-list {
            list-style-type: disc;
            /* padding-left: 20px; */
            padding-left: 0px;
            margin-left: 0;
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .grading-list li {
            margin: 5px 0;
            padding-left: 0px;
        }

        .grading-note {
            font-size: 11pt;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
        }

        .final-assessment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-top: 5px;
        }

        .final-assessment-table th {
            border: 1px solid #000;
            padding: 15px 4px;

        }

        .final-assessment-table td {
            border: 1px solid #000;
            padding: 8px 4px;

        }

        .final-assessment-table th {
            background-color: #f0f0f0;
        }

        .grading-container {
            page-break-before: auto;
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <!-- edited by athira on 10-06-2025 -->
    <?php if (empty($structured_data) && empty($structured_staff_review)) { ?>
        <div class="page-container" style="margin-top:50px;">No data found for selected criteria</div>
    <?php  } ?>
    <!-- end -->
    <?php foreach ($structured_data as $emp_data) { ?>
        <div class="page-container" style="font-family:times; page-break-after: always;">

            <div class="page-header">
                <?php if (!empty($company_info['0']['comp_contact_info']['logo'])): ?>
                    <div class="header-logo-container">
                        <img src="<?= htmlspecialchars($company_info['0']['comp_contact_info']['logo']) ?>" class="header-logo">
                    </div>
                <?php endif; ?>
                <div class="company-name">
                    <?= isset($company_info['0']['comp_contact_info']['business_name']) ? htmlspecialchars($company_info['0']['comp_contact_info']['business_name']) : '' ?>
                </div>
                <div class="report-main-title">ANNUAL PERFORMANCE ASSESSMENT OF EXECUTIVES & SUPERVISORS</div>
                <div class="report-period">
                    Report for the year/period ending
                    <?php
                    if (isset($emp_data['fin_year']) && is_numeric($emp_data['fin_year'])) {
                        echo htmlspecialchars($emp_data['fin_year']) . '-' . ($emp_data['fin_year'] + 1);
                    }
                    ?>
                </div>
            </div>

            <!-- PART I -->
            <div class="section-main-title">PART - I</div>
            <div class="section-sub-title">PERSONAL DATA</div>
            <div class="section-instruction">(To be filled by the HR Department)</div>
            <table class="personal-data-table">
                <tr>
                    <td>1. Name of the Employee</td>
                    <td> : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td><?= isset($emp_data['full_name']) ? htmlspecialchars($emp_data['full_name']) : '' ?></td>
                </tr>
                <tr>
                    <td>2. Designation/Post held</td>
                    <td> : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td><?= isset($emp_data['designation']) ? htmlspecialchars($emp_data['designation']) : '' ?></td>
                </tr>
                <tr>
                    <td>3. Date of birth</td>
                    <td> : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td><?= isset($emp_data['dob']) && strtotime($emp_data['dob']) ? date('d-m-Y', strtotime($emp_data['dob'])) : '' ?></td>
                </tr>
                <tr>
                    <td>4. Date of Joining</td>
                    <td> : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td><?= isset($emp_data['doj']) && strtotime($emp_data['doj']) ? date('d-m-Y', strtotime($emp_data['doj'])) : '' ?></td>
                </tr>
                <tr>
                    <td>5. Date of entry into present grade</td>
                    <td> : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td><?= isset($emp_data['grade_entry_date']) && strtotime($emp_data['grade_entry_date']) ? date('d-m-Y', strtotime($emp_data['grade_entry_date'])) : ' ' ?></td>
                </tr>
                <tr>
                    <td>6. Whether Permanent/Fixed Term Employment</td>
                    <td> : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td><?= isset($emp_data['employment_type']) ? htmlspecialchars($emp_data['employment_type']) : '' ?></td>
                </tr>
                <tr>
                    <td>7. Department/Section in which served during the year</td>
                    <td> : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td><?= isset($emp_data['department']) ? htmlspecialchars($emp_data['department']) : '' ?></td>
                </tr>
                <tr>
                    <td>8. Period of absence from duty</td>
                    <td> : &nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td><?= isset($emp_data['absence_period']) ? htmlspecialchars($emp_data['absence_period']) : '' ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="table-sub-note">(without pay) during the year : </td>
                </tr>
            </table>

            <!-- PART II -->
            <div class="section-main-title">PART - II</div>
            <div class="section-sub-title">SELF APPRAISAL</div>
            <div class="section-instruction">(To be filled by the Employee)</div>
            <div class="appraisal-question">1. Brief description of duties</div>
            <div class="appraisal-answer-space"><?= isset($emp_data['duty_desc']) ? nl2br(htmlspecialchars($emp_data['duty_desc'])) : '' ?></div>
            <div class="appraisal-question">2. Brief resume of the work done by you bringing out any special achievements during the year/period under review. In
                &nbsp;&nbsp;&nbsp;&nbsp;the event of shortfall in achievement furnish reasons. (The resume to be furnished within the space provided limited
                &nbsp;&nbsp;&nbsp;&nbsp;to 100 words and is required to be signed)
            </div>
            <div class="appraisal-answer-space"><?= isset($emp_data['work_done_desc']) ? nl2br(htmlspecialchars($emp_data['work_done_desc'])) : '' ?></div>

        </div>
        <div class="page-container" style="font-family: times;margin-top:8mm;">
            <div class="section-main-title">PART - III</div>
            <div class="section-sub-title">ASSESSMENT OF THE REPORTING & REVIEWING OFFICER</div>
            <div class="part3-main-instruction">
                <p><i>(Please assess the employee objectively based on overall performance during the period. Each attribute carries 5 marks)</i></p>
            </div>
            <div class="part3-textarea-label">1. Does the Reporting Officer agree with the statement made in PART II ? </div>
            <div class="part3-textarea" style="padding:0 12px;"><?= htmlspecialchars($emp_data['summary']['reporting_officer']['comments_recommendation']) ?></div>
            <div class="part3-textarea-label">2. Attributes assessment</div>
            <table class="attributes-table">
                <thead>
                    <tr>
                        <th style="text-align:center;width:60%;"><b>Attributes</b></th>
                        <th style="text-align:center;width:20%;"><b>Reporting Officer</b></th>
                        <th style="text-align:center;width:20%;"><b>Reviewing Officer</b></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    <?php foreach ($emp_data['attributes'] as $attribute) { ?>
                        <tr>
                            <td style="width:60%;"><?= $i . '. ' . htmlspecialchars($attribute['attribute']) ?></td>
                            <td style="text-align:center;width:20%;"><?= htmlspecialchars($attribute['marks_given_by_reporting_officer']) ?></td>
                            <td style="text-align:center;width:20%;"><?= htmlspecialchars($attribute['marks_given_by_reviewing_officer']) ?></td>
                        </tr>
                    <?php $i++;
                    } ?>
                    <tr>
                        <td style="text-align:center;background-color:#f0f0f0;"><b>Total Marks</b></td>
                        <td style="text-align:center;background-color:#f0f0f0;"><b><?= htmlspecialchars($emp_data['summary']['reporting_officer']['total_marks']) ?></b></td>
                        <td style="text-align:center;background-color:#f0f0f0;"><b><?= htmlspecialchars($emp_data['summary']['reviewing_officer']['total_marks']) ?></b></td>
                    </tr>
                    <tr>
                        <td style="text-align:center;background-color:#f0f0f0;"><b>Grade</b></td>
                        <td style="text-align:center;background-color:#f0f0f0;"><b><?= htmlspecialchars($emp_data['summary']['reporting_officer']['grade']) ?></b></td>
                        <td style="text-align:center;background-color:#f0f0f0;"><b><?= htmlspecialchars($emp_data['summary']['reviewing_officer']['grade']) ?></b></td>
                    </tr>

                    <tr>
                        <td style="text-align:center;background-color:#f0f0f0;"><b>Final Marks and Grade (Reviewing Officer)</b></td>
                        <td style="text-align:center;background-color:#f0f0f0;" colspan="2"><b><?= htmlspecialchars($emp_data['summary']['reviewing_officer']['total_marks']) . " , " . htmlspecialchars($emp_data['summary']['reviewing_officer']['grade']) ?></b></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="page-container grading-container" style="font-family: times;margin-top:6mm;">
            <div class="grading-header">Grading</div>
            <ul class="grading-list">
                <li>Outstanding - Above 90</li>
                <li>Very Good - Above 80 upto 90</li>
                <li>Good - Above 60 upto 80</li>
                <li>Average - Above 40 upto 60</li>
                <li>Below Average - Upto 40</li>
            </ul>
            <div class="grading-note">
                ( An employee should not be graded outstanding unless exceptional qualities and performance have been noticed.
                Grounds for giving outstanding / below average grading should be clearly brought out.)
            </div>



            <table class="final-assessment-table" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width:30%;"></th>
                        <th style="text-align:center;width:35%">Reporting Officer</th>
                        <th style="text-align:center;width:35%">Reviewing Officer</th>
                    </tr>
                </thead>
                <tr>
                    <td style="vertical-align: top; min-height: 60px; width: 30%; white-space: pre-wrap; overflow-wrap: break-word;">Training Need Assessment</td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= htmlspecialchars($emp_data['summary']['reporting_officer']['training_need']) ?>
                    </td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= htmlspecialchars($emp_data['summary']['reviewing_officer']['training_need']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; min-height: 60px; width: 30%; white-space: pre-wrap; overflow-wrap: break-word;">Comments & Recommendation based on <br> overall performance</td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= htmlspecialchars($emp_data['summary']['reporting_officer']['comments_recommendation']) ?>
                    </td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= htmlspecialchars($emp_data['summary']['reviewing_officer']['comments_recommendation']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; min-height: 60px; width: 30%; white-space: pre-wrap; overflow-wrap: break-word;">Signature</td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">

                    </td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">

                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; min-height: 60px; width: 30%; white-space: pre-wrap; overflow-wrap: break-word;">Name</td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= htmlspecialchars($emp_data['reporting_officer_name']) ?>
                    </td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= htmlspecialchars($emp_data['reviewing_officer_name']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; min-height: 60px; width: 30%; white-space: pre-wrap; overflow-wrap: break-word;">Designation</td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= htmlspecialchars($emp_data['reporting_officer_designation']) ?>
                    </td>
                    <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= htmlspecialchars($emp_data['reviewing_officer_designation']) ?>
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align: top; padding: 8px; width: 30%; white-space: pre-wrap; overflow-wrap: break-word;">Date</td>
                    <td style="vertical-align: top; padding: 8px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= !empty($emp_data['reported_date']) && strtotime($emp_data['reported_date']) ? date("d-m-Y", strtotime($emp_data['reported_date'])) : " " ?>
                    </td>
                    <td style="vertical-align: top; padding: 8px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                        <?= !empty($emp_data['reviewed_date']) && strtotime($emp_data['reviewed_date']) ? date("d-m-Y", strtotime($emp_data['reviewed_date'])) : " " ?>
                    </td>
                </tr>

            </table>
        </div>

    <?php } ?>

    <div style="font-family: times;page-break-after: always;">
        <!-- STAFF REVIEW SECTION -->
        <?php foreach ($structured_staff_review as $staff_data) { ?>
            <div class="page-container" style="page-break-before: always; font-family:times;">
                <div class="page-header">
                    <?php if (!empty($company_info['0']['comp_contact_info']['logo'])): ?>
                        <div class="header-logo-container">
                            <img src="<?= htmlspecialchars($company_info['0']['comp_contact_info']['logo']) ?>" class="header-logo">
                        </div>
                    <?php endif; ?>

                    <div class="company-name">
                        <?= htmlspecialchars($company_info['0']['comp_contact_info']['business_name']); ?>
                    </div>
                    <div class="report-main-title">ANNUAL PERFORMANCE ASSESSMENT OF STAFFS</div>
                    <!-- edited by athira on 04-06-2025 -->
                    <div class="report-period">
                        Report for the year/period ending <?= htmlspecialchars($staff_data['fin_year']) . '-' . (htmlspecialchars($staff_data['fin_year']) + 1); ?>
                    </div>
                    <!-- end -->
                </div>

                <div class="section-main-title">PART - I</div>
                <div class="section-sub-title">PERSONAL DATA</div>
                <div class="section-instruction">(To be filled by the HR Department)</div>
                <table class="personal-data-table">

                    <tr>
                        <td class="label-col">1. Name of the Employee</td>
                        <td class="colon-col">:</td>
                        <td class="value-col"><?= htmlspecialchars($staff_data['full_name']); ?></td>
                    </tr>
                    <tr>
                        <td>2. Designation/Post held</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($staff_data['designation']); ?></td>
                    </tr>
                    <tr>
                        <td>3. Date of birth</td>
                        <td>:</td>
                        <td>
                            <?= !empty($staff_data['dob']) ? htmlspecialchars(date('d-m-Y', strtotime($staff_data['dob']))) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>4. Date of Joining</td>
                        <td>:</td>
                        <td>
                            <?= !empty($staff_data['doj']) ? htmlspecialchars(date('d-m-Y', strtotime($staff_data['doj']))) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>5. Date of entry into present grade</td>
                        <td>:</td>
                        <td>
                            <?= !empty($staff_data['grade_entry_date']) ? htmlspecialchars(date('d-m-Y', strtotime($staff_data['grade_entry_date']))) : ''; ?>
                        </td>
                    </tr>

                    <tr>
                        <td>6. Whether Permanent/Fixed Term Employment</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($staff_data['employment_type']); ?></td>
                    </tr>
                    <tr>
                        <td>7. Department/Section in which served during the year</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($staff_data['department']); ?></td>
                    </tr>
                    <tr>
                        <td>8. Period of absence from duty</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($staff_data['absence_period']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="table-sub-note">(without pay) during the year:</td>
                    </tr>
                </table>



                <div class="section-main-title">PART - II</div>
                <div class="section-sub-title">ASSESSMENT OF THE REPORTING OFFICER</div>
                <div class="part3-main-instruction">
                    <p style="margin:3px 0 0 0;padding:0;"><i>(Please assess the employee objectively based on over all performance during the period.</i>
                    <p style="margin:3px 0 0 0;padding:0;"><i>Each attributes carry 10 marks)</i></p>
                    </p>

                </div>
                <table class="attributes-table" align="center">
                    <thead>
                        <tr>
                            <th style="width:10%;text-align:center;">Sl. NO. </th>
                            <th style="width:50%;text-align:center;">ATTRIBUTES</th>
                            <th style="width:40%;text-align:center;">REPORTING OFFICER</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($staff_data['attributes'] as $attribute) { ?>
                            <tr>
                                <td style="width:10%;text-align:center;"><?php echo $i; ?></td>
                                <td style="width:50%;"><?= htmlspecialchars($attribute['attribute']) ?></td>
                                <td style="text-align:center;width:40%;"><?= htmlspecialchars($attribute['marks_given_by_reporting_officer']) ?></td>
                            </tr>
                        <?php $i++;
                        } ?>


                        <tr>
                            <td style="text-align:center;background-color:#f0f0f0;"></td>
                            <td style="text-align:center;background-color:#f0f0f0;">
                                <b>Total Marks</b>
                            </td>
                            <td style="text-align:center;background-color:#f0f0f0;"><b><?= htmlspecialchars($staff_data['summary']['reporting_total_marks']); ?></b></td>
                        </tr>
                        <tr>
                            <td style="text-align:center;background-color:#f0f0f0;"></td>
                            <td style="text-align:center;background-color:#f0f0f0;">
                                <b>Grade</b>
                            </td>
                            <td style="text-align:center;background-color:#f0f0f0;"><b><?= htmlspecialchars($staff_data['summary']['reporting_total_grade']); ?></b></td>
                        </tr>
                        <tr>
                            <td style="text-align:center;background-color:#f0f0f0;"></td>
                            <td style="text-align:center;background-color:#f0f0f0;">
                                <b>Final Marks and Grade (Reporting Officer)</b>
                            </td>

                            <td style="text-align:center;background-color:#f0f0f0;" colspan="2"><b><?= htmlspecialchars($staff_data['summary']['reporting_total_marks']) . " , " . htmlspecialchars($staff_data['summary']['reporting_total_grade']) ?></b></td>

                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="page-container grading-container" style="font-family: times;margin-top:6mm;">
                <div class="grading-header">Grading</div>
                <ul class="grading-list">
                    <li>Outstanding - Above 90</li>
                    <li>Very Good - Above 80 upto 90</li>
                    <li>Good - Above 60 upto 80</li>
                    <li>Average - Above 40 upto 60</li>
                    <li>Below Average - Upto 40</li>
                </ul>
                <div class="grading-note">
                    (An employee should not be graded outstanding unless exceptional qualities and performance have been noticed.
                    Grounds for giving outstanding / below average grading should be clearly brought out).
                </div>


                <table class="final-assessment-table" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse;" align="center">
                    <tr>
                        <th style="height: 10px; vertical-align: middle;width:30%; "></th>
                        <th style="height: 10px; vertical-align: middle; text-align:center;width:35%; ">Reporting Officer</th>
                        <th style="height: 10px; vertical-align: middle; text-align:center; width:35%;">Reviewing Officer</th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; min-height: 60px; width:30%; white-space: pre-wrap; overflow-wrap: break-word;">Training Need Assessment</td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= htmlspecialchars($staff_data['summary']['training_needs']['reporting']) ?>
                        </td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= htmlspecialchars($staff_data['summary']['training_needs']['reviewing']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; min-height: 60px; width:30%; white-space: pre-wrap; overflow-wrap: break-word;">Comments & Recommendation based on <br> overall performance</td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= htmlspecialchars($staff_data['summary']['comments']['reporting']) ?>
                        </td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= htmlspecialchars($staff_data['summary']['comments']['reviewing']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; min-height: 60px; width:30%; white-space: pre-wrap; overflow-wrap: break-word;">Signature</td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">

                        </td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">

                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; min-height: 60px; width:30%; white-space: pre-wrap; overflow-wrap: break-word;">Name</td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= htmlspecialchars($staff_data['reporting_officer']['name']) ?>
                        </td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= htmlspecialchars($staff_data['reviewing_officer']['name']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; min-height: 60px; width:30%; white-space: pre-wrap; overflow-wrap: break-word;">Designation</td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= htmlspecialchars($staff_data['reporting_officer']['designation']) ?>
                        </td>
                        <td style="vertical-align: top; min-height: 60px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= htmlspecialchars($staff_data['reviewing_officer']['designation']) ?>
                        </td>
                    </tr>

                    <tr>
                        <td style="vertical-align: top; padding: 8px; width:30%; white-space: pre-wrap; overflow-wrap: break-word;">Date</td>
                        <td style="vertical-align: top; padding: 8px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= !empty($staff_data['reported_date']) && strtotime($staff_data['reported_date']) ? date("d-m-Y", strtotime($staff_data['reported_date'])) : " " ?>
                        </td>
                        <td style="vertical-align: top; padding: 8px; width: 35%; white-space: pre-wrap; overflow-wrap: break-word;">
                            <?= !empty($staff_data['reviewed_date']) && strtotime($staff_data['reviewed_date']) ? date("d-m-Y", strtotime($staff_data['reviewed_date'])) : " " ?>
                        </td>
                    </tr>

                </table>

            </div>

        <?php } ?>
    </div>
</body>

</html>