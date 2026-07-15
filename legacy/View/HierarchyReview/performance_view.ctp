<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Annual Performance Assessment</title>
    <style>
        html,
        #timesFontBody {
            font-family: Times, "Times New Roman", serif;
            font-size: 10pt;
            background-color: #fff;
            margin: 0;
            padding: 0;
            width: 100%;
        }


        td,
        th {
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            word-break: break-word;
            /* add this */
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
            padding-left: 80%;
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
            padding: 8px 0;
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


        .part3-textarea {
            width: 100%;
            min-height: 150px;
            padding: 5px;
            font-size: 11pt;
            margin-bottom: 15px;
        }

        .attributes-table {
            width: 100%;
            font-size: 8pt;
            margin-top: 5px;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .attributes-table tr {
            page-break-inside: avoid;
        }

        .attributes-table th,
        .attributes-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: middle;
        }

        .attributes-table th {
            background-color: #f0f0f0;
            padding: 10px 0;
            font-size: 10pt;
        }

        .attribute-name {
            text-align: left;
        }

        .grading-header {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 15px;
        }

        .grading-list {
            list-style-type: disc;
            padding-left: 0;
            margin-left: 0;
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .grading-list li {
            margin: 5px 0;
            padding-left: 0;
        }

        .grading-note {
            font-size: 11pt;
            margin-bottom: 10px;
        }

        .final-assessment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-top: 5px;
        }

        .final-assessment-table th,
        .final-assessment-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .final-assessment-table th {
            background-color: #f0f0f0;
        }

        .grading-container {
            page-break-before: auto;
            page-break-inside: avoid;
        }

        .scroll-wrapper {
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
</head>

<body id="timesFontBody">
    <div class="scroll-wrapper">
        <div class="page-container">
            <div class="page-container">
                <div class="page-header">
                    <?php if (!empty($arr_comp_contact_info['CompanyContactInfo']['logo'])): ?>
                        <div class="header-logo-container">
                            <img src="<?= htmlspecialchars($arr_comp_contact_info['CompanyContactInfo']['logo']) ?>" class="header-logo">
                        </div>
                    <?php endif; ?>

                    <div class="company-name">
                        <?= htmlspecialchars($arr_comp_contact_info['CompanyContactInfo']['business_name']); ?>
                    </div>
                    <div class="report-main-title">ANNUAL PERFORMANCE ASSESSMENT OF STAFFS</div>
                    <!-- edited by athira on 04-06-2025 -->
                    <div class="report-period">
                        Report for the year/period ending <?= htmlspecialchars($finYear) . '-' . (htmlspecialchars($finYear) + 1); ?>
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
                        <td class="value-col"><?= htmlspecialchars($emp_data[0]['ei']['EmpName']); ?></td>
                    </tr>
                    <tr>
                        <td>2. Designation/Post held</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($emp_data[0]['ei']['designation']); ?></td>
                    </tr>
                    <tr>
                        <td>3. Date of birth</td>
                        <td>:</td>
                        <td>
                            <?= !empty($emp_data[0]['ed']['date_of_birth']) ? htmlspecialchars(date('d-m-Y', strtotime($emp_data[0]['ed']['date_of_birth']))) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>4. Date of Joining</td>
                        <td>:</td>
                        <td>
                            <?= !empty($emp_data[0]['ei']['joining_date']) ? htmlspecialchars(date('d-m-Y', strtotime($emp_data[0]['ei']['joining_date']))) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>5. Date of entry into present grade</td>
                        <td>:</td>
                        <td>
                            <?= !empty($gradeEntryDate) ? htmlspecialchars(date('d-m-Y', strtotime($gradeEntryDate))) : ''; ?>
                        </td>
                    </tr>

                    <tr>
                        <td>6. Whether Permanent/Fixed Term Employment</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($emp_data[0]['ep']['emp_type']); ?></td>
                    </tr>
                    <tr>
                        <td>7. Department/Section in which served during the year</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($emp_data[0]['ei']['department']); ?></td>
                    </tr>
                    <tr>
                        <td>8. Period of absence from duty</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($total_lop); ?></td>
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
                <?php
                // Example: get current user and see if they're the reporting officer
                $loggedInUserId = $this->Session->read('emp_fkey');
                $details        = $assessmentDetail[0]['assessment_attributes_staff_details'];
                $isRepOfficer   = ($loggedInUserId == $details['reporting_officer']);
                // Edited by Akshay on 11-6-2025
                $isRevOfficer = (
                    $loggedInUserId == $details['reviewing_officer'] ||
                    ($loggedInUserId == $details['emp_fkey'] && $loggedInUserId !== $details['reporting_officer'])
                );
                // End
                ?>
                <table class="attributes-table">
                    <thead>
                        <tr>
                            <th style="width:8%;text-align:center;">Sl. NO. </th>
                            <th style="width:50%;text-align:center;">ATTRIBUTES</th>
                            <th style="width:42%;text-align:center;">REPORTING OFFICER</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sl = 1; ?>
                        <?php foreach ($attributeMarks as $row): ?>
                            <?php
                            $item = $row['i'];
                            $attr = $row['s'];
                            $data = $row['d'];
                            $ei1 = $row['ei1'];
                            $ei2 = $row['ei2'];
                            ?>
                            <tr>
                                <td style="text-align:center;"><b><?= $sl++ . "."; ?></b></td>
                                <td>
                                    <?= h($attr['attributes']) ?>
                                </td>
                                <td style="text-align:center;"><?= h($item['marks']) ?></td>
                            </tr>
                        <?php endforeach; ?>


                        <tr>
                            <td style="border-right:none;background-color:#f0f0f0;"></td>
                            <td style="text-align:center;background-color:#f0f0f0;">
                                <b>Total Marks</b>
                            </td>
                            <td style="text-align:center;background-color:#f0f0f0;">
                                <?= isset($data['reporting_officer_marks']) ? h($data['reporting_officer_marks']) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-right:none;background-color:#f0f0f0;"></td>
                            <td style="text-align:center;background-color:#f0f0f0;">
                                <b>Grade</b>
                            </td>
                            <td style="text-align:center;background-color:#f0f0f0;">
                                <?= isset($data['reporting_officer_grade']) ? h($data['reporting_officer_grade']) : '' ?>
                            </td>
                        </tr>
                        <!-- edited by athira on 04-06-2025 -->
                        <tr>
                            <td style="border-right:none;background-color:#f0f0f0;"></td>
                            <td style="text-align:center;background-color:#f0f0f0;">
                                <b>Final Marks and Grade</b>
                            </td>
                            <td style="text-align:center;background-color:#f0f0f0;">
                                <?= (isset($data['reporting_officer_marks']) ? h($data['reporting_officer_marks']) : '')
                                    . ', ' .
                                    (isset($data['reporting_officer_grade']) ? h($data['reporting_officer_grade']) : '')
                                ?>
                            </td>
                        </tr>
                        <!-- end -->
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
                <?php
                $colCount = ($isRevOfficer) ? 3 : 2;

                if ($colCount == 2) {
                    $colWidths = ['60%', '40%'];
                } else {
                    $colWidths = ['40%', '30%', '30%'];
                }
                ?>


                <table class="final-assessment-table" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse;">
                    <tr>
                        <th style="height: 10px; vertical-align: middle; text-align:center; width:<?= $colWidths[0] ?>;">Description</th>
                        <th style="height: 10px; vertical-align: middle; text-align:center; width:<?= $colWidths[1] ?>;">Reporting Officer</th>
                        <?php if ($isRevOfficer): ?>
                            <th style="height: 10px; vertical-align: middle; text-align:center; width:<?= $colWidths[2] ?>;">Reviewing Officer</th>
                        <?php endif; ?>
                    </tr>

                    <?php

                    $reportingDate = isset($data['reported_date']) && !empty($data['reported_date'])
                        ? date('d-m-Y', strtotime($data['reported_date']))
                        : '';

                    $reviewingDate = isset($data['reviewed_date']) && !empty($data['reviewed_date'])
                        ? date('d-m-Y', strtotime($data['reviewed_date']))
                        : '';

                    $rows = [
                        'Training Need Assessment' => [
                            'reportingData' => isset($data['reporting_officer_training_needs']) ? $data['reporting_officer_training_needs'] : '',
                            'reviewingData' => isset($data['reviewing_officer_training_needs']) ? $data['reviewing_officer_training_needs'] : '',
                        ],
                        'Comments & Recommendation based on overall performance' => [
                            'reportingData' => isset($data['reporting_officer_comments']) ? $data['reporting_officer_comments'] : '',
                            'reviewingData' => isset($data['reviewing_officer_comments']) ? $data['reviewing_officer_comments'] : '',
                        ],
                        'Signature' => [
                            'reportingData' => '',
                            'reviewingData' => '',
                        ],
                        'Name' => [
                            'reportingData' => isset($ei1['reporting_officer_name']) ? $ei1['reporting_officer_name'] : '',
                            'reviewingData' => isset($ei2['reviewing_officer_name']) ? $ei2['reviewing_officer_name'] : '',
                        ],
                        'Designation' => [
                            'reportingData' => isset($ei1['reporting_officer_designation']) ? $ei1['reporting_officer_designation'] : '',
                            'reviewingData' => isset($ei2['reviewing_officer_designation']) ? $ei2['reviewing_officer_designation'] : '',
                        ],
                        // edited by athira on 02-06-2025
                        'Date' => [
                            'reportingData' => isset($reportingDate) ? $reportingDate : '',
                            'reviewingData' => isset($reviewingDate) ? $reviewingDate : '',
                        ],
                        //end
                    ];



                    foreach ($rows as $label => $data):
                        $rowHeight = in_array($label, ['Comments & Recommendation based on overall performance']) ? '100px' : '40px';
                    ?>
                        <tr>
                            <td style="padding:5px;word-wrap: break-word; white-space: normal;height: <?= $rowHeight ?>;width:<?= $colWidths[0] ?>; vertical-align: top;"><?= $label ?></td>
                            <td style="padding:5px;word-wrap: break-word; white-space: normal;height: <?= $rowHeight ?>;width:<?= $colWidths[1] ?>; vertical-align: top;"><?= htmlspecialchars($data['reportingData']) ?></td>
                            <?php if ($isRevOfficer): ?>
                                <td style="padding:5px;word-wrap: break-word; white-space: normal;height: <?= $rowHeight ?>;width:<?= $colWidths[2] ?>; vertical-align: top;"><?= htmlspecialchars($data['reviewingData']) ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach;
                    // exit;
                    ?>

                </table>

            </div>
        </div>
    </div>
    <div style="text-align: right; padding-top: 30px; padding-bottom: 30px; margin-right: 30px;">
        <button type="button" class="btn-danger" onclick="closeModal()" style="padding: 8px 16px; font-size: 11pt; background-color: #dc3545; border: none; color: white; border-radius: 4px; cursor: pointer;">
            Close
        </button>
    </div>
</body>

</html>

<script>
    function closeModal() {
        // If this is within a modal, trigger its close
        if (window.parent && typeof window.parent.$ === 'function') {
            // If it's in an iframe or parent modal context
            window.parent.$('.modal').modal('hide');
        } else {
            // Otherwise, just hide the scroll-wrapper div
            document.querySelector('.scroll-wrapper').style.display = 'none';
        }
    }
</script>