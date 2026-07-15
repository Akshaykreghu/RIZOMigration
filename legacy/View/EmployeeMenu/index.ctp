<style>
  :root {
    --primary-blue: #1e516e;
    --rizo-cyan: #00aeef;
    --rizo-yellow: #ffc20e;
    --rizo-pink: #e91e63;
    --rizo-green: #39b54a;
    --rizo-purple: #662d91;
  }

  .employee-layout {
    gap: 30px;
    padding: 25px;
    /* background: #f8fafc; */
    min-height: calc(100vh - 120px);
    font-family: 'Inter', sans-serif;
  }

  .content-area {
    background: white;
    border-radius: 24px;
    padding: 35px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
    border: 1px solid #eef2f6;
  }

  .items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
  }

  .item-card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 20px;
    padding: 25px;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 120px;
    position: relative;
    overflow: hidden;
  }

  .item-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--primary-blue);
    opacity: 0;
    transition: 0.3s;
  }

  .item-card:hover {
    border-color: transparent;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: translateY(-8px);
  }

  .item-card:hover::after {
    opacity: 1;
  }

  .item-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: #fff;
    transition: all 0.4s ease;
    box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
  }

  /* Color Variations for Icons */
  .icon-cyan { background: var(--rizo-cyan); }
  .icon-yellow { background: var(--rizo-yellow); }
  .icon-pink { background: var(--rizo-pink); }
  .icon-green { background: var(--rizo-green); }
  .icon-purple { background: var(--rizo-purple); }
  .icon-blue { background: var(--primary-blue); }

  .item-card:hover .item-icon {
    transform: rotate(10deg) scale(1.1);
  }

  .item-header {
    display: flex;
    align-items: center;
    gap: 18px;
  }

  .item-name {
    font-weight: 700;
    font-size: 1.5rem;
    color: #1e293b;
    margin: 0;
    letter-spacing: -0.3px;
  }

  .item-desc {
    font-size: 0.9rem;
    color: #64748b;
    margin-top: 8px;
  }

  /* Skeleton */
  .skeleton {
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 8px;
  }

  @keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }

  .no-data {
  grid-column: 1 / -1;
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 120px 0;

   color: #94a3b8;
    font-weight: 500;
}
 .no-data i{
  padding: 0 3px;
 }
  .section-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary-blue);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .section-title::before {
    content: '';
    width: 8px;
    height: 32px;
    /* background: var(--rizo-cyan); */
    border-radius: 4px;
    display: inline-block;
  }
</style>

<div class="employee-view">

  <section style="padding:40px 40px 10px 20px;">
    <h1 class="section-title">Standard Menus</h1>
  </section>

  <div class="employee-layout">
    <div class="content-area">
      <div class="items-grid" id="items-container">
      </div>
    </div>
  </div>

</div>

<script>
  var live = (typeof live !== 'undefined') ? live : '<?php echo $this->webroot; ?>';
  if (!live.endsWith('/')) live += '/';

  $(document).ready(function() {
    sessionStorage.setItem('menu_type', 'standard');
    loadDefaultMenus();
  });

  /* ===============================
     Load Default Menus
  ================================ */
  function loadDefaultMenus() {

    const container = $('#items-container');
    container.html(generateSkeletons(6));

    $.ajax({
      url: live + 'EmployeeMenu/getDefaultMenus',
      type: 'GET',
      dataType: 'json',
      success: function(res) {
        renderMenus(res);
      },
      error: function() {
        container.html(
          '<div class="no-data">Failed to load menu data.</div>'
        );
      }
    });
  }

  /* ===============================
     Render Cards
  ================================ */
  function renderMenus(data) {

    const container = $('#items-container');
    const colors = ['icon-cyan', 'icon-yellow', 'icon-pink', 'icon-green', 'icon-purple', 'icon-blue'];

    if (!data || data.length === 0) {
      container.html(
        '<div class="no-data"><i class="fa fa-info-circle"></i> No menus allocated.</div>'
      );
      return;
    }

    let html = '';

    $.each(data, function(i, item) {

      const safeName = escapeHtml(item.menu_name || '');
      const safeUrl = item.menu_url || '';
      const iconClass = item.iconCls ? item.iconCls : 'fa fa-folder-open';
      const colorClass = colors[i % colors.length];

      html += `
           <div class="item-card" data-url="${safeUrl}">
              <div class="item-header">
                  <div class="item-icon ${colorClass}">
                      <i class="${iconClass}"></i>
                  </div>
                  <h1 class="item-name">${safeName}</h1>
              </div>
          </div>
        `;
    });

    container.html(html);

    // Attach click after render (better than inline onclick)
    $('.item-card').off('click').on('click', function() {
      openStandardModule($(this).data('url'));
    });
  }

  /* ===============================
     Open Module Page
  ================================ */
  function openStandardModule(url) {
    sessionStorage.setItem('menu_type', 'standard');

    if (!url) return;

    var emp_id = '<?php echo $emp_pkey; ?>';

    $("#container").isLoading({
      text: "Loading",
      position: "overlay"
    });

    const loadContent = () => {
        var finalUrl = url.startsWith('http') ? url : live + url;

        if (emp_id && !finalUrl.includes('/' + emp_id)) {
          if (!finalUrl.endsWith('/')) finalUrl += '/';
          finalUrl;
        }

        $("#container").load(finalUrl, function() {
          $("#container").isLoading("hide");
          isDashboardShown = false;
        });
    };

    // Clear feature session from the PHP backend, standard pages shouldn't use it!
    // This fixes edge cases where PHP session state stays stuck in "addon" mode!
    $.ajax({
        url: live + 'EmployeeMenu/setFeatureSession',
        type: 'POST',
        data: { feature_id: '' },
        complete: function() {
            loadContent();
        }
    });

  }

  /* ===============================
     Skeleton Generator
  ================================ */
  function generateSkeletons(count) {

    let html = '';

    for (let i = 0; i < count; i++) {
      html += `
            <div class="item-card">
                <div class="item-icon skeleton" style="width:54px;height:54px;"></div>
                <div class="skeleton" style="width:70%;height:20px;margin-bottom:10px;"></div>
                <div class="skeleton" style="width:100%;height:16px;margin-bottom:6px;"></div>
                <div class="skeleton" style="width:85%;height:16px;margin-bottom:20px;"></div>
                <div class="skeleton" style="width:100%;height:40px;border-radius:10px;"></div>
            </div>
        `;
    }

    return html;
  }

  /* ===============================
     XSS Protection
  ================================ */
  function escapeHtml(text) {
    return $('<div>').text(text).html();
  }
</script>