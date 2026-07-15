<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Annual Performance Assessment</title>
    <style>
        /* Scoped styles with ID prefix for uniqueness */

        #apa-modal html,
        #apa-modal body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: Times, "Times New Roman", serif;
            font-size: 10pt;
            background-color: #fff;
            color: #000;
        }

        #apa-modal td,
        #apa-modal th {
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        #apa-modal .page-container {
            margin: 0mm 15mm 15mm 15mm;
        }

        #apa-modal .page-header {
            margin-bottom: 20px;
            min-height: 70px;
        }

        #apa-modal .header-logo-container {
            text-align: right;
            padding-left: 80%;
        }

        #apa-modal .header-logo {
            width: 150px;
            height: 100px;
            display: block;
        }

        #apa-modal .company-name {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
            text-align: center;
        }

        #apa-modal .report-main-title {
            font-size: 10pt;
            font-weight: bold;
            margin: 0 0 4px 0;
            text-align: center;
        }

        #apa-modal .report-period {
            font-size: 9pt;
            text-align: center;
        }

        #apa-modal .section-main-title {
            font-weight: bold;
            font-size: 12pt;
            margin-top: 30px;
            margin-bottom: 5px;
            text-align: left;
        }

        #apa-modal .section-sub-title {
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            margin: 5px 0 2px;
        }

        #apa-modal .section-instruction {
            font-size: 9pt;
            text-align: center;
            margin-bottom: 15px;
        }

        #apa-modal .personal-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            line-height: 1.8;
        }

        #apa-modal .personal-data-table td {
            border: none;
            vertical-align: top;
            padding: 6px 0;
        }

        #apa-modal .label-col {
            width: 55%;
        }

        #apa-modal .colon-col {
            width: 3%;
            text-align: left;
        }

        #apa-modal .value-col {
            width: 42%;
        }

        #apa-modal .table-sub-note {
            padding-left: 25px;
            font-size: 9pt;
            line-height: 1.2;
        }

        #apa-modal .appraisal-question {
            font-size: 10pt;
            margin-top: 15px;
            margin-bottom: 8px;
            text-align: justify;
        }

        #apa-modal .appraisal-answer-space {
            width: 100%;
            margin-bottom: 15px;
            padding: 5px 12px;
            box-sizing: border-box;
            font-size: 10pt;
            text-align: justify;
            min-height: 50px;
        }

        #apa-modal .part3-main-instruction {
            font-size: 10pt;
            margin-bottom: 10px;
            text-align: center;
        }

        #apa-modal .part3-textarea-label {
            font-size: 11pt;
            margin-bottom: 5px;
            margin-top: 10px;
        }

        #apa-modal .part3-textarea {
            width: 100%;
            min-height: 150px;
            padding: 5px;
            font-size: 11pt;
            margin-bottom: 15px;
        }

        #apa-modal .attributes-table {
            width: 100%;
            font-size: 10pt;
            margin-top: 5px;
            margin-bottom: 0px !important;
            border-collapse: collapse;
        }

        #apa-modal .attributes-table tr {
            page-break-inside: avoid;
        }

        #apa-modal .attributes-table th,
        #apa-modal .attributes-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: middle;
        }

        #apa-modal .attributes-table th {
            background-color: #f0f0f0;
            padding: 15px 0;
            border: 1px solid #000;
        }

        #apa-modal .attribute-name {
            text-align: left;
        }

        #apa-modal .grading-header {
            font-size: 11pt;
            font-weight: bold;
        }

        #apa-modal .grading-list {
            list-style-type: disc;
            padding-left: 0px;
            margin-left: 0;
            font-size: 10pt;
            margin-bottom: 5px;
        }

        #apa-modal .grading-list li {
            margin: 5px 0;
            padding-left: 0px;
        }

        #apa-modal .grading-note {
            font-size: 11pt;
            margin-bottom: 10px;
        }

        #apa-modal table {
            width: 100%;
        }

        #apa-modal .final-assessment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-top: 5px;
        }

        #apa-modal .final-assessment-table th {
            border: 1px solid #000;
            padding: 15px 4px;
            background-color: #f0f0f0;
        }

        #apa-modal .final-assessment-table td {
            border: 1px solid #000;
        }

        #apa-modal .grading-container {
            page-break-before: auto;
            page-break-inside: avoid;
        }

        /* Modal wrapping container for scrollbar */
        #apa-modal .modal-content-container {
            max-height: 80vh;
            overflow-y: auto;
            margin: 0 auto;
            max-width: 842pt;
            box-sizing: border-box;
            background-color: white;
            padding: 0mm 15mm 15mm 15mm;
            outline: none;
        }
    </style>
