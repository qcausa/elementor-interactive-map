// jQuery(window).on("elementor/frontend/init", function () {
//   elementorFrontend.hooks.addAction(
//     "frontend/element_ready/interactive_map.default",
//     function ($scope) {
//       const settings = $scope.data("settings");
//       const containerID = settings["contents_container"]; // Get the widget's unique ID
//       console.log("containerID:", containerID);
//     }
//   );

//   // elementorFrontend.hooks.addAction(
//   //   "frontend/element_ready/heading.default",
//   //   function ($scope) {
//   //     console.log("Heading widget ready:", $scope);
//   //     const widget = $scope.data(); // Get the widget's unique ID
//   //     console.log("Heading Widget:", widget);
//   //     const settings = elementorFrontend.config.elements;
//   //     console.log("Heading Settings:", settings);
//   //   }
//   // );
// });

document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM Content Loaded");
  const mapContainer = document.getElementById("interactive-map-widget");

  if (mapContainer) {
    console.log("Map container found");
    const displayType = mapContainer.getAttribute("data-display-type");
    const displayOptions = mapContainer.getAttribute("data-display-options");
    const regionColor = mapContainer.getAttribute("data-region-color");
    const borderColor = mapContainer.getAttribute("data-region-border-color");
    const contentsContainerId = mapContainer.getAttribute(
      "data-contents-container"
    );

    console.log("Map attributes:", {
      displayType,
      displayOptions,
      regionColor,
      borderColor,
      contentsContainerId,
    });

    // Parse the regions and links from the script tag
    const regionsData = JSON.parse(
      document.getElementById("map-regions-data").textContent
    );
    console.log("Regions data:", regionsData);

    const contentsContainer = document.getElementById(contentsContainerId);
    const globalContent = mapContainer.querySelector(
      ".interactive-map-global-content"
    );

    console.log("Contents container:", contentsContainer);
    console.log("Global content:", globalContent);

    // Function to show global or region-specific content
    function showContent(categoryId = "") {
      console.log("showContent called with categoryId:", categoryId);
      if (contentsContainer && globalContent) {
        let content = globalContent.innerHTML;

        // Update the category ID
        const categoryIdDisplay = `<p>Linked Category ID: ${categoryId}</p>`;

        // Find and update Elementor loop widget
        const loopWidget = contentsContainer.querySelector(
          ".elementor-widget-loop-grid"
        );
        console.log("Loop widget:", loopWidget);
        if (loopWidget) {
          const widgetId = loopWidget.dataset.id;
          console.log("Loop widget ID:", widgetId);
          if (widgetId) {
            console.log("Updating loop widget with category:", categoryId);
            if (window.elementorFrontend && window.elementorFrontend.hooks) {
              console.log("Elementor frontend hooks available");

              // Add action to update the loop widget content
              elementorFrontend.hooks.addAction(
                "frontend/element_ready/loop.default",
                function ($element) {
                  const widgetData =
                    elementorFrontend.config.elements.data[widgetId];
                  console.log("Widget data:", widgetData);
                  if (widgetData) {
                    widgetData.settings.category_filter = categoryId
                      ? [categoryId]
                      : [];
                    $element.data("settings", widgetData.settings);

                    // Re-render the widget
                    const loopHandler =
                      elementorFrontend.elementsHandler.getHandler("loop");
                    if (loopHandler) {
                      const widgetInstance = new loopHandler({
                        $element: jQuery(loopWidget),
                      });
                      widgetInstance.onInit();
                    }
                  }
                }
              );
              elementorFrontend.hooks.doAction(
                "frontend/element_ready/loop.default",
                jQuery(loopWidget)
              );
            } else {
              console.warn(
                "Elementor frontend hooks not available, using fallback."
              );
              // Fallback to an AJAX call to update the loop content
              jQuery.ajax({
                url: "/wp-admin/admin-ajax.php",
                method: "POST",
                data: {
                  action: "update_loop_widget",
                  widget_id: widgetId,
                  category_id: categoryId,
                },
                success: function (response) {
                  if (response.success) {
                    loopWidget.innerHTML = response.data;
                  } else {
                    console.error(
                      "Failed to update loop widget:",
                      response.data
                    );
                  }
                },
                error: function (xhr, status, error) {
                  console.error("Ajax request failed:", error);
                },
              });
            }
          }
        }

        // Update content container with region-specific or global content
        contentsContainer.querySelector(
          ".map-contents-region-content"
        ).innerHTML = categoryIdDisplay + content;
      }
    }

    // Clear the region content by default
    contentsContainer.querySelector(".map-contents-region-content").innerHTML =
      "";

    // List of regions with their corresponding country codes
    const regionCodes = {
      US: "840",
      CA: "124",
      LATAM: [
        "032",
        "068",
        "076",
        "152",
        "170",
        "188",
        "192",
        "214",
        "218",
        "222",
        "320",
        "340",
        "484",
        "558",
        "591",
        "600",
        "604",
        "740",
        "858",
        "862",
      ],
    };

    d3.json(
      "https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json"
    ).then(function (world) {
      const allCountries = topojson.feature(
        world,
        world.objects.countries
      ).features;
      const filteredCountries = allCountries.filter((d) => {
        return Object.values(regionCodes).flat().includes(d.id);
      });

      const width = mapContainer.offsetWidth;
      const height = 600;

      const svg = d3
        .select("#interactive-map-widget")
        .append("svg")
        .attr("width", "100%")
        .attr("height", height)
        .attr("viewBox", `0 0 ${width} ${height}`)
        .attr("preserveAspectRatio", "xMidYMid meet");

      const projection = d3.geoMercator();
      const path = d3.geoPath().projection(projection);

      svg
        .selectAll("path")
        .data(filteredCountries)
        .enter()
        .append("path")
        .attr("d", path)
        .attr("fill", (d) => {
          const regionItem = regionsData.find((region) => {
            return (
              regionCodes[region.region_to_display] === d.id ||
              (Array.isArray(regionCodes[region.region_to_display]) &&
                regionCodes[region.region_to_display].includes(d.id))
            );
          });
          return regionItem ? regionColor : "none";
        })
        .attr("stroke", borderColor)
        .attr("stroke-width", 1)
        .on("click", function (event, d) {
          const regionItem = regionsData.find(
            (region) =>
              regionCodes[region.region_to_display] === d.id ||
              regionCodes[region.region_to_display]?.includes(d.id)
          );
          if (regionItem && contentsContainer) {
            if (regionItem.region_content) {
              // Show region-specific content
              contentsContainer.querySelector(
                ".map-contents-region-content"
              ).innerHTML = regionItem.region_content;
            } else {
              // Show global content with category ID
              showContent(regionItem.region_post_category);
            }
          } else {
            showContent(); // Show global content without category ID
          }
        });

      // Add click listener to the map container
      mapContainer.addEventListener("click", function (event) {
        if (event.target === mapContainer) {
          showContent(); // Show global content on map container click
        }
      });

      // Show global content by default
      showContent();

      // Center and zoom to the selected regions using fitExtent
      if (filteredCountries.length > 0) {
        projection.fitExtent(
          [
            [0, 0],
            [width, height],
          ],
          { type: "FeatureCollection", features: filteredCountries }
        );
        svg.selectAll("path").attr("d", path);
      }
    });
  }
});
