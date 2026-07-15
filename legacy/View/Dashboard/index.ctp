
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
      * {
        box-sizing: border-box;
      }

      :root {
        --text-color: white;
        --primary-color: #1e516e; /* Deep Blue */
        --accent-color: #0c9542; /* Vibrant Green */
      }

      body {
        margin: 0;
        background-color: #f8f9fa;
        color: #495057;
      }

      /* Animation Keyframes */
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @keyframes iconWobble {
        0%,
        100% {
          transform: rotate(0deg);
        }
        25% {
          transform: rotate(-10deg);
        }
        75% {
          transform: rotate(10deg);
        }
      }

      @keyframes floatAnimation {
        0% {
          transform: translateY(0);
        }
        50% {
          transform: translateY(-12px);
        }
        100% {
          transform: translateY(0);
        }
      }

      .header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
         /* edited by bindu_18_11_25 */
        /* background: linear-gradient(135deg, #1e516e, #0a7c36); */
        color: #1E516E;
         /* edited by bindu_18_11_25 end */
        padding: 30px 10px;
        /* border-bottom: 1px solid #dee2e6; */
        overflow: hidden;
      }

      .header .image {
        flex: 1 1 40%;
        max-width: 450px;
        padding: 20px;
        animation: fadeInUp 0.8s ease-out forwards;
      }

      .header .image img {
        width: 100%;
        height: auto;
        animation: floatAnimation 6s ease-in-out infinite;
      }

      .header .content {
        flex: 1 1 50%;
        padding: 20px;
      }

      /* --- ADJUSTED STYLES START --- */
      .header h4 {
        font-size: 22px;
        font-weight: 400;
        margin-bottom: 8px; /* Reduced space between greeting and main welcome */
        color: #ffffff;
        animation: fadeInUp 0.8s ease-out 0.2s forwards;
        opacity: 0;
      }

      .header h1 {
        font-size: 48px; /* Slightly increased size for emphasis */
        font-weight: 600;
        margin-top: 0;
        margin-bottom: 20px; /* Increased space below main heading */
        color: #ffffff;
        animation: fadeInUp 0.8s ease-out 0.4s forwards;
        opacity: 0;
      }

      .header h5 {
        font-size: 17px; /* Adjusted for optimal readability */
        font-weight: 400;
        line-height: 1.8; /* Increased line height */
        color: #ffffff;
        animation: fadeInUp 0.8s ease-out 0.6s forwards;
        opacity: 0;
        max-width: 60%; /* Prevents line from being too long on wide screens */
      }
      /* --- ADJUSTED STYLES END --- */
        
      main{
        font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;;
      }
      .main-content {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        align-items: flex-start;
        padding: 10px 60px;
        gap: 40px;
      }

      .links-section {
        flex: 1 1 35%;
        opacity: 0;
        animation: fadeInUp 0.8s ease-out 0.6s forwards;
      }

      .links-section h2 {
        font-size: 28px;
        font-weight: 600;
        margin-bottom: 25px;
        color: var(--primary-color);
      }

      .helpful-links {
        display: inline-block;
        margin: 0 10px 10px 0;
        padding: 12px 25px;
        border: 1px solid #1e516e;
        border-radius: 50px;
        color: var(--primary-color);
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
      }

      .helpful-links:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(30, 81, 110, 0.25);
      }

      .updates-section {
        flex: 1 1 45%;
        background-color: #ffffff;
        padding: 0 30px 30px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        max-height: 300px;
        overflow-y: auto;
        opacity: 0;
        animation: fadeInUp 0.8s ease-out 0.8s forwards;
      }
      .updates-section .announcement-link{
        color:#1e516e;
      }

      .updates-section h3 {
        font-size: 24px;
        font-weight: 600;
        margin: 0 -30px 20px -30px;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        position: sticky;
        top: 0;
        padding: 30px 30px 15px 30px;
        background-color: #ffffff;
        z-index: 10;
        border-bottom: 1px solid #f0f0f0;
      }

      .updates-section h3 .bi-megaphone {
        animation: iconWobble 2s ease-in-out 1s infinite;
      }

      .updates-section p {
        font-size: 16px;
        line-height: 1.8;
        margin: 15px 0;
        display: flex;
        align-items: center;
        opacity: 0;
        animation: fadeInUp 0.5s ease-out forwards;
      }

      .updates-section p:nth-of-type(1) {
        animation-delay: 1s;
      }
      .updates-section p:nth-of-type(2) {
        animation-delay: 1.2s;
      }
      .updates-section p:nth-of-type(3) {
        animation-delay: 1.4s;
      }
      .updates-section p:nth-of-type(4) {
        animation-delay: 1.6s;
      }
      .updates-section p:nth-of-type(5) {
        animation-delay: 1.8s;
      }

      .updates-section .bi-megaphone {
        margin-right: 12px;
        color: var(--accent-color);
        font-size: 28px;
        transition: transform 0.3s ease;
      }

      .updates-section .bi-chevron-right {
        margin-right: 10px;
        color: var(--accent-color);
        font-weight: bold;
        flex-shrink: 0;
        margin-top: 5px;
      }

      .new-label {
        background-color: var(--accent-color);
        color: white;
        padding: 0px 5px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        margin-left: 10px;
        flex-shrink: 0;
      }

      @media (max-width: 992px) {
        .header {
          padding: 40px 30px;
        }
        .main-content {
          padding: 40px 30px;
        }
      }

      @media (max-width: 768px) {
        .header {
          flex-direction: column;
          text-align: center;
        }

        .header .content,
        .header .image {
          flex: 1 1 100%;
        }

        .header h5 {
          max-width: 100%; /* Allow h5 to take full width in mobile view */
        }

        .main-content {
          flex-direction: column;
        }

        .links-section,
        .updates-section {
          flex: 1 1 100%;
          max-height: none;
          overflow-y: visible;
        }

        .updates-section h3 {
          position: static;
          padding: 0 0 15px 0;
          margin: 0 0 20px 0;
        }
      }
      #support-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        /* background: linear-gradient(
          135deg,
          var(--primary-color),
          var(--accent-color)
        ); */
         /* edited by bindu_18_11_25 */
        background: linear-gradient(135deg, #1e516e, #2193c0ff);
        /* edited by bindu_18_11_25 end */
        color: white;
        border: none;
        padding: 14px 24px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 50px;
        box-shadow: 0 6px 16px rgba(30, 81, 110, 0.4);
        cursor: pointer;
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        letter-spacing: 0.3px;
      }

      #support-btn i {
        font-size: 18px;
      }

      #support-btn:hover {
          /* edited by bindu_18_11_25 */
        background: linear-gradient(135deg, #1e516e, #0a7c36);
        /* box-shadow: 0 10px 24px rgba(12, 149, 66, 0.4); */
        /* edited by bindu_18_11_25 end */
        transform: translateY(-2px) scale(1.02);
      }
      .emoji {
  display: inline-block;
  animation: pulse 1.5s infinite;
  transform-origin: center;
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.15);
    opacity: 0.95;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes scroll {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); } /* scrolls left by half the width */
}

