<style>
    /* Basic Reset and Page Setup (Keep your existing styles here) */
    html,
    body {
        margin: 0;
        padding: 0;
        width: 100%;
        font-family: Times, "Times New Roman", serif;
        /* Global font */
        font-size: 10pt;
        /* Default base font size, can be overridden */
        background-color: #fff;
        /* Ensure white background for PDF */
    }

    .page-container {
        margin: 0mm 15mm 15mm 15mm;
        /* Your UNIFORM MARGINS for top, right, bottom, left. */
    }

    .content-wrapper {
        box-sizing: border-box;
        padding-left: 0;
        padding-right: 0;
    }

    /* === REVISED HEADER STYLES START === */
    .page-header {
        /* position: relative;    */
        margin-bottom: 20px;
        /* Space after the entire header block, before PART-I */
        /* min-height helps ensure the header block has enough vertical space for titles,
           especially if titles have significant top padding.
           The logo (100px high) is absolute, so it doesn't contribute to this flow height.
           Adjust based on the visual height of your title block content + its padding-top. */
        min-height: 70px;
        /* Example: If titles + padding-top are about this tall */
    }

    .header-logo-container {
        text-align: right;
        /* Set width for the container if needed, same as logo */
    }

    .header-logo {
        width: 150px;
        /* Your specified width */
        height: 100px;
        /* Your specified height */
        display: block;
        /* Good practice for images */
    }

    .company-name {
        font-size: 14pt;
        /* Adjusted to match "KOCHI WATER METRO..." visual */
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 4px;
        /* Slightly more space */
    }

    .report-main-title {
        font-size: 10pt;
        /* Adjusted to match "ANNUAL PERFORMANCE..." visual */
        font-weight: bold;
        margin-top: 0;
        margin-bottom: 4px;
        /* Slightly more space */
    }

    /* edited by athira on 04-06-2025 */
    .report-period {
        font-size: 9pt;
        /* Adjusted to match "Report for the year..." visual */
        font-weight: normal;
        /* Ensure it's not bold if the example isn't */
        margin-top: 0;
        /* margin-bottom is effectively handled by .page-header's margin-bottom
           if this is the last item in the title block. */
        overflow-wrap: break-word;
        text-align: center;
    }

    /* end */

    /* === REVISED HEADER STYLES END === */


    /* Section Titles (Keep your existing styles) */
    .section-main-title {
        font-weight: bold;
        font-size: 12pt;
        margin-top: 20px;
        margin-bottom: 5px;
        text-align: left;
    }

    .section-sub-title {
        font-weight: bold;
        font-size: 11pt;
        text-align: center;
        margin-top: 5px;
        margin-bottom: 2px;
    }

    .section-instruction {
        font-size: 9pt;
        text-align: center;
        margin-bottom: 15px;
    }

    /* Table for Personal Data (Keep your existing styles) */
    .personal-data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
        line-height: 1.8;
        margin-top: 0;
    }

    .personal-data-table td {
        border: none;
        vertical-align: top;
        padding-top: 1px;
        padding-bottom: 1px;
    }

    .personal-data-table .label-col {
        width: 55%;
    }

    .personal-data-table .colon-col {
        width: 3%;
        text-align: left;
    }

    .personal-data-table .value-col {
        width: 42%;
        overflow-wrap: break-word;
    }

    .table-sub-note {
        padding: 0px 5px 4px 25px;
        font-size: 9pt;
        line-height: 1.2;
        border: none;
    }

    /* Text Blocks for Self Appraisal (Keep your existing styles) */
    .appraisal-question {
        font-size: 10pt;
        margin-top: 15px;
        margin-bottom: 8px;
        overflow-wrap: break-word;
        word-break: break-word;
        text-align: justify;
    }

    .appraisal-answer-space {
        width: 100%;
        margin-bottom: 15px;
        padding: 5px 12px;
        box-sizing: border-box;
        overflow-wrap: break-word;
        word-break: break-word;
        font-size: 10pt;
        text-align: justify;
        page-break-inside: auto;
        page-break-before: auto;
        page-break-after: auto;
    }

    p.appraisal-answer-space {
  width: 180mm; /* or 100% if the parent is constrained */
  max-width: 100%;
  margin: 0 auto 15px auto;
  padding: 5px 12px;
  box-sizing: border-box;
  font-size: 10pt;
  text-align: justify;
  overflow-wrap: break-word;
  word-break: break-word;
  page-break-inside: auto;
}

    table {
        /* General table rule */
        margin-top: 0;
    }
