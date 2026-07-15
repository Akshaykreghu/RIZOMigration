
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
        background: linear-gradient(135deg, #1e516e, #0a7c36);
        color: #ffffff;
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
        max-width: 600px; /* Prevents line from being too long on wide screens */
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
        padding: 50px 60px;
        gap: 40px;
      }

      .links-section {
        flex: 1 1 45%;
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
        background: linear-gradient(
          135deg,
          var(--primary-color),
          var(--accent-color)
        );
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
        background: linear-gradient(135deg, #1e516e, #0a7c36);
        box-shadow: 0 10px 24px rgba(12, 149, 66, 0.4);
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
          <h4>
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
          <h1>Welcome to MPM HR</h1>
          <h5>
            MPM HR is your trusted HRMS platform for managing
            payroll, attendance, leave and more. Designed with simplicity and
            efficiency in mind, we empower organizations with smart HR tools.
          </h5>
        </div>
      </div>

          <div style="overflow:hidden; width:100%; background:#008080; box-shadow:0 4px 14px rgba(0,0,0,0.25);">
  <div style="display:inline-block; white-space:nowrap; animation: scroll 30s linear infinite; color:#fff; font-family:Inter, Arial, sans-serif; font-size:15px; line-height:1.5;">
    <!-- First copy of content -->
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>

    <!-- Duplicate copy for seamless scrolling -->
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>
    <span style="margin-right:50px;">✨ New Business Dashboard is now LIVE! Enjoy enhanced insights and a smarter HRMS experience. Explore now! ✨</span>
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

    <a href="https://www.youtube.com/@mypayrollmaster4556" class="helpful-links" target="_blank">
        📊 Product Overview
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

  <!-- feedback form announcement  -->
  <!-- <div class="announcement-feedback" 
     style="padding:15px; background:#f1f9ff; border-left:5px solid #1e516e; margin-bottom:15px; border-radius:6px;">

 
  <strong style="font-size:18px; display:block; margin-bottom:6px;">
    📄Second Quarter Feedback Form Published!
  </strong>

  
  <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
     onclick="toggleZoomAnnouncementfeedback()">

  <p style="font-size:14px; margin:0; color:#333; flex:1;">
  &nbsp; <b>We value your voice. Share your thoughts through our Q2 feedback form and help us improve further. &nbsp;</b>
  </p>

  <i id="zoom-chevron4" class="bi bi-chevron-down" 
     style="font-size:16px; color:#1e516e; margin-left:10px; flex-shrink:0; transition: transform 0.3s ease;"></i>
</div>


<div id="feedback-details" 
     style="max-height:0; overflow:hidden; transition:max-height 0.5s ease; margin-top:0;">
        <p style="font-size:14px; margin:0; color:#555;">
     Note : Submit the form by 09/10/2025
   </p>
     <a href="https://forms.gle/tCbuMzatairEvQgV6" 
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
 Click here to share your feedback
</a>

</div>

  </div> -->


  <div class="announcement-dashboard" 
     style="padding:15px; background:#f1f9ff; border-left:5px solid #1e516e; margin-bottom:15px; border-radius:6px;">

  <!-- Title -->
  <strong style="font-size:18px; display:block; margin-bottom:6px;color:#4e97fa;">
    🎉 New Business Dashboard is now LIVE! 🎉
  </strong>

  
  <div style="display:flex;justify-content:space-between;align-items:center;">

  <p style="font-size:14px; margin:0; color:#333; flex:1;">
 Experience improved insights, faster performance, and a smarter view of your business metrics.
 Explore the updated dashboard today and elevate your HRMS experience!!
  </p>

</div>

</div> 

  <div class="announcement-registration3" 
     style="padding:15px; background:#f1f9ff; border-left:5px solid #1e516e; margin-bottom:15px; border-radius:6px;">

  <!-- Title -->
  <strong style="font-size:18px; display:block; margin-bottom:6px;">
    🎉 New Update : Myprofile Master 🎉
  </strong>

  
  <div style="display:flex;justify-content:space-between;align-items:center;"
     onclick="toggleZoomAnnouncement8()">

  <p style="font-size:14px; margin:0; color:#333; flex:1;">
 Latest version of the My Profile Master application is now available for both Android and iOS users. 
 You can update the app through the Google Play Store or App Store to access the latest features and enhancements.
  </p>

  <i id="zoom-chevron8" class="bi bi-chevron-down" 
     style="font-size:16px; color:#1e516e; margin-left:10px; flex-shrink:0; transition: transform 0.3s ease;"></i>
</div>


<div id="zoom-details8" 
     style="max-height:0; overflow:hidden; transition:max-height 0.5s ease; margin-top:0;">
   
    <a href="https://play.google.com/store/apps/details?id=com.mpm.myprofile" 
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
  📲 Update Android App
</a>

<a href="https://apps.apple.com/in/app/myprofilemaster/id1594402858"
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
  🍎 Update iOS App
</a>

   <p style="font-size:14px; margin:0; color:#555;">
     
   </p>
</div> 

  </div>

  <!-- support announcement  -->
  <div class="announcement-support" 
     style="padding:15px; background:#f1f9ff; border-left:5px solid #1e516e; margin-bottom:15px; border-radius:6px;">

  <!-- Title -->
  <strong style="font-size:18px; display:block; margin-bottom:6px;">
    📣A Better Support Experience is Coming Soon!
  </strong>

  
  <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
     onclick="toggleZoomAnnouncementspt()">

  <p style="font-size:14px; margin:0; color:#333; flex:1;">
  &nbsp; <b>We’re excited to announce an update to our support options! A new Support Portal is now available on your homepage. &nbsp;</b>
  </p>

  <i id="zoom-chevron3" class="bi bi-chevron-down" 
     style="font-size:16px; color:#1e516e; margin-left:10px; flex-shrink:0; transition: transform 0.3s ease;"></i>
</div>


<div id="support-details" 
     style="max-height:0; overflow:hidden; transition:max-height 0.5s ease; margin-top:0;">
        <p style="font-size:14px; margin:0; color:#555;">
     Note : From September 20th, the Get Support button will be removed. Please use the Support Portal for assistance.
   </p>
     <a href="https://greatleapsupport.zohodesk.in/portal/en/kb/articles/how-to-submit-a-queries-in-mpm-hr-support" 
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
 Learn more about the new portal
</a>

</div>

  </div>

  <!-- edited by athira on 09-09-2025 -->
   <div class="announcement-registration" 
     style="padding:15px; background:#f1f9ff; border-left:5px solid #1e516e; margin-bottom:15px; border-radius:6px;">

  <!-- Title -->
  <strong style="font-size:18px; display:block; margin-bottom:6px;">
    🌟 Unlock the Full Power of MPM HR! 🌟
  </strong>

  
  <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
     onclick="toggleZoomAnnouncement()">

  <p style="font-size:14px; margin:0; color:#333; flex:1;">
  &nbsp; <b>Join our <span > Weekly Live Training Session </span> and become an MPM HR Pro! &nbsp;</b>
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
  👉 Register Now
</a>
   <p style="font-size:14px; margin:0; color:#555;">
     Registration is mandatory – Zoom link will be sent to your email upon successful registration
   </p>
</div>

  </div>


  <!-- end -->

  <!-- edited by athira on 16-09-2025 -->
  <!-- meeting - employee success -->
  <div class="announcement-registration2" 
     style="padding:15px; background:#f1f9ff; border-left:5px solid #1e516e; margin-bottom:15px; border-radius:6px;">

  <!-- Title -->
  <strong style="font-size:18px; display:block; margin-bottom:6px;">
    🌟 Exciting Opportunity for Employee Growth 🌟
  </strong>


  
  <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
     onclick="toggleZoomAnnouncement2()">

      <p style="font-size:14px; margin:0; color:#333; flex:1;">
  &nbsp; <b>Join our <span > Employee Success Program </span> &nbsp;</b>
  </p>

  <i id="zoom-chevron2" class="bi bi-chevron-down" 
     style="font-size:16px; color:#1e516e; margin-left:10px; flex-shrink:0; transition: transform 0.3s ease;"></i>
</div>


<div id="zoom-details2" 
     style="max-height:0; overflow:hidden; transition:max-height 0.5s ease; margin-top:0;">

     <p style="margin:0;">
      We’re thrilled to invite  your organization to be part of our Employee Success Program — a valuable initiative designed to enhance knowledge and empower teams with essential insights on:</p>
<br>
     <p style="margin:0;"> 🔹 HR Policies <br>
🔹 Social Security Benefits <br>
🔹 HRMS (MPM) System Overview <br> </p>
  
   <div style="display:flex; justify-content:space-between; margin-top:12px;">
     <span>
       <i class="bi bi-calendar-event" style="margin-right:6px; color:#1e516e;"></i>
      <b> Wednesday, Sep 17 2025</b>
     </span>

     <span>
       <i class="bi bi-clock" style="margin-right:6px; color:#1e516e;"></i>
      <b> 3 : 00 pm Onwards </b>
     </span>
   </div>
    <a href="https://us02web.zoom.us/j/9562628000?pwd=Z9Kibm1U26EGkGdc90yZkpCdc6W7bD.1&omn=86720669926" 
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
    var url = "/Attendance/showregister";
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
    var url = "/Employee/index";
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

//edited by athira on 16-09-2025

function toggleZoomAnnouncementfeedback() {

  const details2 = document.getElementById("feedback-details");
  const chevron2 = document.getElementById("zoom-chevron4");

   if (details2.style.maxHeight === "0px" || details2.style.maxHeight === "") {
    details2.style.maxHeight = details2.scrollHeight + "px"; // expand smoothly
    chevron2.style.transform = "rotate(180deg)";
    details2.style.marginTop = "12px";
  } else {
    details2.style.maxHeight = "0";
    chevron2.style.transform = "rotate(0deg)";
    details2.style.marginTop = "0";
  }
}

function toggleZoomAnnouncement2() {

  const details2 = document.getElementById("zoom-details2");
  const chevron2 = document.getElementById("zoom-chevron2");

   if (details2.style.maxHeight === "0px" || details2.style.maxHeight === "") {
    details2.style.maxHeight = details2.scrollHeight + "px"; // expand smoothly
    chevron2.style.transform = "rotate(180deg)";
    details2.style.marginTop = "12px";
  } else {
    details2.style.maxHeight = "0";
    chevron2.style.transform = "rotate(0deg)";
    details2.style.marginTop = "0";
  }
}

function toggleZoomAnnouncementspt() {
  const details = document.getElementById("support-details");
  const chevron = document.getElementById("zoom-chevron3");

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

function toggleZoomAnnouncement8(){
   const details = document.getElementById("zoom-details8");
  const chevron = document.getElementById("zoom-chevron8");

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


document.addEventListener('DOMContentLoaded', function() {
    const joinButton = document.querySelector('.announcement-registration2 a');
    const announcementSection = document.querySelector('.announcement-registration2');

    const now = new Date();
    const tomorrow = new Date();
    tomorrow.setDate(now.getDate() + 1); // tomorrow's date

    // Hide announcement after 5 PM tomorrow
    const hideTimeTomorrow = new Date(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate(), 17, 0, 0);
    if (now >= hideTimeTomorrow) {
        announcementSection.style.display = 'none';
        return; // stop execution
    }

    // Enable button only on Sep 17, 2025 at 2:50 PM
    const enableTime = new Date(2025, 8, 17, 14, 50, 0); // 8 = September
    const hideTime = new Date(2025, 8, 17, 17, 0, 0);     // 5 PM Sep 17

    function updateAnnouncement() {
        const current = new Date();

        // Enable button at 2:50 PM Sep 17, 2025
        if (current >= enableTime && current < hideTime) {
            joinButton.style.pointerEvents = 'auto';
            joinButton.style.opacity = '1';
        } else {
            joinButton.style.pointerEvents = 'none';
            joinButton.style.opacity = '0.5';
        }

        // Hide announcement at 5 PM Sep 17
        if (current >= hideTime) {
            announcementSection.style.display = 'none';
        }
    }

    // Initial check
    updateAnnouncement();

    // Check every 30 seconds
    setInterval(updateAnnouncement, 30000);
});
//end

    </script>