.youtube-icon {
  color: #ff0000; /* YouTube red */
  margin-right: 5px;
  vertical-align: middle;
  font-size: 16px;
  
}

.helpful-links:hover .youtube-icon {
  transform: scale(1.2);
}

    </style>
  </head>
  <body>
    <main>
      <!-- Header / Banner -->
      <div class="header">
        <div class="image">
          <img src="/img/landing page.png" alt="HRMS Platform" />
        </div>
        <div class="content">
          <!-- MODIFIED: Changed h1 to h4 for the greeting -->
           <!-- /* edited by bindu_18_11_25 */ -->
          <h4 style="color: #1E516E;">
            <!-- /* edited by bindu_18_11_25 end */ -->
<?php
    date_default_timezone_set("Asia/Kolkata");
    $hour = date("H");
    $emoji = "";

    if ($hour < 12) {
        $greeting = "Good Morning";
        $emoji = "☀️";
    } elseif ($hour < 15) {
        $greeting = "Good Afternoon";
        $emoji = "🌤️";
    } else {
        $greeting = "Good Evening";
        $emoji = "⛅";
    }

    echo $greeting . ' <span class="emoji">' . $emoji . '</span>' . ($emp_name ? ", $emp_name" : "");
?>
</h4>
           <!-- /* edited by bindu_18_11_25 */ -->
          <h1 style="color: #1E516E;">Welcome to <img src="/newlogin/img/rizonew.png" style="width:13%;"></h1>
          <h5 style="color: #1E516E;">
            Rizo is your trusted HRMS platform for managing
            payroll, attendance, leave and more. Designed with simplicity and
            efficiency in mind, we empower organizations with smart HR tools.
          </h5>
        </div>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <!-- Helpful Links -->
        <div class="links-section">
    <h2>Helpful Links</h2>

    <a href="https://chat.whatsapp.com/JkSjZl8nDQ4JUGMkHmZSnn?mode=r_c" class="helpful-links" target="_blank">
        🌐 HR Community
    </a>

    <!-- <a href="https://youtube.com/@rizo-greatleap?si=G2oeE5pgH5YlAXSV" class="helpful-links" target="_blank">
        ▶️ Rizo Tutorial
    </a> -->

    <a href="https://youtube.com/@rizo-greatleap?si=G2oeE5pgH5YlAXSV" class="helpful-links" target="_blank">
    <i class="bi bi-youtube youtube-icon"></i> Rizo Tutorial
