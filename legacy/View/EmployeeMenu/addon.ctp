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

  /* .items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
  } */

  .item-card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 20px;
    padding: 10px 25px;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 80px;
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
    width: 46px;
    height: 46px;
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
    text-align: center;
    padding: 80px 0;
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
  .feature-group {
  margin-bottom: 20px;
}

.feature-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 25px;
  border: 1px solid #eef2f6;
  border-radius: 16px;
  background: #f9fafb;
  cursor: pointer;
  transition: 0.3s;
}

.feature-header:hover {
  background: #f1f5f9;
}

.feature-items {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  max-height: 0;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 0;
  padding: 0 10px;
}

.feature-items.active {
  max-height: 2000px; /* Allows for many items */
  opacity: 1;
  padding: 20px 10px;
  margin-top: 5px;
}

.feature-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border: 1px solid #eef2f6;
  border-radius: 16px;
  background: #ffffff;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.feature-header:hover {
  background: #f8fafc;
  border-color: var(--rizo-cyan);
  transform: translateX(5px);
}

.feature-header.active {
  background: #f1f5f9;
  border-color: var(--primary-blue);
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.toggle-icon {
  transition: all 0.3s ease;
  color: #64748b;
  width: 20px;
  text-align: center;
}

.feature-header.active .toggle-icon {
  color: var(--primary-blue);
}

/* Ensure no overlap blocks clicks */
.feature-group {
  position: relative;
  z-index: 1;
}

.feature-header {
  user-select: none;
}

</style>

<div class="employee-view">
  <section style="padding: 40px 40px 10px 20px;">
    <h1 class="section-title">Add-Ons</h1>
  </section>

  <div class="employee-layout">
    <div class="content-area">
      <div class="items-grid" id="items-container">
        <!-- Items loaded via AJAX -->
      </div>
    </div>
  </div>
</div>

<script>
  var live = (typeof live !== 'undefined') ? live : '<?php echo $this->webroot; ?>';
  if (!live.endsWith('/')) live += '/';

  $(document).ready(function() {
    // Load Addon Menus only for addon page
     sessionStorage.setItem('menu_type', 'addon');
    loadItems();

    // Toggle logic with delegation on the container
    $('#items-container').on('click', '.feature-header', function(e) {
        e.preventDefault();
        const header = $(this);
        const groupKey = header.data('key');
        const items = header.next('.feature-items');
        const icon = header.find('.toggle-icon');

        // Toggle state
        const isActive = header.toggleClass('active').hasClass('active');
        items.toggleClass('active', isActive);

        // Save state to window object (persists during navigation, cleared on refresh)
        if (isActive) {
            window.last_open_addon_group = groupKey;
        } else {
            if (window.last_open_addon_group === groupKey) {
                window.last_open_addon_group = null;
            }
        }

        // Update icon robustly
        if (isActive) {
            icon.removeClass('fa-plus').addClass('fa-minus');
        } else {
            icon.removeClass('fa-minus').addClass('fa-plus');
        }
    });

    // Open module page
    $('#items-container').on('click', '.item-card', function(e) {
        e.stopPropagation();
        const url = $(this).data('url');
        const id = $(this).data('id');
        if (url) {
            openAddonModule(url, id);
        }
    });

    function loadItems() {
      const container = $('#items-container');
      container.html(generateSkeletons(6));

      const url = live + 'EmployeeMenu/getAddonMenus';

      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
          renderItems(res);
        },
        error: function(xhr) {
          container.html('<div class="no-data">Failed to load addon data.</div>');
        }
      });
    }
function renderItems(data) {

    const container = $('#items-container');
    const colors = ['icon-cyan', 'icon-yellow', 'icon-pink', 'icon-green', 'icon-purple', 'icon-blue'];

    if (!data || Object.keys(data).length === 0) {
        container.html('<div class="no-data"><i class="fa fa-info-circle"></i>No add-on menus allocated.</div>');
        return;
    }

    /* REQUIRED ORDER */
    const addonOrder = [
        "Company",
        "Employee",
        "Attendance",
        "Payroll",
        "Payment Approvals",
        "Reports",
        "Payment management",
        "Stock Managements"
    ];

    /* SORT GROUPS BASED ON ORDER */
    const sortedKeys = Object.keys(data).sort(function(a, b) {

        const indexA = addonOrder.indexOf(a) === -1 ? 999 : addonOrder.indexOf(a);
        const indexB = addonOrder.indexOf(b) === -1 ? 999 : addonOrder.indexOf(b);

        return indexA - indexB;
    });

    let html = '';
    const lastOpenKey = window.last_open_addon_group;

    $.each(sortedKeys, function(_, featureKey) {

        const features = data[featureKey];
        const safeKey = escapeHtml(featureKey || 'Others');
        const isOpen = (safeKey === lastOpenKey);

        html += `
            <div class="feature-group">
                <div class="feature-header ${isOpen ? 'active' : ''}" data-key="${safeKey}">
                    <h3 style="margin:0;font-weight:700;color:#1e293b;">
                        ${safeKey}
                    </h3>
                    <i class="fa ${isOpen ? 'fa-minus' : 'fa-plus'} toggle-icon" style="font-size:18px;"></i>
                </div>
                <div class="feature-items ${isOpen ? 'active' : ''}">
        `;

        $.each(features, function(i, item) {

            const safeName = escapeHtml(item.feature_name || '');
            const colorClass = colors[i % colors.length];
            const iconClass = item.icon ? item.icon : 'fa fa-circle-o';

            html += `
                <div class="item-card" 
                     style="width:280px;" 
                     data-url="${item.feature_path}" 
                     data-id="${item.feature_id}">
                    <div class="item-header">
                        <div class="item-icon ${colorClass}">
                            <i class="${iconClass}"></i>
                        </div>
                        <h1 class="item-name">${safeName}</h1>
                    </div>
                </div>
            `;
        });

        html += `</div></div>`;
    });

    container.html(html);
}
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

    function escapeHtml(text) {
      return $('<div>').text(text).html();
    }
  });

function openAddonModule(url, featureId) {
    sessionStorage.setItem('menu_type', 'addon');
    if (!url) return false;
    var emp_id = '<?php echo $emp_pkey; ?>';

    $("#container").isLoading({ text: "Loading", position: "overlay" });

    const loadContent = () => {
        var finalUrl = url.startsWith('http') ? url : live + url;
        if (!finalUrl.endsWith('/')) finalUrl += '/';
        
        $("#container").load(finalUrl, function () {
            $("#container").isLoading("hide");
            isDashboardShown = false;
        });
    };

    if (featureId) {
        $.ajax({
            url: live + 'EmployeeMenu/setFeatureSession',
            type: 'POST',
            data: { feature_id: featureId },
            dataType: 'json',
            success: function() {
                loadContent();
            },
            error: function() {
                console.error("Failed to set feature session");
                loadContent(); // Load anyway as fallback
            }
        });
    } else {
        loadContent();
    }
}
</script>
