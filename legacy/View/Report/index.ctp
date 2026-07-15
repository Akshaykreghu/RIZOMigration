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
    transform: translateY(-3px);
  }

  /* Enabled feature: active */
  .payroll-main-grid.active {
    background-color: #1e516e;
    color: #fff;
  }

  /* Disabled feature - normal state */
  .payroll-main-grid.disabled {
    background-color: #f1f1f1;
    color: #aaa;
    cursor: pointer;
    /* Still clickable */
  }

  /* Disabled feature - on click/active state */
  .payroll-main-grid.disabled:active {
    background-color: #ffffff;
    /* lighter highlight */
    color: #1e516e;
    /* text highlight */
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(30, 81, 110, 0.25);
  }


  /* -------- Payroll Card Buttons -------- */
  .payroll-upgrade-btn {
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

  .payroll-upgrade-btn:hover {
    background-color: #1e516e;
    color: #fff !important;
    transform: translateY(-3px);
  }

  .payroll-card-btn {
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

  .payroll-card-btn:hover {
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

  .payroll-grid-center,
  .payroll-grid-right {
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

  .payroll-article-list li {
    list-style: circle !important;
    cursor: pointer;
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
    pointer-events: none;
    /* Prevent clicking */
    opacity: 0.6;
    /* Visual disabled effect */
    cursor: not-allowed;
  }
</style>

<!-- ===== PAYROLL PAGE ===== -->
<div class="payroll-view">
  <section class="content-header heading">
    <h1 class="text-primary-18">Reports</h1>
  </section>
  <hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">

  <div class="payroll-three-grid">
    <div class="payroll-grid-left"></div>
    <div class="payroll-grid-center"></div>
    <div class="payroll-grid-right"></div>
  </div>
</div><script>
$(document).ready(function () {

    // ------------------------------------
    // LOAD GRID 1 — REPORT CATEGORIES
    // ------------------------------------
    $.getJSON("Report/getReportCategories", function (res) {
        let html = "";

        $.each(res.categories, function (i, cat) {
            html += `
                <a href="#" class="payroll-main-grid"
                   data-cat="${cat}"
                   data-index="${i}">
                   ${i + 1}. ${cat.charAt(0).toUpperCase() + cat.slice(1).toLowerCase()}
                </a>`;
        });

        $(".payroll-grid-left").html(html);

        // ------------------------------------
        // RESTORE LAST CLICKED CATEGORY
        // ------------------------------------
        let lastIndex = localStorage.getItem("report_last_clicked_category");

        if (lastIndex !== null) {
            let lastItem = $(`.payroll-main-grid[data-index="${lastIndex}"]`);

            if (lastItem.length) {
                // Instant UI activate + load
                lastItem.addClass("active");
                lastItem.trigger("click");
                return;
            }
        }

        // If no last saved → auto-click first category (instant)
        let firstItem = $(".payroll-main-grid").first();
        firstItem.addClass("active");
        firstItem.trigger("click");
    });


    // ------------------------------------
    // CATEGORY CLICK → LOAD FEATURES + ARTICLES
    // ------------------------------------
    $(document).on("click", ".payroll-main-grid", function (e) {
        e.preventDefault();

        // Save last clicked
        let index = $(this).data("index");
        localStorage.setItem("report_last_clicked_category", index);

        // Active styling
        $(".payroll-main-grid").removeClass("active");
        $(this).addClass("active");

        let category = $(this).data("cat");

        // Load feature list (center grid)
        $.getJSON("Report/getReportByCategory?category=" + category, function (res) {

           let allEnabled = res.features.every(f => f.is_enabled == 1);

let badgeHtml = allEnabled
  ? `<div class="payroll-badge"><span class="payroll-present-badge">Active</span></div>`
  : `<div class="payroll-badge"><span class="payroll-step-badge">Not in Plan</span></div>`;

let html = badgeHtml + `
  <div class="payroll-card-title">
      ${capitalize(category)} Reports
  </div>
`;
            $.each(res.features, function (i, f) {
    let enabled = f.is_enabled;
    let badgeHtml = enabled 
      ? `<div class="payroll-badge"><span class="payroll-present-badge">Active</span></div>`
      : `<div class="payroll-badge"><span class="payroll-step-badge">Not in Plan</span></div>`;

    if (enabled == 1) {
        html += `
            <div style="margin-bottom:15px;">
             
                <button class="payroll-card-btn openFeature"
                        data-id="${f.feature_id}"
                        data-path="${f.feature_path}">
                    Open ${f.feature_name}
                </button>
            </div>`;
    } else {
        html += `
            <div style="margin-bottom:15px;">
             
                <button class="payroll-upgrade-btn">
                    ${f.feature_name} — Not in your plan
                </button>
            </div>`;
    }
});


            $(".payroll-grid-center").html(html);

            // Load articles for first feature automatically
            if (res.features.length > 0) {
    let firstFeatureID = res.features[0].feature_id;
    loadArticles(firstFeatureID);
} else {
                $(".payroll-grid-right").html(`<h4>No Articles Found</h4>`);
            }
        });
    });


    // ------------------------------------
    // LOAD ARTICLES BY FEATURE
    // ------------------------------------
    function loadArticles(featureId) {

        $.getJSON("Report/getArticleByFeature?feature_id=" + featureId, function (res) {

            let title = res.feature_name ? res.feature_name : "Report Guide";
            let desc = res.description ? res.description : "Explore details for this reporting module.";

            let html = `
                <div class="payroll-card-step">Info Panel</div>
                <div class="payroll-card-title">${title}</div>
                <div class="payroll-card-desc">${desc}</div>
                <div class="payroll-card-title" style="margin-top:25px;">Articles</div>
            `;

            if (res.article) {

                let links = res.article.split(",");

                html += `<ul class="payroll-article-list">`;

                $.each(links, function (i, link) {
                    let clean = decodeURIComponent(
                        link.split("/").pop().replace(/-/g, " ")
                    );
                    clean = clean.replace(/\b\w/g, c => c.toUpperCase());

                    html += `
                        <li>
                            <a href="${link.trim()}" target="_blank">${clean}</a>
                        </li>`;
                });

                html += `</ul>`;

            } else {
                html += `<p>No articles available.</p>`;
            }

            $(".payroll-grid-right").html(html);
        });
    }


    // ------------------------------------
    // FEATURE CLICK → OPEN REPORT FORM
    // ------------------------------------
    $(document).on("click", ".openFeature", function () {

        let path = $(this).data("path");

        $("#container").isLoading({ text: "Loading", position: "overlay" });
        $("#container").empty().load(path, function () {
            isDashboardShown = false;
        });
    });


    // ------------------------------------
    // Helper Function
    // ------------------------------------
    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }

});
</script>