</a>
    <a href="https://greatleapsupport.zohodesk.in/portal/en/home" class="helpful-links" target="_blank">
        🛠️ Support Portal
    </a>

    <a href="javascript:void(0);" onclick="viewAddEmployee();" class="helpful-links">
        👨🏻 Add Employee
    </a>


    <a href="javascript:void(0);" onclick="viewAttendance();" class="helpful-links" >
        🗓️ Attendance Verification
    </a>

    <a href="javascript:void(0);" onclick="viewPayroll();" class="helpful-links">
        💰 Process Payroll
    </a>

     <a href="https://www.mpmhr.com/referandearn"  class="helpful-links">
        🎁 Refer & Earn
    </a>
    
</div>


        <!-- Announcements -->
        <div class="updates-section">
  <h3><i class="bi bi-megaphone"></i> Announcements</h3>

  

  <!-- edited by athira on 09-09-2025 -->
   <div class="announcement-registration" 
     style="padding:15px; background:#f1f9ff; border-left:5px solid #1e516e; margin-bottom:15px; border-radius:6px;">

  <!-- Title -->
  <strong style="font-size:18px; display:block; margin-bottom:6px;">
    🌟 Weekly Training 🌟
  </strong>

  
  <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
     onclick="toggleZoomAnnouncement()">

  <p style="font-size:14px; margin:0; color:#333; flex:1;">
  &nbsp; <b>Join our <span > Weekly Live Training Session </span></b>
  </p>

  <i id="zoom-chevron" class="bi bi-chevron-down" 
     style="font-size:16px; color:#1e516e; margin-left:10px; flex-shrink:0; transition: transform 0.3s ease;"></i>
</div>


