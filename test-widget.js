jQuery(window).on("elementor/frontend/init", function () {
  elementorFrontend.hooks.addAction(
    "frontend/element_ready/simple_text_widget.default",
    function ($scope, $) {
      // Access the text content of the widget
      var widgetText = $scope.find(".simple-text-content").text();

      // Do something with the text
      console.log("The text is: " + widgetText);

      // Example: Change the text
      $scope.find(".simple-text-content").text("Updated Text via JS!");
    }
  );
});
