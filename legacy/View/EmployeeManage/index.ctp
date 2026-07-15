<style>
/* -------- Payroll Grid Buttons -------- */
.payroll-main-grid { 
  display: inline-block;
  margin: 0 10px 10px 0;
  padding: 8px 20px;
  border: 1px solid #1e516e;
  border-radius: 50px;
  color: #1e516e;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.1s ease;
}

.payroll-main-grid:hover {
  background-color: #1e516e;
  color: white;
  transform: translateY(-3px);}

/* Enabled feature: active */
.payroll-main-grid.active {
  background-color: #1e516e;
  color: #fff;
}

/* Disabled feature - normal state */
.payroll-main-grid.disabled {
  background-color: #f1f1f1;
  color: #aaa;
  cursor: pointer;  /* Still clickable */
}

/* Disabled feature - on click/active state */
.payroll-main-grid.disabled:active {
  background-color: #ffffff;  /* lighter highlight */
  color: #1e516e;             /* text highlight */
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(30, 81, 110, 0.25);
}


/* -------- Payroll Card Buttons -------- */
.payroll-upgrade-btn{
  border: 1px solid #1e516e;
  border-radius: 50px;
  background-color: white;
  color: #1e516e !important;
  padding: 6px 20px;
  width: 100%;
  cursor: pointer;
  font-weight: 600;               
  transition: all 0.1s ease;       
}

.payroll-upgrade-btn:hover{
  background-color:#1e516e;
  color: #fff !important;
  transform: translateY(-3px);
}
.payroll-card-btn{
  border: 1px solid #1e516e;
  border-radius: 50px;
  background-color: #1e516e;
  color: #fff !important;
  padding: 6px 20px;
  width: 100%;
  cursor: pointer;
  font-weight: 600;               
  transition: all 0.1s ease;       
}

.payroll-card-btn:hover{
  background-color: #fff;
  color: #1e516e !important;
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(30, 81, 110, 0.25);
}


/* -------- Payroll Grid Layout -------- */
.payroll-three-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 80px;
  margin: 20px 15px 0 15px;
}

.payroll-grid-left {
  display: grid;
  grid-template-rows: repeat(7, 1fr);
  gap: 10px;
}