<div id="zoom-details" 
     style="max-height:0; overflow:hidden; transition:max-height 0.5s ease; margin-top:0;">
   <div style="display:flex; justify-content:space-between; margin-top:12px;">
     <span>
       <i class="bi bi-calendar-event" style="margin-right:6px; color:#1e516e;"></i>
       <?= date('l, d M Y', strtotime($nextZoomSession)) ?>
     </span>

     <span>
       <i class="bi bi-clock" style="margin-right:6px; color:#1e516e;"></i>
       11:00 AM – 12:00 PM
     </span>
   </div>
    <a href="<?= $zoomRegistrationLink ?>" 
   target="_blank" 
   style="display:inline-block; 
          padding:10px 20px; 
          background:#1e516e; 
          color:#fff; 
          border-radius:6px; 
          font-weight:600; 
          text-decoration:none; 
          margin:12px 0; 
          box-shadow:0 2px 6px rgba(0,0,0,0.15); 
          transition:background 0.3s ease, transform 0.2s ease;">
  👉 Join Now
</a>
   <!-- <p style="font-size:14px; margin:0; color:#555;">
     Registration is mandatory – Zoom link will be sent to your email upon successful registration
   </p> -->
</div>

  </div>




  <!-- end -->
  <?php if (!empty($announcements)): ?>
    <?php foreach ($announcements as $row): ?>
      <?php
        $a = $row['announcements'];
        $title = h($a['title']);
        $link = h($a['link']);
        $createdDate = $a['creation_date'];
      ?>
      <p>
        <i class="bi bi-chevron-right"></i>
        <span>
          <?php if (!empty($link)): ?>
            <a class="announcement-link" target="_blank" href="<?= $link ?>"><?= $title ?></a>
            <?php if (date('Y-m-d') === date('Y-m-d', strtotime($createdDate))): ?>
              <span class="new-label">New</span>
            <?php endif; ?>
          <?php else: ?>
            <?= $title ?>
          <?php endif; ?>
        </span>
      </p>
    <?php endforeach; ?>
  <?php else: ?>
    <!-- <p>No announcements found.</p> -->
  <?php endif; ?>
</div>

      <!-- Support Button -->
      <a href="https://greatleapsupport.zohodesk.in/portal/en/home" id="support-btn" target='_blank'>
        <i class="bi bi-question-circle"></i> Help
      </a>
    </main>
    <script>
     function viewAttendance() {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay"
    });

    var companyCode = "<?php echo $this->Session->read('company_code'); ?>";

    var allowedCompanies = [
         'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','GTRA','VGNN'
    ];

    var url = "";

    if (allowedCompanies.includes(companyCode)) {
        url = "/Attendance/showregister";
    } else {
        url = "/AttendanceRegisterNew";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
        $('html, body').scrollTop(0);
    });
}


function viewPayroll() {
    $("#container").isLoading({
        text: "Loading",
        position: "overlay"
    });
    var url = "/Payroll/showprocesspayroll";
    $("#container").load(url, function () {
        isDashboardShown = false;
        $('html, body').scrollTop(0);

    });
}

function viewAddEmployee() {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay"
    });

    var companyCode = "<?php echo $this->Session->read('company_code'); ?>";

    var allowedCompanies = [
        'HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL',
        'VGNN','MDLK','ABSG','VGFS','VSFS',
        'DRRC','DJIC','AGNG','AYRK','SRTS'
    ];

    var url = "";

    if (allowedCompanies.includes(companyCode)) {
        url = "/Employee/index";
    } else {
        url = "/EmployeeJoin/index";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
        $('html, body').scrollTop(0);
    });
}
//edited by athira on 09-09-2025
function toggleZoomAnnouncement() {
  const details = document.getElementById("zoom-details");
  const chevron = document.getElementById("zoom-chevron");

  if (details.style.maxHeight === "0px" || details.style.maxHeight === "") {
    details.style.maxHeight = details.scrollHeight + "px"; // expand smoothly
    chevron.style.transform = "rotate(180deg)";
    details.style.marginTop = "12px";
  } else {
    details.style.maxHeight = "0";
    chevron.style.transform = "rotate(0deg)";
    details.style.marginTop = "0";
  }
}
//end

    </script>