</head>

<body>
    <div id="apa-modal" role="dialog" tabindex="0" aria-modal="true" aria-label="Annual Performance Assessment">
        <div class="modal-content-container">
            <div class="page-container" style="font-family:times;">
                <div class="page-header">
                    <?php if (!empty($arr_comp_contact_info['CompanyContactInfo']['logo'])): ?>
                        <div class="header-logo-container">
                            <img src="https://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . htmlspecialchars($arr_comp_contact_info['CompanyContactInfo']['logo']) ?>" class="header-logo" alt="Company Logo">
                        </div>
                    <?php endif; ?>
                    <div class="company-name">
                        <?= htmlspecialchars($arr_comp_contact_info['CompanyContactInfo']['business_name']); ?>
                    </div>
                    <div class="report-main-title">ANNUAL PERFORMANCE ASSESSMENT OF EXECUTIVES & SUPERVISORS</div>
                    <!-- edited by athira on 04-06-2025 -->
                    <div class="report-period">
                        Report for the year/period ending <?= htmlspecialchars($emp_data[0]['s']['fin_year']) . '-' . (htmlspecialchars($emp_data[0]['s']['fin_year']) + 1); ?>
                    </div>
                    <!-- end -->
                </div>

                <!-- PART I -->
                <div class="section-main-title">PART - I</div>
                <div class="section-sub-title">PERSONAL DATA</div>
                <div class="section-instruction">(To be filled by the HR Department)</div>

                <table class="personal-data-table">
                    <tr>
                        <td class="label-col">1. Name of the Employee</td>
                        <td class="colon-col">:</td>
                        <td class="value-col"><?= htmlspecialchars($emp_data[0]['e']['EmpName']) ?></td>
                    </tr>
                    <tr>
                        <td>2. Designation/Post held</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($emp_data[0]['s']['designation']) ?></td>
                    </tr>
                    <!-- edited by athira on 30-05-2025 -->
                    <tr>
                        <td>3. Date of birth</td>
                        <td>:</td>
                        <td><?= date('d-m-Y', strtotime($emp_data[0]['s']['dob'])) ?></td>
                    </tr>
                    <tr>
                        <td>4. Date of Joining</td>
                        <td>:</td>
                        <td><?= date('d-m-Y', strtotime($emp_data[0]['s']['doj'])) ?></td>
                    </tr>
                    <tr>
                        <td>5. Date of entry into present grade</td>
                        <td>:</td>
                        <td><?= date('d-m-Y', strtotime($emp_data[0]['s']['grade_entry_date'])) ?></td>
                    </tr>
                    <!-- end -->
                    <tr>
                        <td>6. Whether Permanent/Fixed Term Employment</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($emp_data[0]['s']['employment_type']) ?></td>
                    </tr>
                    <tr>
                        <td>7. Department/Section in which served during the year</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($emp_data[0]['s']['department']) ?></td>
                    </tr>
                    <tr>
                        <td>8. Period of absence from duty</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($emp_data[0]['s']['absence_period']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="table-sub-note">(without pay) during the year:</td>
                    </tr>
                </table>

                <!-- PART II -->
                <div class="section-main-title">PART - II</div>
                <div class="section-sub-title">SELF APPRAISAL</div>
                <div class="section-instruction">(To be filled by the Employee)</div>

                <div class="appraisal-question">1. Brief description of duties</div>
                <div class="appraisal-answer-space"><?= nl2br(htmlspecialchars($emp_data[0]['s']['duty_desc'])) ?></div>

                <div class="appraisal-question">2. Brief resume of the work done by you bringing out any special achievements during the year/period under review. In &nbsp;&nbsp;&nbsp;&nbsp;the event of shortfall in achievement furnish reasons. (The resume to be furnished within the space provided limited &nbsp;&nbsp;&nbsp;&nbsp;to 100 words and is required to be signed)</div>
                <div class="appraisal-answer-space"><?= nl2br(htmlspecialchars($emp_data[0]['s']['work_done_desc'])) ?></div>
            </div>
            <!-- Existing PART III ends here -->

            <!-- ADDITIONAL PART III SECTION (New Page) -->
            <?php
            $reportingData = null;
            $reviewingData = null;
            if (!empty($executive_summary)) {
                foreach ($executive_summary as $summary) {
                    $officerId = $summary['assessment_summary_executive']['officer_fkey']; // from assessment_summary_executive table

                    // Common employee info
                    $empName = $summary['ei']['EmpName'];
                    $designation = $summary['ei']['designation'];

                    if ($role === 'reporting_officer' && $officerId == $loggedInOfficerId) {
                        $reportingData = $summary['assessment_summary_executive'];
                        $reportingData['EmpName'] = $empName;
                        $reportingData['designation'] = $designation;
                    } elseif ($role === 'reviewing_officer') {
                        if ($officerId == $reportingOfficerId) {
                            $reportingData = $summary['assessment_summary_executive'];
                            $reportingData['EmpName'] = $empName;
                            $reportingData['designation'] = $designation;
                        } elseif ($officerId == $reviewingOfficerId) {
                            $reviewingData = $summary['assessment_summary_executive'];
                            $reviewingData['EmpName'] = $empName;
                            $reviewingData['designation'] = $designation;
                        }
                    }
                }
            }
            ?>
            <div class="page-container" style="font-family:times;margin-top:8mm;">
                <div class="section-main-title">PART - III</div>
                <div class="section-sub-title">ASSESSMENT OF THE REPORTING & REVIEWING OFFICER</div>
                <div class="part3-main-instruction">
                    <p style="margin:3px 0 0 0;padding:0;"><i>(Please assess the employee objectively based on overall performance during the period.</i>
                    <p style="margin:3px 0 0 0;padding:0;"><i>Each attribute carries 5 marks)</i></p>
                    </p>

                </div>

                <div class="part3-textarea-label">1. Does the Reporting Officer agree with the statement made in PART II? If not, the extent of disagreement &nbsp;&nbsp;&nbsp; and reasons thereof. </div>
                <div class="part3-textarea" style="padding:0 12px;"><?= htmlspecialchars($reportingData['agreement_comment']); ?></div>

                <?php
                $colCount = ($role === 'reviewing_officer') ? 3 : 2;

                if ($colCount == 2) {
                    $colWidths = ['70%', '30%'];
                } else {
                    $colWidths = ['60%', '20%', '20%'];
                }
                ?>

                <div class="part3-textarea-label">2. Attributes assessment</div>
                <table class="attributes-table">
                    <thead>
                        <tr>
                            <th style="text-align:center;width:<?= $colWidths[0] ?>;"><b>Attributes</b></th>
                            <th style="text-align:center;width:<?= $colWidths[1] ?>"><b>Reporting Officer</b></th>
                            <?php if ($role === 'reviewing_officer'): ?>
                                <th style="text-align:center;width:<?= $colWidths[2] ?>"><b>Reviewing Officer</b></th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($attributeMarks)):
                            foreach ($attributeMarks as $i => $attr):
                                // 'd' contains details, 'a' contains attribute label
                                $attributeDetails = $attr['d'];
                                $attributeLabel = $attr['a']['attributes'];

                                $reportingMark = isset($attributeDetails['reporting_officer_marks']) ? $attributeDetails['reporting_officer_marks'] : ' ';
                                $reviewingMark = isset($attributeDetails['reviewing_officer_marks']) ? $attributeDetails['reviewing_officer_marks'] : ' ';

                        ?>
                                <tr>
                                    <td style="width:<?= $colWidths[0] ?>" class="attribute-name">
                                        <?= ($i + 1) . '. ' . htmlspecialchars($attributeLabel) ?>
                                    </td>
                                    <td style="text-align:center;width:<?= $colWidths[1] ?>">
                                        <?= htmlspecialchars($reportingMark) ?>
                                    </td>
                                    <?php if ($role === 'reviewing_officer'): ?>
                                        <td style="text-align:center;width:<?= $colWidths[2] ?>"> <?= htmlspecialchars($reviewingMark) ?></td>
                                    <?php endif; ?>
                                </tr>

                        <?php endforeach;
                        endif; ?>



                        <tr>
                            <td style="text-align:center;background-color:#f0f0f0;"><b>Total Marks</b></td>
                            <td style="text-align:center;background-color:#f0f0f0;"><b> <?= htmlspecialchars($reportingData['total_marks']); ?></b></td>
                            <?php if ($role === 'reviewing_officer'): ?>
                                <td style="text-align:center;background-color:#f0f0f0;"><b><?= htmlspecialchars($reviewingData['total_marks']); ?></b></td>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <td style="text-align:center;background-color:#f0f0f0;"><b>Grade</b></td>
                            <td style="text-align:center;background-color:#f0f0f0;"><b> <?= htmlspecialchars($reportingData['grade']); ?></b></td>
                            <?php if ($role === 'reviewing_officer'): ?>
                                <td style="text-align:center;background-color:#f0f0f0;"><b><?= htmlspecialchars($reviewingData['grade']); ?></b></td>
                            <?php endif; ?>
                        </tr>
                        <?php if ($role === 'reviewing_officer'): ?>
                            <tr>
                                <td style="text-align:center;background-color:#f0f0f0;"><b>Final Marks and Grade (Reviewing Officer)</b></td>
                                <td colspan="2" style="text-align:center;background-color:#f0f0f0;">
                                    <b>
                                        <?= htmlspecialchars($reviewingData['total_marks']) . ', ' . htmlspecialchars($reviewingData['grade']); ?>
                                    </b>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

            <?php
            $colCount = ($role === 'reviewing_officer') ? 3 : 2;

            if ($colCount == 2) {
                $colWidths = ['50%', '50%'];
            } else {
                $colWidths = ['40%', '30%', '30%'];
            }
            ?>
            <!-- <div class="page-container" style="page-break-before: always; font-family: times;margin-top:8mm;"> -->
            <div class="page-container grading-container" style="font-family: times;margin-top:6mm;">
                <div class="grading-header">Grading</div>
                <ul class="grading-list">
                    <li>Outstanding &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Above 90 </li>
                    <li>Very Good &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp;Above 80 upto 90</li>
                    <li>Good &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp;Above 60 upto 80</li>
                    <li>Average &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;Above 40 upto 60</li>
                    <li>Below Average &nbsp;&nbsp;- &nbsp;&nbsp;&nbsp;&nbsp;Upto 40</li>
                </ul>
                <div class="grading-note">
                    ( An employee should not be graded outstanding unless exceptional qualities and performance have been noticed.
                    Grounds for giving outstanding / below average grading should be clearly brought out).
                </div>

                <table class="final-assessment-table" cellspacing="0" cellpadding="6" style="width:100%; border-collapse: collapse;">
                    <tr>
                        <th style="height: 10px; vertical-align: middle; width:<?= $colWidths[0] ?>;">Description</th>
                        <th style="height: 10px; vertical-align: middle; text-align:center; width:<?= $colWidths[1] ?>;">Reporting Officer</th>
                        <?php if ($role === 'reviewing_officer'): ?>
                            <th style="height: 10px; vertical-align: middle; text-align:center; width:<?= $colWidths[2] ?>;">Reviewing Officer</th>
                        <?php endif; ?>
                    </tr>

                    <?php
                    $rows = [
                        'Training Need Assessment' => ['reportingData' => $reportingData['training_need'], 'reviewingData' => $reviewingData['training_need']],
                        'Comments & Recommendation based on overall performance' => ['reportingData' => $reportingData['comments_recommendation'], 'reviewingData' => $reviewingData['comments_recommendation']],
                        'Signature' => ['reportingData' => '', 'reviewingData' => ''],
                        'Name' => ['reportingData' => $reportingData['EmpName'], 'reviewingData' => $reviewingData['EmpName']],
                        'Designation' => ['reportingData' => $reportingData['designation'], 'reviewingData' => $reviewingData['designation']],
                        // edited by athira on 02-06-2025
                        'Date' => ['reportingData' => $reportingDate, 'reviewingData' => $reviewingDate],
                        //end
                    ];

                    foreach ($rows as $label => $data):
                        $rowHeight = in_array($label, ['Comments & Recommendation based on overall performance']) ? '100px' : '40px';
                    ?>
                        <tr>
                            <td style="padding:5px;word-wrap: break-word; white-space: normal;height: <?= $rowHeight ?>;width:<?= $colWidths[0] ?>; vertical-align: top;"><?= $label ?></td>
                            <td style="padding:5px; word-wrap:break-word; white-space:pre-line; vertical-align:top; width:<?= $colWidths[1] ?>;">
                                <?= nl2br(htmlspecialchars($data['reportingData'])) ?>
                            </td>

                            <?php if ($role === 'reviewing_officer'): ?>
                                <td style="padding:5px; word-wrap:break-word; white-space:pre-line; vertical-align:top; width:<?= $colWidths[2] ?>;">
                                    <?= nl2br(htmlspecialchars($data['reviewingData'])) ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>

                </table>

            </div>
        </div>
        <div style="text-align: right; padding-top: 30px; padding-bottom: 30px; margin-right: 30px;">
            <button type="button" class="btn-danger" onclick="closeModal()" style="padding: 8px 16px; font-size: 11pt; background-color: #dc3545; border: none; color: white; border-radius: 4px; cursor: pointer;">
                Close
            </button>
        </div>
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