</style>
<div class="page-container" style="font-family:times;"> <!-- REMOVE inline style="font-family: times;" -->

    <div class="content-wrapper"> <!-- REMOVE inline style="box-sizing: border-box;" -->

        <div class="page-header">
            <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && $arr_comp_contact_info['CompanyContactInfo']['logo']): ?>
                <div class="header-logo-container">
                    <img src="<?= htmlspecialchars($arr_comp_contact_info['CompanyContactInfo']['logo']) ?>" class="header-logo">
                </div>
            <?php endif; ?>

            <div class="company-title-block">
                <div class="company-name" style="text-align:center;">
                    <?= isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? htmlspecialchars($arr_comp_contact_info['CompanyContactInfo']['business_name']) : 'KOCHI WATER METRO LIMITED'; ?>
                </div>
                <div class="report-main-title" style="text-align:center;">
                    ANNUAL PERFORMANCE ASSESSMENT OF EXECUTIVES & SUPERVISORS
                </div>
                <!-- edited by athira on 04-06-2025 -->
                <div class="report-period">
                    Report for the year/period ending <?= htmlspecialchars($emp_data[0]['s']['fin_year']) . '-' . (htmlspecialchars($emp_data[0]['s']['fin_year']) + 1); ?>
                </div>
                <!-- end -->
            </div>
        </div>

        <!-- PART - I Section -->
        <div class="section-main-title">
            PART - I
        </div>
        <div class="section-sub-title">
            PERSONAL DATA
        </div>
        <div class="section-instruction">
            (To be filled by the HR Department)
        </div>

        <table class="personal-data-table" align="center">
            <tr>
                <td class="label-col">1. Name of the Employee</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= isset($emp_data['0']['e']['EmpName']) ? htmlspecialchars($emp_data['0']['e']['EmpName']) : ''; ?></td>
            </tr>
            <tr>
                <td class="label-col">2. Designation/Post held</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= isset($emp_data['0']['s']['designation']) ? htmlspecialchars($emp_data['0']['s']['designation']) : ''; ?></td>
            </tr>
            <tr>
                <td class="label-col">3. Date of birth</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?= isset($emp_data[0]['s']['dob']) && $emp_data[0]['s']['dob'] != '0000-00-00' ? date('d-m-Y', strtotime($emp_data[0]['s']['dob'])) : ''; ?>
                </td>
            </tr>
            <tr>
                <td class="label-col">4. Date of Joining</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?= isset($emp_data[0]['s']['doj']) && $emp_data[0]['s']['doj'] != '0000-00-00' ? date('d-m-Y', strtotime($emp_data[0]['s']['doj'])) : ''; ?>
                </td>
            </tr>
            <tr>
                <td class="label-col">5. Date of entry into the present grade</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <?= isset($emp_data[0]['s']['grade_entry_date']) && $emp_data[0]['s']['grade_entry_date'] != '0000-00-00' ? date('d-m-Y', strtotime($emp_data[0]['s']['grade_entry_date'])) : ''; ?>
                </td>
            </tr>

            <tr>
                <td class="label-col">6. Whether Permanent/Fixed Term Employment</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= isset($emp_data['0']['s']['employment_type']) ? htmlspecialchars($emp_data['0']['s']['employment_type']) : ''; ?></td>
            </tr>
            <tr>
                <td class="label-col">7. Department /Section in which served during the year</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= isset($emp_data['0']['s']['department']) ? htmlspecialchars($emp_data['0']['s']['department']) : ''; ?></td>
            </tr>
            <tr>
                <td class="label-col">8. Period of absence from duty</td>
                <td class="colon-col">:</td>
                <td class="value-col"><?= isset($emp_data['0']['s']['absence_period']) ? htmlspecialchars($emp_data['0']['s']['absence_period']) : ''; ?></td>
            </tr>
            <tr>
                <td class="table-sub-note" colspan="3">(without pay) during the year:</td>
            </tr>
        </table>

        <!-- PART - II Section -->
        <div class="section-main-title">
            PART -II
        </div>
        <div class="section-sub-title">
            SELF APPRAISAL
        </div>
        <div class="section-instruction">
            (To be filled by the Employee)
        </div>

        <div class="appraisal-question">
            1. Brief description of duties
        </div>
        <p class="appraisal-answer-space">
            <?= isset($emp_data['0']['s']['duty_desc']) ? nl2br(htmlspecialchars($emp_data['0']['s']['duty_desc'])) : '<!-- s.duty_desc not set -->'; ?>
        </p>

        <div class="appraisal-question">
            2. Brief resume of the work done by you bringing out any special achievements during the year/period under review. In &nbsp;&nbsp;&nbsp;&nbsp;the event of shortfall in achievement furnish reasons. (The resume to be furnished within the space provided limited &nbsp;&nbsp;&nbsp;&nbsp;to 100 words and is required to be signed)
        </div>
        <p class="appraisal-answer-space">
            <?= isset($emp_data['0']['s']['work_done_desc']) ? nl2br(htmlspecialchars($emp_data['0']['s']['work_done_desc'])) : '<!-- s.work_done_desc not set -->'; ?>
        </p>

    </div> <!-- End of content-wrapper -->
</div> <!-- End of page-container -->