.payroll-grid-center, .payroll-grid-right {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* -------- Cards -------- */
.payroll-card-step {
  font-size: 14px;
  color: gray;
  margin-bottom: 8px;
}

.payroll-card-title {
  font-size: 18px;
  font-weight: bold;
  margin: 10px 0;
}

.payroll-card-desc {
  font-size: 14px;
  color: #555;
  margin-bottom: 16px;
}

.payroll-step-badge {
  background: #e2e8f0;
  color: #666;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: bold;
}

.payroll-present-badge {
  background: #28a745;
  color: #fff;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: bold;
}

.payroll-badge {
  display: flex;
  justify-content: space-between;
}

.payroll-article-list li{ 
  list-style: circle !important;
  cursor:pointer;
  margin-left: 15px;
}

.payroll-grid-left {
    max-height: 450px;   
    overflow-y: auto;  
    overflow-x: hidden; 
    padding-right: 5px; 
    padding: 10px !important; 
}

/* Chrome, Edge, Safari */
.payroll-grid-left::-webkit-scrollbar {
    width: 5px; 
}

.payroll-grid-left::-webkit-scrollbar-thumb {
    background: #b5b5b5;
    border-radius: 10px;
}

.payroll-grid-left::-webkit-scrollbar-track {
    background: #f1f1f1;
}

/* Firefox */
.payroll-grid-left {
    scrollbar-width: thin;       
    scrollbar-color: #b5b5b5 #f1f1f1;
}
.payroll-upgrade-btn {
    pointer-events: none;   /* Prevent clicking */
    opacity: 0.6;           /* Visual disabled effect */
    cursor: not-allowed;
}
</style>

<!-- ===== PAYROLL PAGE ===== -->
<div class="payroll-view">
  <section class="content-header heading">
      <h1 class="text-primary-18">Employee</h1>
  </section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">

  <div class="payroll-three-grid">
    <div class="payroll-grid-left"></div>
    <div class="payroll-grid-center"></div>
    <div class="payroll-grid-right"></div>
  </div>
</div>


<script>
$(document).ready(function () {

   const pageKey = "employee"; // page-specific key
  const storageKey = `last_clicked_feature_${pageKey}`;

  // ===== Fetch Employee Features =====
  $.getJSON("EmployeeManage/getEmployeeFeatures", function (res) {
    if (res.error) {
      $(".payroll-grid-left").html(`<div class="error">${res.error}</div>`);
      return;
    }

    let features = res.features || [];
    let html = "";

    $.each(features, function (i, f) {
      let disabledClass = f.is_enabled == 1 ? "" : "disabled";
      html += `
        <a href="#" class="payroll-main-grid ${disabledClass}"
           data-index="${pageKey}-${i}"
           data-title="${f.feature_name}"
           data-desc="${f.description}"
           data-route="${f.feature_path}"
           data-article="${f.article || ''}"
           data-enabled="${f.is_enabled}">
           ${i + 1}. ${f.feature_name}
        </a>`;
    });

    $(".payroll-grid-left").html(html);

    // ===== Restore last clicked or select first =====
    let lastIndex = localStorage.getItem(storageKey);

    if (lastIndex && $(`.payroll-main-grid[data-index="${lastIndex}"]`).length) {
        $(`.payroll-main-grid[data-index="${lastIndex}"]`).trigger("click");
    } else {
        $(".payroll-main-grid").not(".disabled").first().trigger("click");
    }
  });

  // ===== Click handler to save last clicked feature =====
  $(document).on("click", ".payroll-main-grid", function () {
    localStorage.setItem(storageKey, $(this).data("index"));
  });


  // ===== Feature Click Handler =====
  $(document).on("click", ".payroll-main-grid", function (e) {
    e.preventDefault();
    let title = $(this).data("title");
    let desc = $(this).data("desc");
    let route = $(this).data("route");
    let article = $(this).data("article");
    let enabled = $(this).data("enabled");

    if (enabled == 1) {
      // Enabled feature
      $(".payroll-grid-center").html(`
        <div class="payroll-badge">
          <span class="payroll-present-badge">Active</span>
        </div>
        <div class="payroll-card-title">${title}</div>
        <div class="payroll-card-desc">${desc}</div>
        <button class="payroll-card-btn" data-route="${route}">Open ${title}</button>
      `);

      // Articles
      let articleHtml = "";
      if (article) {
  let links = article.split(",");
  articleHtml = `<ul class="payroll-article-list">`;
  
  $.each(links, function (i, link) {
    link = link.trim();
    if (link) {

      // Extract last part & replace hyphens with spaces
      let text = decodeURIComponent(
        link.split("/").pop().replace(/-/g, " ")
      );

      // Capitalize every word
      text = text.replace(/\b\w/g, (c) => c.toUpperCase());

      articleHtml += `<li><a href="${link}" target="_blank">${text}</a></li>`;
    }
  });

  articleHtml += `</ul>`;
}


      $(".payroll-grid-right").html(`
        <div class="payroll-card-step">Info Panel</div>
        <div class="payroll-card-title">${title} Guide</div>
        <div class="payroll-card-desc">${desc}</div>
        <h4>Articles</h4>
        ${articleHtml || "<p>No articles available</p>"}
      `);

    } else {
      // Disabled feature → Upgrade
      $(".payroll-grid-center").html(`
        <div class="payroll-badge">
          <span class="payroll-step-badge">Not in Plan</span>
        </div>
        <div class="payroll-card-title">${title}</div>
        <div class="payroll-card-desc">This feature is not available in your current plan.</div>
        <button class="payroll-upgrade-btn">Upgrade to Next Plan</button>
      `);

      $(".payroll-grid-right").html(`
        <div class="payroll-card-step">Info Panel</div>
        <div class="payroll-card-title">${title} Guide</div>
        <div class="payroll-card-desc">Upgrade your plan to access this feature.</div>
      `);
    }

    $(".payroll-main-grid").removeClass("active");
    $(this).addClass("active");
  });

  // ===== Open feature page =====
  $(document).on("click", ".payroll-card-btn", function (e) {
    e.preventDefault();
    let url = $(this).data("route");
    if (url) {
      $("#container").isLoading({ text: "Loading", position: "overlay" });
      $("#container").empty().load(url, function () { isDashboardShown = false; });
    }
  });

  // ===== Upgrade Button =====
  $(document).on("click", ".payroll-upgrade-btn", function (e) {
    e.preventDefault();
    $("#container").isLoading({ text: "Loading", position: "overlay" });
    $("#container").empty().load("User/profile", function () {
        isDashboardShown = false;
        $(".nav-tabs li, .tab-pane").removeClass("active");
        $('a[href="#tab_3-3"]').parent().addClass("active");
        $("#tab_3-3").addClass("active");
        $('html, body').animate({ scrollTop: $("#tab_3-3").offset().top }, 500);
    });
  });

});
$('.payroll-upgrade-btn').prop('disabled', true);
</script>